<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Symfony\Component\Process\Process;

class YouTubeLiveCountsService
{
    public function searchChannels(string $query): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2 || mb_strlen($query) > 255) {
            return [];
        }

        if (preg_match('/[\x00-\x1F\x7F]/u', $query)) {
            throw new RuntimeException('Ongeldige YouTube zoekterm.');
        }

        $cacheKey = 'youtube-livecounts:search:v2:'
            .sha1(mb_strtolower($query));

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $payload = $this->runPython(
            'scripts/livecounts_youtube_search.py',
            [$query],
            38
        );

        if (($payload['success'] ?? false) !== true) {
            throw new RuntimeException(
                (string) ($payload['message'] ?? 'YouTube kanaal zoeken faalde.')
            );
        }

        $results = $payload['results'] ?? [];
        if (!is_array($results)) {
            return [];
        }

        $normalized = [];
        $seen = [];

        foreach ($results as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = trim((string) ($item['id'] ?? $item['channel_id'] ?? ''));
            if (!preg_match('/^UC[A-Za-z0-9_-]{22}$/', $id)) {
                continue;
            }

            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;

            $title = trim((string) (
                $item['title']
                ?? $item['display_name']
                ?? $item['username']
                ?? 'YouTube-kanaal'
            ));

            $avatar = $item['avatar']
                ?? $item['avatar_url']
                ?? $item['thumbnail']
                ?? null;

            $normalized[] = [
                'id' => $id,
                'title' => $title !== '' ? $title : 'YouTube-kanaal',
                'avatar' => is_string($avatar) && $avatar !== ''
                    ? $avatar
                    : null,
                'url' => 'https://www.youtube.com/channel/'.$id,
            ];

            if (count($normalized) >= 8) {
                break;
            }
        }

        if ($normalized !== []) {
            Cache::put($cacheKey, $normalized, now()->addSeconds(60));
        }

        return $normalized;
    }

    public function searchVideos(string $query): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2 || mb_strlen($query) > 255) {
            return [];
        }

        if (preg_match('/[\x00-\x1F\x7F]/u', $query)) {
            throw new RuntimeException('Ongeldige YouTube zoekterm.');
        }

        $directVideoId = $this->extractVideoIdFromQuery($query);

        if ($directVideoId !== null) {
            return [[
                'id' => $directVideoId,
                'title' => 'YouTube-video',
                'thumbnail' => 'https://i.ytimg.com/vi/'.$directVideoId.'/hqdefault.jpg',
                'channel' => null,
                'url' => 'https://www.youtube.com/watch?v='.$directVideoId,
            ]];
        }

        $cacheKey = 'youtube-livecounts:video-search:v1:'
            .sha1(mb_strtolower($query));

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $payload = $this->runPython(
            'scripts/livecounts_youtube_view_search.py',
            [$query],
            38
        );

        if (($payload['success'] ?? false) !== true) {
            throw new RuntimeException(
                (string) ($payload['message'] ?? 'YouTube video zoeken faalde.')
            );
        }

        $results = $payload['results'] ?? [];
        if (!is_array($results)) {
            return [];
        }

        $normalized = [];
        $seen = [];

        foreach ($results as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = trim((string) (
                $item['id']
                ?? $item['video_id']
                ?? $item['videoId']
                ?? ''
            ));

            if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
                continue;
            }

            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;

            $title = trim((string) (
                $item['title']
                ?? $item['name']
                ?? $item['display_name']
                ?? 'YouTube-video'
            ));

            $thumbnail = $item['thumbnail']
                ?? $item['thumbnail_url']
                ?? $item['image']
                ?? $item['avatar']
                ?? null;

            $channel = trim((string) (
                $item['channel']
                ?? $item['channelTitle']
                ?? $item['channel_name']
                ?? $item['author']
                ?? ''
            ));

            $normalized[] = [
                'id' => $id,
                'title' => $title !== '' ? $title : 'YouTube-video',
                'thumbnail' => is_string($thumbnail) && $thumbnail !== ''
                    ? $thumbnail
                    : 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg',
                'channel' => $channel !== '' ? $channel : null,
                'url' => 'https://www.youtube.com/watch?v='.$id,
            ];

            if (count($normalized) >= 8) {
                break;
            }
        }

        if ($normalized !== []) {
            Cache::put($cacheKey, $normalized, now()->addSeconds(60));
        }

        return $normalized;
    }

    public function getVideoStats(string $videoId): array
    {
        if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            throw new RuntimeException('Ongeldig YouTube video-ID.');
        }

        $cacheKey = 'youtube-livecounts:video-stats:v1:'.$videoId;
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $lock = Cache::lock(
            'youtube-livecounts:video-stats-lock:v1:'.$videoId,
            35
        );

        if (!$lock->get()) {
            for ($attempt = 0; $attempt < 80; $attempt++) {
                usleep(250000);
                $cached = Cache::get($cacheKey);

                if (is_array($cached)) {
                    return $cached;
                }
            }

            throw new RuntimeException(
                'YouTube video refresh draait al maar leverde nog geen snapshot.'
            );
        }

        try {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }

            $payload = $this->runPython(
                'scripts/livecounts_youtube_view_browser_fetch.py',
                [$videoId],
                30
            );

            if (($payload['success'] ?? false) !== true) {
                throw new RuntimeException(
                    (string) (
                        $payload['message']
                        ?? 'YouTube live views ophalen faalde.'
                    )
                );
            }

            $stats = $payload['stats'] ?? null;
            if (!is_array($stats)) {
                throw new RuntimeException(
                    'Livecounts YouTube video response bevatte geen stats.'
                );
            }

            foreach (['views', 'likes', 'dislikes', 'comments'] as $key) {
                if (!array_key_exists($key, $stats) || $stats[$key] === null) {
                    throw new RuntimeException(
                        'Livecounts YouTube video mist teller: '.$key
                    );
                }
            }

            $result = [
                'id' => $videoId,
                'title' => $payload['title'] ?? null,
                'thumbnail' => $payload['thumbnail']
                    ?? 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg',
                'channel' => $payload['channel'] ?? null,
                'description' => $payload['description'] ?? null,
                'url' => 'https://www.youtube.com/watch?v='.$videoId,
                'views' => (int) $stats['views'],
                'likes' => (int) $stats['likes'],
                'dislikes' => (int) $stats['dislikes'],
                'comments' => (int) $stats['comments'],
                'source' => (string) (
                    $payload['source']
                    ?? 'livecounts-youtube-view-public-page-rendered'
                ),
            ];

            Cache::put($cacheKey, $result, now()->addSeconds(3));

            return $result;
        } finally {
            $lock->release();
        }
    }

    public function getChannelStats(string $channelId): array
    {
        if (!preg_match('/^UC[A-Za-z0-9_-]{22}$/', $channelId)) {
            throw new RuntimeException('Ongeldig YouTube kanaal-ID.');
        }

        $cacheKey = 'youtube-livecounts:stats:v1:'.$channelId;
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $lock = Cache::lock(
            'youtube-livecounts:stats-lock:v1:'.$channelId,
            35
        );

        if (!$lock->get()) {
            for ($attempt = 0; $attempt < 80; $attempt++) {
                usleep(250000);
                $cached = Cache::get($cacheKey);

                if (is_array($cached)) {
                    return $cached;
                }
            }

            throw new RuntimeException(
                'YouTube live refresh draait al maar leverde nog geen snapshot.'
            );
        }

        try {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }

            $payload = $this->runPython(
                'scripts/livecounts_youtube_browser_fetch.py',
                [$channelId],
                30
            );

            if (($payload['success'] ?? false) !== true) {
                throw new RuntimeException(
                    (string) ($payload['message'] ?? 'YouTube live stats ophalen faalde.')
                );
            }

            $stats = $payload['stats'] ?? null;
            if (!is_array($stats)) {
                throw new RuntimeException(
                    'Livecounts YouTube response bevatte geen stats.'
                );
            }

            foreach (['subscribers', 'views', 'videos', 'goal'] as $key) {
                if (!array_key_exists($key, $stats) || $stats[$key] === null) {
                    throw new RuntimeException(
                        'Livecounts YouTube mist teller: '.$key
                    );
                }
            }

            $result = [
                'id' => $channelId,
                'title' => $payload['title'] ?? null,
                'avatar' => $payload['avatar'] ?? null,
                'banner' => $payload['banner'] ?? null,
                'description' => $payload['description'] ?? null,
                'url' => 'https://www.youtube.com/channel/'.$channelId,
                'subscribers' => (int) $stats['subscribers'],
                'views' => (int) $stats['views'],
                'videos' => (int) $stats['videos'],
                'goal' => (int) $stats['goal'],
                'hidden' => false,
                'source' => (string) (
                    $payload['source']
                    ?? 'livecounts-youtube-public-page-rendered'
                ),
            ];

            Cache::put($cacheKey, $result, now()->addSeconds(5));

            return $result;
        } finally {
            $lock->release();
        }
    }

    private function extractVideoIdFromQuery(string $query): ?string
    {
        $query = trim($query);

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $query)) {
            return $query;
        }

        $candidate = $query;

        if (!preg_match('#^https?://#i', $candidate)) {
            if (preg_match('#^(?:www\.)?(?:youtube\.com|youtu\.be)/#i', $candidate)) {
                $candidate = 'https://'.$candidate;
            } else {
                return null;
            }
        }

        $parts = parse_url($candidate);
        if (!is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $path = trim((string) ($parts['path'] ?? ''), '/');

        if ($host === 'youtu.be') {
            $segment = explode('/', $path)[0] ?? '';

            return preg_match('/^[A-Za-z0-9_-]{11}$/', $segment)
                ? $segment
                : null;
        }

        if (!in_array($host, [
            'youtube.com',
            'm.youtube.com',
            'music.youtube.com',
        ], true)) {
            return null;
        }

        parse_str((string) ($parts['query'] ?? ''), $params);

        $watchId = trim((string) ($params['v'] ?? ''));
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $watchId)) {
            return $watchId;
        }

        if (preg_match(
            '#^(?:shorts|embed|live)/([A-Za-z0-9_-]{11})(?:/|$)#',
            $path,
            $match
        )) {
            return $match[1];
        }

        return null;
    }

    private function runPython(
        string $relativeScript,
        array $arguments,
        int $timeoutSeconds
    ): array {
        $script = base_path($relativeScript);

        if (!is_file($script)) {
            throw new RuntimeException($relativeScript.' ontbreekt.');
        }

        $projectPython = base_path('.venv/bin/python');
        $python = is_file($projectPython)
            ? $projectPython
            : (string) env('TIKTOK_PYTHON', '/opt/tiktok-venv/bin/python');

        if (!is_file($python) && $python !== 'python3') {
            $python = 'python3';
        }

        $process = new Process(array_merge(
            [$python, $script],
            array_map('strval', $arguments)
        ));
        $process->setTimeout($timeoutSeconds);
        $process->setIdleTimeout(null);
        $process->run();

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());
        $payload = json_decode($stdout, true);

        if (!$process->isSuccessful()) {
            $message = is_array($payload)
                ? (string) ($payload['message'] ?? 'YouTube browser-helper faalde.')
                : 'YouTube browser-helper faalde.';

            throw new RuntimeException(
                $message
                .' exit='.(string) $process->getExitCode()
                .' stderr='.mb_substr($stderr, 0, 900)
            );
        }

        if (!is_array($payload)) {
            throw new RuntimeException(
                'YouTube browser-helper gaf geen geldige JSON terug.'
            );
        }

        return $payload;
    }
}
