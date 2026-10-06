<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TikTokVideoStatsService
{
    private const ENSEMBLE_ENDPOINT = 'https://ensembledata.com/apis/tt/post/info';

    private const ALLOWED_HOSTS = [
        'tiktok.com',
        'www.tiktok.com',
        'm.tiktok.com',
        'vm.tiktok.com',
        'vt.tiktok.com',
    ];

    /**
     * Resolve a normal or short TikTok URL into a final URL + video id.
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
     * Fetch fresh TikTok stats from EnsembleData.
     *
     * This method intentionally does NOT use Laravel cache.
     * Your frontend can call this method every 4 seconds.
     */
    public function getLiveStats(string $videoUrl, string $videoId): array
    {
        $resolved = $this->resolveVideo($videoUrl);

        if ((string) $resolved['video_id'] !== (string) $videoId) {
            throw new RuntimeException('De TikTok-link hoort niet bij deze video.');
        }

        $token = trim((string) env('ENSEMBLEDATA_TOKEN', ''));

        if ($token === '') {
            throw new RuntimeException(
                'ENSEMBLEDATA_TOKEN ontbreekt. Voeg deze toe bij Railway Variables.'
            );
        }

        $response = Http::acceptJson()
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, max-age=0',
                'Pragma' => 'no-cache',
            ])
            ->connectTimeout(6)
            ->timeout(15)
            ->retry(1, 250, throw: false)
            ->get(self::ENSEMBLE_ENDPOINT, [
                'url' => $resolved['url'],
                'token' => $token,
                'new_version' => false,
                'download_video' => false,
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                'EnsembleData gaf HTTP '.$response->status().'.'
            );
        }

        $payload = $response->json();

        if (!is_array($payload)) {
            throw new RuntimeException('EnsembleData gaf een ongeldige JSON-response.');
        }

        /*
         * EnsembleData documents Post Info as:
         *
         * result = response.json()["data"]
         * post = result[0]
         */
        $data = $payload['data'] ?? null;

        if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
            $message = $this->extractProviderMessage($payload);

            throw new RuntimeException(
                $message ?: 'EnsembleData gaf geen TikTok-post terug.'
            );
        }

        $post = $data[0];

        $postId = $post['id']
            ?? $post['aweme_id']
            ?? $post['awemeId']
            ?? null;

        if (
            $postId !== null
            && (string) $postId !== (string) $videoId
        ) {
            throw new RuntimeException(
                'EnsembleData gaf een andere TikTok-video terug dan verwacht.'
            );
        }

        $stats = $this->extractStats($post);

        if ($stats['views'] === null) {
            throw new RuntimeException(
                'Geen exacte view count ontvangen van EnsembleData.'
            );
        }

        return [
            'views' => $stats['views'],
            'likes' => $stats['likes'],
            'comments' => $stats['comments'],
            'shares' => $stats['shares'],

            'author_name' => $this->extractAuthorName($post),
            'title' => $this->extractTitle($post),
            'thumbnail_url' => $this->extractThumbnail($post),
        ];
    }

    private function extractStats(array $post): array
    {
        $stats = [];

        if (isset($post['stats']) && is_array($post['stats'])) {
            $stats = $post['stats'];
        } elseif (
            isset($post['statistics'])
            && is_array($post['statistics'])
        ) {
            $stats = $post['statistics'];
        }

        return [
            'views' => $this->firstInt([
                $stats['playCount'] ?? null,
                $stats['play_count'] ?? null,
                $stats['viewCount'] ?? null,
                $stats['view_count'] ?? null,
                $post['playCount'] ?? null,
                $post['play_count'] ?? null,
                $post['viewCount'] ?? null,
                $post['view_count'] ?? null,
            ]),

            'likes' => $this->firstInt([
                $stats['diggCount'] ?? null,
                $stats['digg_count'] ?? null,
                $stats['likeCount'] ?? null,
                $stats['like_count'] ?? null,
                $post['diggCount'] ?? null,
                $post['digg_count'] ?? null,
                $post['likeCount'] ?? null,
                $post['like_count'] ?? null,
            ]),

            'comments' => $this->firstInt([
                $stats['commentCount'] ?? null,
                $stats['comment_count'] ?? null,
                $post['commentCount'] ?? null,
                $post['comment_count'] ?? null,
            ]),

            'shares' => $this->firstInt([
                $stats['shareCount'] ?? null,
                $stats['share_count'] ?? null,
                $post['shareCount'] ?? null,
                $post['share_count'] ?? null,
            ]),
        ];
    }

    private function extractAuthorName(array $post): ?string
    {
        $author = $post['author'] ?? null;

        if (is_string($author)) {
            return $author;
        }

        if (!is_array($author)) {
            return null;
        }

        return $author['uniqueId']
            ?? $author['unique_id']
            ?? $author['username']
            ?? $author['nickname']
            ?? null;
    }

    private function extractTitle(array $post): ?string
    {
        return $post['desc']
            ?? $post['description']
            ?? $post['title']
            ?? null;
    }

    private function extractThumbnail(array $post): ?string
    {
        $video = $post['video'] ?? null;

        if (is_array($video)) {
            $candidate = $video['cover']
                ?? $video['originCover']
                ?? $video['origin_cover']
                ?? $video['dynamicCover']
                ?? $video['dynamic_cover']
                ?? null;

            $url = $this->extractUrlValue($candidate);

            if ($url !== null) {
                return $url;
            }
        }

        return $this->extractUrlValue(
            $post['cover']
            ?? $post['cover_url']
            ?? null
        );
    }

    private function extractUrlValue(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (!is_array($value)) {
            return null;
        }

        if (isset($value['url_list'][0]) && is_string($value['url_list'][0])) {
            return $value['url_list'][0];
        }

        if (isset($value['urlList'][0]) && is_string($value['urlList'][0])) {
            return $value['urlList'][0];
        }

        if (isset($value[0]) && is_string($value[0])) {
            return $value[0];
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
            ]);
        }

        return null;
    }

    private function extractProviderMessage(array $payload): ?string
    {
        $candidates = [
            $payload['message'] ?? null,
            $payload['error'] ?? null,
            $payload['detail'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
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

            $current = $this->absoluteUrl($current, $location);
        }

        return $current;
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
