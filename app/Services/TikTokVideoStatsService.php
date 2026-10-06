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
     * Resolve a TikTok URL and return a canonical video URL + video id.
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
     * Fetch TikTok again for every call.
     *
     * No Laravel cache is used here on purpose.
     */
    public function getLiveStats(string $videoUrl, string $videoId): array
    {
        $resolved = $this->resolveVideo($videoUrl);

        if ((string) $resolved['video_id'] !== (string) $videoId) {
            throw new RuntimeException('De TikTok-link hoort niet bij deze video.');
        }

        $freshUrl = $this->withCacheBuster($resolved['url']);

        $response = $this->requestTikTok($freshUrl);

        if (!$response->successful()) {
            throw new RuntimeException(
                'TikTok gaf HTTP '.$response->status().'.'
            );
        }

        $html = $response->body();

        if ($html === '') {
            throw new RuntimeException('TikTok gaf een lege response terug.');
        }

        $video = $this->extractVideoObject($html, $videoId);

        if ($video === null) {
            throw new RuntimeException(
                'De actuele TikTok-statistieken konden niet uit de response worden gelezen.'
            );
        }

        return $this->normalizeVideoObject($video);
    }

    private function requestTikTok(string $url): Response
    {
        return Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9,nl;q=0.8',
            'Cache-Control' => 'no-cache, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'DNT' => '1',
            'Upgrade-Insecure-Requests' => '1',
        ])
            ->timeout(20)
            ->connectTimeout(8)
            ->retry(2, 300, throw: false)
            ->get($url);
    }

    private function followShortTikTokUrl(string $url): string
    {
        $current = $url;

        for ($i = 0; $i < 6; $i++) {
            if (!$this->isAllowedTikTokUrl($current)) {
                throw new RuntimeException('TikTok redirectte naar een ongeldig domein.');
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

            if ($response->redirect()) {
                $location = $response->header('Location');

                if (!$location) {
                    break;
                }

                $current = $this->absoluteUrl($current, $location);
                continue;
            }

            break;
        }

        return $current;
    }

    private function extractVideoObject(string $html, string $videoId): ?array
    {
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $scriptPatterns = [
            '/<script[^>]*id=["\']__UNIVERSAL_DATA_FOR_REHYDRATION__["\'][^>]*>(.*?)<\/script>/is',
            '/<script[^>]*id=["\']SIGI_STATE["\'][^>]*>(.*?)<\/script>/is',
        ];

        foreach ($scriptPatterns as $pattern) {
            if (!preg_match($pattern, $html, $match)) {
                continue;
            }

            $json = trim($match[1] ?? '');

            if ($json === '') {
                continue;
            }

            $decoded = json_decode($json, true);

            if (!is_array($decoded)) {
                continue;
            }

            $video = $this->findVideoRecursively($decoded, $videoId);

            if ($video !== null) {
                return $video;
            }
        }

        /*
         * Fallback for TikTok page variants where the useful JSON is embedded
         * differently. Restrict the search to an area around this exact video id.
         */
        $position = strpos($html, '"'.$videoId.'"');

        if ($position === false) {
            $position = strpos($html, $videoId);
        }

        if ($position !== false) {
            $start = max(0, $position - 120000);
            $chunk = substr($html, $start, 240000);

            $stats = $this->extractStatsFromText($chunk);

            if ($this->hasAnyStat($stats)) {
                return [
                    'id' => $videoId,
                    'stats' => [
                        'playCount' => $stats['views'],
                        'diggCount' => $stats['likes'],
                        'commentCount' => $stats['comments'],
                        'shareCount' => $stats['shares'],
                    ],
                ];
            }
        }

        return null;
    }

    private function findVideoRecursively(mixed $value, string $videoId): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $candidateId = null;

        if (array_key_exists('id', $value)) {
            $candidateId = (string) $value['id'];
        } elseif (array_key_exists('aweme_id', $value)) {
            $candidateId = (string) $value['aweme_id'];
        }

        if (
            $candidateId === (string) $videoId
            && (
                isset($value['stats'])
                || isset($value['statsV2'])
                || isset($value['statistics'])
            )
        ) {
            return $value;
        }

        foreach ($value as $child) {
            if (!is_array($child)) {
                continue;
            }

            $found = $this->findVideoRecursively($child, $videoId);

            if ($found !== null) {
                return $found;
            }
        }

        return null;
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

        $author = $video['author'] ?? [];

        if (is_string($author)) {
            $authorName = $author;
        } elseif (is_array($author)) {
            $authorName = $author['uniqueId']
                ?? $author['unique_id']
                ?? $author['nickname']
                ?? null;
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

    private function extractStatsFromText(string $text): array
    {
        return [
            'views' => $this->matchNumber($text, [
                'playCount',
                'play_count',
                'viewCount',
                'view_count',
            ]),
            'likes' => $this->matchNumber($text, [
                'diggCount',
                'digg_count',
                'likeCount',
                'like_count',
            ]),
            'comments' => $this->matchNumber($text, [
                'commentCount',
                'comment_count',
            ]),
            'shares' => $this->matchNumber($text, [
                'shareCount',
                'share_count',
            ]),
        ];
    }

    private function matchNumber(string $text, array $keys): ?int
    {
        foreach ($keys as $key) {
            $quoted = preg_quote($key, '/');

            if (preg_match('/["\']'.$quoted.'["\']\s*:\s*["\']?(\d+)["\']?/i', $text, $match)) {
                return (int) $match[1];
            }
        }

        return null;
    }

    private function hasAnyStat(array $stats): bool
    {
        foreach ($stats as $value) {
            if ($value !== null) {
                return true;
            }
        }

        return false;
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

    private function withCacheBuster(string $url): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url
            .$separator
            .'_mashal_live='
            .rawurlencode((string) now()->valueOf())
            .'-'
            .random_int(100000, 999999);
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
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

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

    private function absoluteUrl(string $baseUrl, string $location): string
    {
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
        $directory = rtrim(str_replace('\\', '/', dirname($path)), '/');

        return $scheme.'://'.$host.$directory.'/'.$location;
    }
}
