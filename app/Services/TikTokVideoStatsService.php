<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class TikTokVideoStatsService
{
    /**
     * How long one snapshot is considered fresh.
     *
     * Your frontend polls every 4 seconds, so only the first request
     * after this window refreshes TikTok. Other visitors reuse that snapshot.
     */
    private const FRESH_MS = 4000;

    /**
     * Keep the last successful snapshot longer than the fresh window.
     * This lets us serve the latest known real value if TikTok temporarily fails.
     */
    private const SNAPSHOT_TTL_SECONDS = 45;

    /**
     * Prevent multiple visitors from refreshing the same video simultaneously.
     */
    private const LOCK_SECONDS = 18;

    private const ALLOWED_HOSTS = [
        'tiktok.com',
        'www.tiktok.com',
        'm.tiktok.com',
        'vm.tiktok.com',
        'vt.tiktok.com',
    ];

    public function resolveVideo(string $input): array
    {
        $url = $this->normalizeUrl($input);

        if (!$this->isAllowedTikTokUrl($url)) {
            throw new RuntimeException('Gebruik een geldige TikTok-video-URL.');
        }

        $videoId = $this->extractVideoId($url);

        if ($videoId !== null) {
            return [
                'video_id' => $videoId,
                'url' => $url,
            ];
        }

        $resolvedUrl = $this->followShortTikTokUrl($url);
        $videoId = $this->extractVideoId($resolvedUrl);

        if ($videoId === null) {
            throw new RuntimeException('Kon geen TikTok-video-ID uit deze link halen.');
        }

        return [
            'video_id' => $videoId,
            'url' => $resolvedUrl,
        ];
    }

    /**
     * Public method used by the controller.
     *
     * Result always contains real values returned by TikTok.
     * No artificial increments are generated.
     */
    public function getLiveStats(string $videoUrl, string $videoId): array
    {
        $resolved = $this->resolveVideo($videoUrl);

        if ((string) $resolved['video_id'] !== (string) $videoId) {
            throw new RuntimeException('De TikTok-link hoort niet bij deze video.');
        }

        $cacheKey = $this->snapshotKey($videoId);
        $lockKey = $this->lockKey($videoId);

        $snapshot = Cache::get($cacheKey);

        if ($this->isFreshSnapshot($snapshot)) {
            return $this->snapshotResult($snapshot, false);
        }

        /*
         * Only one request is allowed to refresh a video at a time.
         */
        $lock = Cache::lock($lockKey, self::LOCK_SECONDS);

        if ($lock->get()) {
            try {
                /*
                 * Double-check because another request may have refreshed
                 * between our first cache read and acquiring the lock.
                 */
                $snapshot = Cache::get($cacheKey);

                if ($this->isFreshSnapshot($snapshot)) {
                    return $this->snapshotResult($snapshot, false);
                }

                try {
                    $stats = $this->fetchFreshTikTokStats(
                        $resolved['url'],
                        $videoId
                    );

                    $snapshot = [
                        'stats' => $stats,
                        'fetched_at_ms' => $this->nowMs(),
                    ];

                    Cache::put(
                        $cacheKey,
                        $snapshot,
                        now()->addSeconds(self::SNAPSHOT_TTL_SECONDS)
                    );

                    return $this->snapshotResult($snapshot, true);
                } catch (Throwable $e) {
                    /*
                     * TikTok can temporarily block or change web responses.
                     * If we have a previous real snapshot, return it instead of
                     * inventing data or taking the whole counter offline.
                     */
                    $stale = Cache::get($cacheKey);

                    if ($this->isUsableSnapshot($stale)) {
                        report($e);

                        return $this->snapshotResult(
                            $stale,
                            false,
                            true
                        );
                    }

                    throw $e;
                }
            } finally {
                $lock->release();
            }
        }

        /*
         * Another request is currently refreshing this same video.
         * Return the latest real snapshot immediately when available.
         */
        $snapshot = Cache::get($cacheKey);

        if ($this->isUsableSnapshot($snapshot)) {
            return $this->snapshotResult($snapshot, false);
        }

        /*
         * First-ever request and somebody else owns the refresh lock.
         * Wait briefly for their snapshot instead of sending another TikTok fetch.
         */
        for ($attempt = 0; $attempt < 8; $attempt++) {
            usleep(250000);

            $snapshot = Cache::get($cacheKey);

            if ($this->isUsableSnapshot($snapshot)) {
                return $this->snapshotResult($snapshot, false);
            }
        }

        throw new RuntimeException(
            'De eerste TikTok Live Count snapshot wordt nog opgehaald. Probeer opnieuw.'
        );
    }

    /**
     * Fetch directly from TikTok without a third-party provider token.
     */
    private function fetchFreshTikTokStats(
        string $videoUrl,
        string $videoId
    ): array {
        /*
         * Public page hydration is the most stable no-token path.
         */
        $item = $this->fetchFromPublicVideoPage(
            $videoUrl,
            $videoId
        );

        if ($item !== null) {
            $normalized = $this->normalizeVideoObject($item);
            $normalized['source'] = 'tiktok-page';

            return $normalized;
        }

        /*
         * Secondary attempt. This unofficial TikTok web endpoint may require
         * additional browser signatures at times, therefore it is only a fallback.
         */
        $item = $this->fetchFromItemDetail(
            $videoId,
            $videoUrl
        );

        if ($item !== null) {
            $normalized = $this->normalizeVideoObject($item);
            $normalized['source'] = 'tiktok-item-detail';

            return $normalized;
        }

        throw new RuntimeException(
            'TikTok gaf op dit moment geen bruikbare publieke videostatistieken terug.'
        );
    }

    private function fetchFromPublicVideoPage(
        string $videoUrl,
        string $videoId
    ): ?array {
        $response = $this->requestTikTok(
            $this->withCacheBuster($videoUrl),
            [
                'Accept' =>
                    'text/html,application/xhtml+xml,application/xml;q=0.9,'
                    .'image/avif,image/webp,*/*;q=0.8',

                'Referer' => 'https://www.tiktok.com/',
            ]
        );

        if (!$response->successful()) {
            return null;
        }

        $html = $response->body();

        if ($html === '') {
            return null;
        }

        /*
         * Current/modern TikTok page hydration.
         */
        $universal = $this->extractJsonScript(
            $html,
            '__UNIVERSAL_DATA_FOR_REHYDRATION__'
        );

        if (is_array($universal)) {
            $direct =
                $universal['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct']
                ?? $universal['__DEFAULT_SCOPE__']['webapp.video-detail']['item_info']['item_struct']
                ?? null;

            if (
                is_array($direct)
                && $this->videoMatches($direct, $videoId)
                && $this->containsStats($direct)
            ) {
                return $direct;
            }

            $found = $this->findVideoRecursively(
                $universal,
                $videoId
            );

            if ($found !== null) {
                return $found;
            }
        }

        /*
         * Older/alternate TikTok response.
         */
        $sigi = $this->extractJsonScript(
            $html,
            'SIGI_STATE'
        );

        if (is_array($sigi)) {
            $direct = $sigi['ItemModule'][$videoId] ?? null;

            if (
                is_array($direct)
                && $this->containsStats($direct)
            ) {
                return $direct;
            }

            $found = $this->findVideoRecursively(
                $sigi,
                $videoId
            );

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function fetchFromItemDetail(
        string $videoId,
        string $referer
    ): ?array {
        $query = http_build_query([
            'aid' => '1988',
            'app_language' => 'en',
            'app_name' => 'tiktok_web',
            'browser_language' => 'en-US',
            'browser_name' => 'Mozilla',
            'browser_online' => 'true',
            'browser_platform' => 'Win32',
            'channel' => 'tiktok_web',
            'cookie_enabled' => 'true',
            'device_platform' => 'web_pc',
            'focus_state' => 'true',
            'from_page' => 'video',
            'history_len' => '1',
            'is_fullscreen' => 'false',
            'is_page_visible' => 'true',
            'itemId' => $videoId,
            'language' => 'en',
            'os' => 'windows',
            'region' => 'NL',
            'screen_height' => '1080',
            'screen_width' => '1920',
            'tz_name' => 'Europe/Amsterdam',
        ]);

        $response = $this->requestTikTok(
            'https://www.tiktok.com/api/item/detail/?'.$query,
            [
                'Accept' => 'application/json, text/plain, */*',
                'Referer' => $referer,
            ]
        );

        if (!$response->successful()) {
            return null;
        }

        $payload = $response->json();

        if (!is_array($payload)) {
            return null;
        }

        foreach ([
            $payload['itemInfo']['itemStruct'] ?? null,
            $payload['itemStruct'] ?? null,
            $payload['item_info']['item_struct'] ?? null,
        ] as $candidate) {
            if (
                is_array($candidate)
                && $this->videoMatches($candidate, $videoId)
                && $this->containsStats($candidate)
            ) {
                return $candidate;
            }
        }

        return $this->findVideoRecursively(
            $payload,
            $videoId
        );
    }

    private function requestTikTok(
        string $url,
        array $extraHeaders = []
    ): Response {
        return Http::withHeaders(
            array_merge(
                [
                    'User-Agent' =>
                        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                        .'AppleWebKit/537.36 (KHTML, like Gecko) '
                        .'Chrome/141.0.0.0 Safari/537.36',

                    'Accept-Language' => 'en-US,en;q=0.9,nl;q=0.8',
                    'Cache-Control' => 'no-cache, no-store, max-age=0',
                    'Pragma' => 'no-cache',
                    'DNT' => '1',
                ],
                $extraHeaders
            )
        )
            ->connectTimeout(7)
            ->timeout(14)
            ->retry(1, 250, throw: false)
            ->get($url);
    }

    private function normalizeVideoObject(array $video): array
    {
        $stats = $video['stats']
            ?? $video['statsV2']
            ?? $video['statistics']
            ?? [];

        $views = $this->firstInt([
            $stats['playCount'] ?? null,
            $stats['play_count'] ?? null,
            $stats['viewCount'] ?? null,
            $stats['view_count'] ?? null,
        ]);

        $likes = $this->firstInt([
            $stats['diggCount'] ?? null,
            $stats['digg_count'] ?? null,
            $stats['likeCount'] ?? null,
            $stats['like_count'] ?? null,
        ]);

        $comments = $this->firstInt([
            $stats['commentCount'] ?? null,
            $stats['comment_count'] ?? null,
        ]);

        $shares = $this->firstInt([
            $stats['shareCount'] ?? null,
            $stats['share_count'] ?? null,
        ]);

        if (
            $views === null
            && $likes === null
            && $comments === null
            && $shares === null
        ) {
            throw new RuntimeException(
                'TikTok response bevatte geen bruikbare counters.'
            );
        }

        $author = $video['author'] ?? null;

        if (is_array($author)) {
            $authorName =
                $author['uniqueId']
                ?? $author['unique_id']
                ?? $author['username']
                ?? $author['nickname']
                ?? null;
        } elseif (is_string($author)) {
            $authorName = $author;
        } else {
            $authorName = null;
        }

        $videoData = is_array($video['video'] ?? null)
            ? $video['video']
            : [];

        return [
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,

            'author_name' => $authorName,

            'title' =>
                $video['desc']
                ?? $video['description']
                ?? $video['title']
                ?? null,

            'thumbnail_url' => $this->extractUrlValue(
                $videoData['cover']
                    ?? $videoData['originCover']
                    ?? $videoData['origin_cover']
                    ?? $videoData['dynamicCover']
                    ?? $videoData['dynamic_cover']
                    ?? null
            ),
        ];
    }

    private function snapshotResult(
        array $snapshot,
        bool $fetchedFresh,
        bool $staleFallback = false
    ): array {
        $stats = $snapshot['stats'];

        return array_merge(
            $stats,
            [
                'fetched_fresh' => $fetchedFresh,
                'stale_fallback' => $staleFallback,
                'snapshot_age_ms' => max(
                    0,
                    $this->nowMs()
                    - (int) ($snapshot['fetched_at_ms'] ?? 0)
                ),
            ]
        );
    }

    private function isFreshSnapshot(mixed $snapshot): bool
    {
        if (!$this->isUsableSnapshot($snapshot)) {
            return false;
        }

        $age = $this->nowMs()
            - (int) $snapshot['fetched_at_ms'];

        return $age >= 0
            && $age < self::FRESH_MS;
    }

    private function isUsableSnapshot(mixed $snapshot): bool
    {
        return is_array($snapshot)
            && isset($snapshot['stats'])
            && is_array($snapshot['stats'])
            && isset($snapshot['fetched_at_ms'])
            && is_numeric($snapshot['fetched_at_ms']);
    }

    private function snapshotKey(string $videoId): string
    {
        return 'tiktok-live-count:snapshot:v2:'.$videoId;
    }

    private function lockKey(string $videoId): string
    {
        return 'tiktok-live-count:refresh-lock:v2:'.$videoId;
    }

    private function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    private function extractJsonScript(
        string $html,
        string $scriptId
    ): ?array {
        $pattern =
            '/<script\b[^>]*\bid=["\']'
            .preg_quote($scriptId, '/')
            .'["\'][^>]*>(.*?)<\/script>/is';

        if (!preg_match($pattern, $html, $match)) {
            return null;
        }

        $json = trim(
            html_entity_decode(
                $match[1] ?? '',
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );

        if ($json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded)
            ? $decoded
            : null;
    }

    private function findVideoRecursively(
        mixed $value,
        string $videoId
    ): ?array {
        if (!is_array($value)) {
            return null;
        }

        if (
            $this->videoMatches($value, $videoId)
            && $this->containsStats($value)
        ) {
            return $value;
        }

        foreach ($value as $child) {
            if (!is_array($child)) {
                continue;
            }

            $found = $this->findVideoRecursively(
                $child,
                $videoId
            );

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function videoMatches(
        array $video,
        string $videoId
    ): bool {
        $candidate =
            $video['id']
            ?? $video['aweme_id']
            ?? $video['awemeId']
            ?? $video['itemId']
            ?? $video['item_id']
            ?? null;

        return $candidate !== null
            && (string) $candidate === (string) $videoId;
    }

    private function containsStats(array $video): bool
    {
        return is_array($video['stats'] ?? null)
            || is_array($video['statsV2'] ?? null)
            || is_array($video['statistics'] ?? null);
    }

    private function extractUrlValue(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (!is_array($value)) {
            return null;
        }

        foreach ([
            $value['url_list'][0] ?? null,
            $value['urlList'][0] ?? null,
            $value['UrlList'][0] ?? null,
            $value[0] ?? null,
        ] as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function firstInt(array $values): ?int
    {
        foreach ($values as $value) {
            $number = $this->toInt($value);

            if ($number !== null) {
                return $number;
            }
        }

        return null;
    }

    private function toInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        if (is_array($value)) {
            return $this->firstInt([
                $value['value'] ?? null,
                $value['count'] ?? null,
                $value['number'] ?? null,
            ]);
        }

        return null;
    }

    private function withCacheBuster(string $url): string
    {
        $separator = str_contains($url, '?')
            ? '&'
            : '?';

        return $url
            .$separator
            .'_mashal_live='
            .rawurlencode(
                $this->nowMs()
                .'-'
                .random_int(100000, 999999)
            );
    }

    private function followShortTikTokUrl(string $url): string
    {
        $current = $url;

        for ($i = 0; $i < 6; $i++) {
            if (!$this->isAllowedTikTokUrl($current)) {
                throw new RuntimeException(
                    'TikTok redirectte naar een ongeldig domein.'
                );
            }

            $response = Http::withHeaders([
                'User-Agent' =>
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                    .'AppleWebKit/537.36 (KHTML, like Gecko) '
                    .'Chrome/141.0.0.0 Safari/537.36',

                'Accept' =>
                    'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',

                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])
                ->withoutRedirecting()
                ->connectTimeout(6)
                ->timeout(10)
                ->get($current);

            if (!$response->redirect()) {
                break;
            }

            $location = $response->header('Location');

            if (!$location) {
                break;
            }

            $current = $this->absoluteUrl(
                $current,
                $location
            );
        }

        return $current;
    }

    private function normalizeUrl(string $input): string
    {
        $url = trim($input);

        if ($url === '') {
            throw new RuntimeException('TikTok URL ontbreekt.');
        }

        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://'.$url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('Gebruik een geldige TikTok URL.');
        }

        return $url;
    }

    private function isAllowedTikTokUrl(string $url): bool
    {
        $host = strtolower(
            (string) parse_url(
                $url,
                PHP_URL_HOST
            )
        );

        if ($host === '') {
            return false;
        }

        foreach (self::ALLOWED_HOSTS as $allowedHost) {
            if ($host === $allowedHost) {
                return true;
            }
        }

        return str_ends_with($host, '.tiktok.com');
    }

    private function extractVideoId(string $url): ?string
    {
        foreach ([
            '~/(?:video|v)/(\d{10,30})(?:[/?#]|$)~i',
            '~[?&](?:item_id|video_id)=(\d{10,30})(?:&|$)~i',
        ] as $pattern) {
            if (preg_match($pattern, $url, $match)) {
                return $match[1];
            }
        }

        return null;
    }

    private function absoluteUrl(
        string $baseUrl,
        string $location
    ): string {
        if (preg_match('~^https?://~i', $location)) {
            return $location;
        }

        $parts = parse_url($baseUrl);

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? 'www.tiktok.com';

        if (str_starts_with($location, '//')) {
            return $scheme.':'.$location;
        }

        if (str_starts_with($location, '/')) {
            return $scheme.'://'.$host.$location;
        }

        $path = $parts['path'] ?? '/';

        $directory = rtrim(
            str_replace(
                '\\',
                '/',
                dirname($path)
            ),
            '/'
        );

        return $scheme
            .'://'
            .$host
            .$directory
            .'/'
            .$location;
    }
}
