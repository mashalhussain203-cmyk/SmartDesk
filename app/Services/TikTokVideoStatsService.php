<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class TikTokVideoStatsService
{
    private const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36';

    public function inspect(string $input): array
    {
        $url = $this->normaliseUrl($input);
        $resolvedUrl = $this->resolveUrl($url);
        $videoId = $this->extractVideoId($resolvedUrl);

        if ($videoId === null) {
            throw new InvalidArgumentException('We konden geen TikTok-video-ID uit deze link halen.');
        }

        $canonicalUrl = 'https://www.tiktok.com/@_/video/'.$videoId;

        return [
            'video_id' => $videoId,
            'url' => $resolvedUrl,
            'canonical_url' => $canonicalUrl,
        ];
    }

    public function get(string $input, bool $fresh = false): array
    {
        $video = $this->inspect($input);
        $key = 'tiktok-video-stats:'.$video['video_id'];

        if ($fresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addSeconds(8), function () use ($video) {
            $metadata = $this->fetchOEmbed($video['url']);
            $page = $this->fetchVideoPage($video['url']);
            $stats = $this->extractStats($page, $video['video_id']);

            return [
                ...$video,
                'title' => $metadata['title'] ?? null,
                'author_name' => $metadata['author_name'] ?? null,
                'author_url' => $metadata['author_url'] ?? null,
                'thumbnail_url' => $metadata['thumbnail_url'] ?? null,
                'stats' => $stats,
                'source' => $stats !== null ? 'public_page' : 'metadata_only',
                'updated_at' => now()->toIso8601String(),
            ];
        });
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

        $response = Http::withHeaders([
            'User-Agent' => self::USER_AGENT,
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->withOptions([
            'allow_redirects' => [
                'max' => 5,
                'strict' => true,
                'referer' => true,
                'track_redirects' => true,
            ],
        ])->timeout(10)->get($url);

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
            ])->timeout(10)->get('https://www.tiktok.com/oembed', [
                'url' => $url,
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
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(12)->get($url);

            if (! $response->successful()) {
                return '';
            }

            return $response->body();
        } catch (\Throwable) {
            return '';
        }
    }

    private function extractStats(string $html, string $videoId): ?array
    {
        if ($html === '') {
            return null;
        }

        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $maps = [
            'views' => ['playCount', 'play_count', 'viewCount'],
            'likes' => ['diggCount', 'likeCount', 'like_count'],
            'comments' => ['commentCount', 'comment_count'],
            'shares' => ['shareCount', 'share_count'],
        ];

        $stats = [];

        foreach ($maps as $target => $keys) {
            $value = null;

            foreach ($keys as $key) {
                $patterns = [
                    '/["\']'.preg_quote($key, '/').'["\']\s*:\s*["\']?(\d+)["\']?/i',
                    '/\\"'.preg_quote($key, '/').'\\"\s*:\s*\\"?(\d+)\\"?/i',
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $decoded, $match)) {
                        $value = (int) $match[1];
                        break 2;
                    }
                }
            }

            $stats[$target] = $value;
        }

        if (collect($stats)->every(fn ($value) => $value === null)) {
            return null;
        }

        return $stats;
    }

    private function isTikTokHost(string $host): bool
    {
        return $host === 'tiktok.com'
            || $host === 'www.tiktok.com'
            || str_ends_with($host, '.tiktok.com');
    }
}
