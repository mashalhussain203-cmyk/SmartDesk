<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TikTokVideoStatsService
{
    private const ALLOWED_HOSTS = [
        'tiktok.com',
        'www.tiktok.com',
        'm.tiktok.com',
        'vm.tiktok.com',
        'vt.tiktok.com',
    ];

    /**
     * Resolve a normal or short TikTok URL to a video id + final URL.
     */
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
     * Fetch fresh TikTok stats.
     *
     * IMPORTANT:
     * - no Laravel cache
     * - first tries TikTok web item/detail
     * - then falls back to the video page
     */
    public function getLiveStats(string $videoUrl, string $videoId): array
    {
        $resolved = $this->resolveVideo($videoUrl);

        if ((string) $resolved['video_id'] !== (string) $videoId) {
            throw new RuntimeException('De TikTok-link hoort niet bij deze video.');
        }

        /*
         * 1. Try TikTok's web item detail response first.
         * This can expose integer counters directly when TikTok allows it.
         */
        $item = $this->fetchItemDetail(
            $videoId,
            $resolved['url']
        );

        if ($item !== null) {
            return $this->normalizeVideoObject($item);
        }

        /*
         * 2. Fallback: fetch the public video page again.
         */
        $item = $this->fetchVideoPage(
            $resolved['url'],
            $videoId
        );

        if ($item !== null) {
            return $this->normalizeVideoObject($item);
        }

        throw new RuntimeException(
            'TikTok gaf geen bruikbare actuele videostatistieken terug.'
        );
    }

    /**
     * Try TikTok web item/detail.
     *
     * TikTok can change or restrict this undocumented web endpoint at any time.
     */
    private function fetchItemDetail(
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
            'history_len' => '2',
            'is_fullscreen' => 'false',
            'is_page_visible' => 'true',
            'itemId' => $videoId,
            'language' => 'en',
            'os' => 'windows',
            'region' => 'NL',
            'screen_height' => '1080',
            'screen_width' => '1920',
            'tz_name' => 'Europe/Amsterdam',

            /*
             * Prevent accidental intermediary caching.
             */
            '_mashal_live' => now()->valueOf().'-'.random_int(100000, 999999),
        ]);

        $url = 'https://www.tiktok.com/api/item/detail/?'.$query;

        $response = $this->tiktokRequest(
            $url,
            [
                'Accept' => 'application/json, text/plain, */*',
                'Referer' => $referer,
            ]
        );

        if (!$response->successful()) {
            return null;
        }

        $body = trim($response->body());

        if ($body === '') {
            return null;
        }

        $data = json_decode($body, true);

        if (!is_array($data)) {
            return null;
        }

        /*
         * Common shapes:
         * itemInfo.itemStruct
         * itemStruct
         */
        $directCandidates = [
            $data['itemInfo']['itemStruct'] ?? null,
            $data['itemStruct'] ?? null,
            $data['item_info']['item_struct'] ?? null,
        ];

        foreach ($directCandidates as $candidate) {
            if (
                is_array($candidate)
                && $this->videoMatches($candidate, $videoId)
                && $this->containsStats($candidate)
            ) {
                return $candidate;
            }
        }

        return $this->findVideoRecursively(
            $data,
            $videoId
        );
    }

    /**
     * Fallback: reload the public video page and inspect hydration JSON.
     */
    private function fetchVideoPage(
        string $videoUrl,
        string $videoId
    ): ?array {
        $url = $this->withCacheBuster($videoUrl);

        $response = $this->tiktokRequest(
            $url,
            [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
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

        return $this->extractVideoFromHtml(
            $html,
            $videoId
        );
    }

    private function tiktokRequest(
        string $url,
        array $extraHeaders = []
    ): Response {
        $headers = array_merge([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
            'Accept-Language' => 'en-US,en;q=0.9,nl;q=0.8',
            'Cache-Control' => 'no-cache, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'DNT' => '1',
        ], $extraHeaders);

        /*
         * Optional:
         * Put a TikTok browser cookie in Railway/.env as:
         *
         * TIKTOK_COOKIE="..."
         *
         * Do not hardcode the cookie in this PHP file.
         */
        $cookie = trim((string) env('TIKTOK_COOKIE', ''));

        if ($cookie !== '') {
            $headers['Cookie'] = $cookie;
        }

        return Http::withHeaders($headers)
            ->timeout(20)
            ->connectTimeout(8)
            ->retry(2, 300, throw: false)
            ->get($url);
    }

    private function extractVideoFromHtml(
        string $html,
        string $videoId
    ): ?array {
        $decodedHtml = html_entity_decode(
            $html,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $patterns = [
            '/<script[^>]*id=["\']__UNIVERSAL_DATA_FOR_REHYDRATION__["\'][^>]*>(.*?)<\/script>/is',
            '/<script[^>]*id=["\']SIGI_STATE["\'][^>]*>(.*?)<\/script>/is',
        ];

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern, $decodedHtml, $match)) {
                continue;
            }

            $json = trim($match[1] ?? '');

            if ($json === '') {
                continue;
            }

            $data = json_decode($json, true);

            if (!is_array($data)) {
                continue;
            }

            /*
             * Current web page data commonly contains:
             * __DEFAULT_SCOPE__.webapp.video-detail.itemInfo.itemStruct
             */
            $direct = $data['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct']
                ?? $data['__DEFAULT_SCOPE__']['webapp.video-detail']['item_info']['item_struct']
                ?? null;

            if (
                is_array($direct)
                && $this->videoMatches($direct, $videoId)
                && $this->containsStats($direct)
            ) {
                return $direct;
            }

            $found = $this->findVideoRecursively(
                $data,
                $videoId
            );

            if ($found !== null) {
                return $found;
            }
        }

        return null;
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
        $candidate = $video['id']
            ?? $video['aweme_id']
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

    private function normalizeVideoObject(array $video): array
    {
        $stats = $video['stats']
            ?? $video['statsV2']
            ?? $video['statistics']
            ?? [];

        $views = $this->firstNumber([
            $stats['playCount'] ?? null,
            $stats['play_count'] ?? null,
            $stats['viewCount'] ?? null,
            $stats['view_count'] ?? null,
        ]);

        $likes = $this->firstNumber([
            $stats['diggCount'] ?? null,
            $stats['digg_count'] ?? null,
            $stats['likeCount'] ?? null,
            $stats['like_count'] ?? null,
        ]);

        $comments = $this->firstNumber([
            $stats['commentCount'] ?? null,
            $stats['comment_count'] ?? null,
        ]);

        $shares = $this->firstNumber([
            $stats['shareCount'] ?? null,
            $stats['share_count'] ?? null,
        ]);

        /*
         * Do not fabricate counters.
         */
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
            $authorName = $author['uniqueId']
                ?? $author['unique_id']
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

            'title' => $video['desc']
                ?? $video['description']
                ?? $video['title']
                ?? null,

            'thumbnail_url' => $videoData['cover']
                ?? $videoData['originCover']
                ?? $videoData['dynamicCover']
                ?? $video['cover']
                ?? null,
        ];
    }

    private function firstNumber(array $values): ?int
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
        if (is_array($value)) {
            $value = $value['value']
                ?? $value['count']
                ?? $value['number']
                ?? null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
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
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ])
                ->withoutRedirecting()
                ->timeout(12)
                ->connectTimeout(6)
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

    private function withCacheBuster(string $url): string
    {
        $separator = str_contains($url, '?')
            ? '&'
            : '?';

        return $url
            .$separator
            .'_mashal_live='
            .rawurlencode(
                now()->valueOf()
                .'-'
                .random_int(100000, 999999)
            );
    }

    private function normalizeUrl(string $input): string
    {
        $url = trim($input);

        if ($url === '') {
            throw new RuntimeException(
                'TikTok URL ontbreekt.'
            );
        }

        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://'.$url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException(
                'Gebruik een geldige TikTok URL.'
            );
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

        return str_ends_with(
            $host,
            '.tiktok.com'
        );
    }

    private function extractVideoId(string $url): ?string
    {
        $patterns = [
            '~/(?:video|v)/(\d{10,30})(?:[/?#]|$)~i',
            '~[?&](?:item_id|video_id)=(\d{10,30})(?:&|$)~i',
        ];

        foreach ($patterns as $pattern) {
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
