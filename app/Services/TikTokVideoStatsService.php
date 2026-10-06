<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TikTokVideoStatsService
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36';

    /**
     * Controleert een TikTok-link, volgt korte links en haalt de video-ID eruit.
     */
    public function inspect(string $input): array
    {
        $url = $this->normaliseUrl($input);
        $resolvedUrl = $this->resolveUrl($url);
        $videoId = $this->extractVideoId($resolvedUrl);

        if ($videoId === null) {
            throw new InvalidArgumentException('We konden geen TikTok-video-ID uit deze link halen.');
        }

        return [
            'video_id' => $videoId,
            'url' => $resolvedUrl,
            'canonical_url' => 'https://www.tiktok.com/@_/video/'.$videoId,
        ];
    }

    /**
     * Haalt ELKE AANROEP opnieuw de actuele publieke TikTok-pagina op.
     * Er wordt bewust geen Laravel Cache gebruikt voor de live counter.
     */
    public function getLive(string $input): array
    {
        $video = $this->inspect($input);

        // Beide bronnen worden per live request opnieuw opgehaald.
        $metadata = $this->fetchOEmbed($video['url']);
        $html = $this->fetchVideoPage($video['url']);
        $pageData = $this->extractVideoData($html, $video['video_id']);

        $stats = $pageData['stats'] ?? null;

        return [
            ...$video,
            'title' => $pageData['title'] ?? $metadata['title'] ?? null,
            'author_name' => $pageData['author_name'] ?? $metadata['author_name'] ?? null,
            'author_url' => $metadata['author_url'] ?? null,
            'thumbnail_url' => $pageData['thumbnail_url'] ?? $metadata['thumbnail_url'] ?? null,
            'stats' => $stats,
            'source' => $stats !== null ? 'public_page_live' : 'metadata_only',
            'updated_at' => now()->toIso8601String(),
            'request_id' => (string) Str::uuid(),
        ];
    }

    public function normaliseUrl(string $input): string
    {
        $input = trim($input);

        if ($input === '') {
            throw new InvalidArgumentException('Plak eerst een TikTok-link.');
        }

        if (! Str::startsWith($input, ['http://', 'https://'])) {
            $input = 'https://'.$input;
        }

        $parts = parse_url($input);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! $this->isTikTokHost($host)) {
            throw new InvalidArgumentException('Gebruik een geldige tiktok.com-link.');
        }

        return $input;
    }

    public function extractVideoId(string $url): ?string
    {
        if (preg_match('~/(?:video|v)/(\d{12,24})(?:[/?#]|$)~', $url, $matches)) {
            return $matches[1];
        }

        if (preg_match('~(?:item_id|video_id)=(\d{12,24})~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function resolveUrl(string $url): string
    {
        if ($this->extractVideoId($url) !== null) {
            return $url;
        }

        $response = Http::withHeaders($this->browserHeaders())
            ->withOptions([
                'allow_redirects' => [
                    'max' => 5,
                    'strict' => true,
                    'referer' => true,
                    'track_redirects' => true,
                ],
            ])
            ->timeout(12)
            ->get($url);

        $effectiveUrl = (string) ($response->handlerStats()['url'] ?? $url);
        $host = strtolower((string) parse_url($effectiveUrl, PHP_URL_HOST));

        if (! $this->isTikTokHost($host)) {
            throw new InvalidArgumentException('Deze TikTok-link verwijst niet naar een geldige TikTok-pagina.');
        }

        return $effectiveUrl;
    }

    private function fetchOEmbed(string $url): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'application/json',
                'Cache-Control' => 'no-cache, no-store, max-age=0',
                'Pragma' => 'no-cache',
            ])
                ->timeout(10)
                ->get('https://www.tiktok.com/oembed', [
                    'url' => $url,
                    '_ts' => now()->valueOf(),
                ]);

            if (! $response->successful()) {
                return [];
            }

            return $response->json() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function fetchVideoPage(string $url): string
    {
        try {
            $separator = str_contains($url, '?') ? '&' : '?';
            $liveUrl = $url.$separator.'_mashal_live='.now()->valueOf();

            $response = Http::withHeaders($this->browserHeaders())
                ->timeout(15)
                ->get($liveUrl);

            if (! $response->successful()) {
                return '';
            }

            return $response->body();
        } catch (\Throwable) {
            return '';
        }
    }

    private function browserHeaders(): array
    {
        return [
            'User-Agent' => self::USER_AGENT,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Cache-Control' => 'no-cache, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ];
    }

    /**
     * Probeert eerst de JSON-data van TikTok te lezen en gebruikt daarna
     * een beperkte regex-fallback rond de gevraagde video-ID.
     */
    private function extractVideoData(string $html, string $videoId): array
    {
        if ($html === '') {
            return [];
        }

        foreach ($this->extractJsonScripts($html) as $json) {
            $node = $this->findVideoNode($json, $videoId);

            if ($node !== null) {
                $stats = $this->statsFromNode($node);

                if ($stats !== null) {
                    return [
                        'stats' => $stats,
                        'title' => $this->firstString($node, ['desc', 'description', 'title']),
                        'author_name' => $this->authorNameFromNode($node),
                        'thumbnail_url' => $this->thumbnailFromNode($node),
                    ];
                }
            }
        }

        $stats = $this->extractStatsNearVideoId($html, $videoId);

        return $stats === null ? [] : ['stats' => $stats];
    }

    /**
     * TikTok gebruikt door de tijd heen verschillende JSON scriptblokken.
     */
    private function extractJsonScripts(string $html): array
    {
        $decodedHtml = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $results = [];

        $patterns = [
            '/<script[^>]+id=["\']__UNIVERSAL_DATA_FOR_REHYDRATION__["\'][^>]*>(.*?)<\/script>/is',
            '/<script[^>]+id=["\']SIGI_STATE["\'][^>]*>(.*?)<\/script>/is',
            '/<script[^>]+type=["\']application\/json["\'][^>]*>(.*?)<\/script>/is',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match_all($pattern, $decodedHtml, $matches)) {
                continue;
            }

            foreach ($matches[1] as $payload) {
                $payload = trim($payload);

                if ($payload === '' || ($payload[0] ?? '') !== '{') {
                    continue;
                }

                $json = json_decode($payload, true);

                if (is_array($json)) {
                    $results[] = $json;
                }
            }
        }

        return $results;
    }

    private function findVideoNode(mixed $node, string $videoId): ?array
    {
        if (! is_array($node)) {
            return null;
        }

        // Veel SIGI_STATE responses hebben ItemModule[videoId].
        if (isset($node[$videoId]) && is_array($node[$videoId])) {
            $candidate = $node[$videoId];

            if ($this->looksLikeVideoNode($candidate, $videoId)) {
                return $candidate;
            }
        }

        if ($this->looksLikeVideoNode($node, $videoId)) {
            return $node;
        }

        foreach ($node as $child) {
            if (! is_array($child)) {
                continue;
            }

            $found = $this->findVideoNode($child, $videoId);

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function looksLikeVideoNode(array $node, string $videoId): bool
    {
        foreach (['id', 'itemId', 'aweme_id', 'awemeId', 'videoId'] as $key) {
            if (isset($node[$key]) && (string) $node[$key] === $videoId) {
                return true;
            }
        }

        return false;
    }

    private function statsFromNode(array $node): ?array
    {
        $sources = [$node];

        foreach (['stats', 'statistics', 'statsV2'] as $key) {
            if (isset($node[$key]) && is_array($node[$key])) {
                $sources[] = $node[$key];
            }
        }

        $map = [
            'views' => ['playCount', 'play_count', 'viewCount', 'view_count'],
            'likes' => ['diggCount', 'digg_count', 'likeCount', 'like_count'],
            'comments' => ['commentCount', 'comment_count'],
            'shares' => ['shareCount', 'share_count'],
        ];

        $stats = [];

        foreach ($map as $target => $keys) {
            $stats[$target] = null;

            foreach ($sources as $source) {
                foreach ($keys as $key) {
                    if (! array_key_exists($key, $source)) {
                        continue;
                    }

                    $value = $this->numericValue($source[$key]);

                    if ($value !== null) {
                        $stats[$target] = $value;
                        break 2;
                    }
                }
            }
        }

        if (collect($stats)->every(fn ($value) => $value === null)) {
            return null;
        }

        return $stats;
    }

    private function numericValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value) || (is_string($value) && ctype_digit($value))) {
            return (int) $value;
        }

        if (is_array($value)) {
            foreach (['value', 'count', 'total'] as $key) {
                if (isset($value[$key])) {
                    $parsed = $this->numericValue($value[$key]);

                    if ($parsed !== null) {
                        return $parsed;
                    }
                }
            }
        }

        return null;
    }

    private function extractStatsNearVideoId(string $html, string $videoId): ?array
    {
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $position = strpos($decoded, $videoId);

        if ($position !== false) {
            $start = max(0, $position - 30000);
            $decoded = substr($decoded, $start, 60000);
        }

        $map = [
            'views' => ['playCount', 'play_count', 'viewCount', 'view_count'],
            'likes' => ['diggCount', 'digg_count', 'likeCount', 'like_count'],
            'comments' => ['commentCount', 'comment_count'],
            'shares' => ['shareCount', 'share_count'],
        ];

        $stats = [];

        foreach ($map as $target => $keys) {
            $stats[$target] = null;

            foreach ($keys as $key) {
                $quotedKey = preg_quote($key, '/');
                $patterns = [
                    '/["\']'.$quotedKey.'["\']\s*:\s*["\']?(\d+)["\']?/i',
                    '/\\"'.$quotedKey.'\\"\s*:\s*\\"?(\d+)\\"?/i',
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $decoded, $match)) {
                        $stats[$target] = (int) $match[1];
                        break 2;
                    }
                }
            }
        }

        if (collect($stats)->every(fn ($value) => $value === null)) {
            return null;
        }

        return $stats;
    }

    private function firstString(array $node, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($node[$key]) && is_string($node[$key]) && trim($node[$key]) !== '') {
                return trim($node[$key]);
            }
        }

        return null;
    }

    private function authorNameFromNode(array $node): ?string
    {
        foreach (['author', 'authorInfo'] as $key) {
            if (! isset($node[$key]) || ! is_array($node[$key])) {
                continue;
            }

            foreach (['uniqueId', 'unique_id', 'nickname'] as $nameKey) {
                if (isset($node[$key][$nameKey]) && is_string($node[$key][$nameKey])) {
                    return trim($node[$key][$nameKey]);
                }
            }
        }

        return null;
    }

    private function thumbnailFromNode(array $node): ?string
    {
        $candidates = [];

        if (isset($node['video']) && is_array($node['video'])) {
            $candidates[] = $node['video'];
        }

        $candidates[] = $node;

        foreach ($candidates as $candidate) {
            foreach (['cover', 'originCover', 'dynamicCover', 'thumbnail'] as $key) {
                if (! isset($candidate[$key])) {
                    continue;
                }

                if (is_string($candidate[$key]) && str_starts_with($candidate[$key], 'http')) {
                    return $candidate[$key];
                }

                if (is_array($candidate[$key])) {
                    foreach (['urlList', 'url_list'] as $listKey) {
                        $first = $candidate[$key][$listKey][0] ?? null;

                        if (is_string($first) && str_starts_with($first, 'http')) {
                            return $first;
                        }
                    }
                }
            }
        }

        return null;
    }

    private function isTikTokHost(string $host): bool
    {
        return $host === 'tiktok.com'
            || $host === 'www.tiktok.com'
            || str_ends_with($host, '.tiktok.com');
    }
}
