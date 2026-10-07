<?php

namespace App\Services;

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
    private const FRESH_MS = 1500;

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
     * Fetch stats without TikTok API keys/tokens and without the unofficial
     * /api/item/detail endpoint. We only download the public video webpage
     * and read the JSON TikTok embeds in that HTML.
     */
    private function fetchFreshTikTokStats(
        string $videoUrl,
        string $videoId
    ): array {
        /*
         * No TikTok developer API is used here. We try two public web surfaces:
         * the normal video page first, then TikTok's public embed page.  Both
         * contain the same hydration data TikTok needs to render the page.
         */
        $attempts = [
            [
                'url' => $this->canonicalVideoPageUrl($videoUrl),
                'source' => 'tiktok-public-page',
            ],
            [
                'url' => 'https://www.tiktok.com/embed/v2/'
                    .rawurlencode($videoId)
                    .'?lang=en-US',
                'source' => 'tiktok-public-embed',
            ],
        ];

        $lastProblem = null;

        foreach ($attempts as $attempt) {
            try {
                $item = $this->fetchVideoObjectFromPublicPage(
                    $attempt['url'],
                    $videoId
                );

                if ($item === null) {
                    $lastProblem = 'geen hydration-data gevonden';
                    continue;
                }

                $normalized = $this->normalizeVideoObject($item);
                $normalized['source'] = $attempt['source'];

                return $normalized;
            } catch (Throwable $e) {
                $lastProblem = $e->getMessage();
                report($e);
            }
        }

        throw new RuntimeException(
            'TikTok gaf geen actuele publieke videostatistieken terug'
            .($lastProblem ? ' ('.$lastProblem.')' : '.')
        );
    }

    /**
     * Read a public TikTok HTML page. This method intentionally does not call
     * /api/item/detail, Research API, Display API or any signed TikTok endpoint.
     */
    private function fetchVideoObjectFromPublicPage(
        string $url,
        string $videoId
    ): ?array {
        $response = $this->requestTikTok($url, [
            'Accept' =>
                'text/html,application/xhtml+xml,application/xml;q=0.9,'
                .'image/avif,image/webp,image/apng,*/*;q=0.8',
            'Referer' => 'https://www.tiktok.com/',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'same-origin',
            'Sec-Fetch-User' => '?1',
            'Upgrade-Insecure-Requests' => '1',
        ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'TikTok HTTP '.$response->status()
            );
        }

        $html = (string) $response->body();

        if (trim($html) === '') {
            throw new RuntimeException('TikTok stuurde een lege HTML-pagina terug');
        }

        if ($this->looksLikeTikTokChallenge($html)) {
            throw new RuntimeException(
                'TikTok gaf een browser/challenge-pagina terug aan de Railway-server'
            );
        }

        foreach ([
            '__UNIVERSAL_DATA_FOR_REHYDRATION__',
            'SIGI_STATE',
            '__NEXT_DATA__',
        ] as $scriptId) {
            $data = $this->extractJsonScript($html, $scriptId);

            if (!is_array($data)) {
                continue;
            }

            if ($scriptId === '__UNIVERSAL_DATA_FOR_REHYDRATION__') {
                $direct =
                    $data['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct']
                    ?? $data['__DEFAULT_SCOPE__']['webapp.video-detail']['item_info']['item_struct']
                    ?? null;

                if (
                    is_array($direct)
                    && $this->videoMatches($direct, $videoId)
                    && $this->containsStats($direct)
                ) {
                    return $direct;
                }
            }

            if ($scriptId === 'SIGI_STATE') {
                $direct = $data['ItemModule'][$videoId] ?? null;

                if (
                    is_array($direct)
                    && $this->containsStats($direct)
                ) {
                    return $direct;
                }
            }

            $found = $this->findVideoRecursively($data, $videoId);

            if ($found !== null) {
                return $found;
            }
        }

        /*
         * TikTok occasionally changes only the script element id. Search every
         * application/json script rather than depending on a single DOM id.
         */
        if (preg_match_all(
            '/<script\\b[^>]*type=["\\\']application\\/json["\\\'][^>]*>(.*?)<\\/script>/is',
            $html,
            $matches
        )) {
            foreach ($matches[1] as $rawJson) {
                $rawJson = html_entity_decode(
                    trim((string) $rawJson),
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

                $data = json_decode($rawJson, true);

                if (!is_array($data)) {
                    continue;
                }

                $found = $this->findVideoRecursively($data, $videoId);

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return $this->extractVideoFromHtmlWindow($html, $videoId);
    }

    private function canonicalVideoPageUrl(string $videoUrl): string
    {
        $parts = parse_url($videoUrl);

        if (!is_array($parts)) {
            return $videoUrl;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) ($parts['host'] ?? 'www.tiktok.com'));
        $path = (string) ($parts['path'] ?? '/');

        return $scheme.'://'.$host.$path.'?lang=en';
    }

    private function looksLikeTikTokChallenge(string $html): bool
    {
        $sample = strtolower(substr($html, 0, 250000));

        foreach ([
            'captcha',
            'verify to continue',
            'verify you are human',
            'security verification',
            'secsdk-captcha',
        ] as $needle) {
            if (str_contains($sample, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function extractVideoFromHtmlWindow(
        string $html,
        string $videoId
    ): ?array {
        $decoded = html_entity_decode(
            $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        /* JSON may be embedded with escaped quotes in some page variants. */
        $searchable = str_replace('\\"', '"', $decoded);
        $offset = 0;

        while (($position = strpos($searchable, $videoId, $offset)) !== false) {
            $start = max(0, $position - 30000);
            $window = substr($searchable, $start, 90000);

            $stats = [
                'playCount' => $this->extractCounterFromText(
                    $window,
                    ['playCount', 'play_count', 'viewCount', 'view_count']
                ),
                'diggCount' => $this->extractCounterFromText(
                    $window,
                    ['diggCount', 'digg_count', 'likeCount', 'like_count']
                ),
                'commentCount' => $this->extractCounterFromText(
                    $window,
                    ['commentCount', 'comment_count']
                ),
                'shareCount' => $this->extractCounterFromText(
                    $window,
                    ['shareCount', 'share_count']
                ),
            ];

            if (count(array_filter($stats, static fn ($v) => $v !== null)) >= 2) {
                return [
                    'id' => $videoId,
                    'stats' => $stats,
                ];
            }

            $offset = $position + strlen($videoId);
        }

        return null;
    }

    private function extractCounterFromText(
        string $text,
        array $keys
    ): ?int {
        foreach ($keys as $key) {
            $quotedKey = preg_quote($key, '/');

            foreach ([
                '/["\']'.$quotedKey.'["\']\\s*:\\s*["\']?(\\d{1,20})["\']?/i',
                '/\\b'.$quotedKey.'\\b\\s*[:=]\\s*["\']?(\\d{1,20})["\']?/i',
            ] as $pattern) {
                if (preg_match($pattern, $text, $match)) {
                    return (int) $match[1];
                }
            }
        }

        return null;
    }

    private function requestTikTok(
        string $url,
        array $extraHeaders = []
    ): Response {
        $headers = array_merge([
            'User-Agent' =>
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                .'AppleWebKit/537.36 (KHTML, like Gecko) '
                .'Chrome/141.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9,nl;q=0.7',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'DNT' => '1',
            'Sec-CH-UA' => '"Google Chrome";v="141", "Chromium";v="141", "Not_A Brand";v="99"',
            'Sec-CH-UA-Mobile' => '?0',
            'Sec-CH-UA-Platform' => '"Windows"',
        ], $extraHeaders);

        return Http::withHeaders($headers)
            ->withOptions([
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => true,
                    'referer' => true,
                    'track_redirects' => true,
                ],
                'http_errors' => false,
            ])
            ->connectTimeout(8)
            ->timeout(18)
            ->retry(2, 350, throw: false)
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
