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
