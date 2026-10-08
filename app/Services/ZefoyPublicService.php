<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Symfony\Component\Process\Process;

class ZefoyPublicService
{
    private const ALLOWED_SERVICES = [
        'hearts',
        'comments',
        'favorites',
    ];

    public function services(): array
    {
        $cacheKey = 'zefoy:public-services:v1';

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $payload = $this->run([
            'services',
        ], 18);

        if (($payload['success'] ?? false) === true) {
            Cache::put($cacheKey, $payload, now()->addSeconds(30));
        }

        return $payload;
    }

    public function search(string $service, string $videoUrl): array
    {
        $service = strtolower(trim($service));

        if (!in_array($service, self::ALLOWED_SERVICES, true)) {
            throw new RuntimeException('Ongeldige Zefoy-service.');
        }

        $videoUrl = trim($videoUrl);

        if ($videoUrl === '') {
            throw new RuntimeException('TikTok video URL ontbreekt.');
        }

        $cacheKey = 'zefoy:public-search:v1:'
            .$service.':'.sha1($videoUrl);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $payload = $this->run([
            'search',
            $service,
            $videoUrl,
        ], 24);

        /*
         * Keep successful lookup/cooldown results briefly so repeated browser
         * refreshes do not hammer Zefoy. Never cache blocked/error states.
         */
        if (($payload['success'] ?? false) === true) {
            Cache::put($cacheKey, $payload, now()->addSeconds(8));
        }

        return $payload;
    }

    private function run(array $arguments, int $timeoutSeconds): array
    {
        $script = base_path('scripts/zefoy_browser_fetch.py');

        if (!is_file($script)) {
            throw new RuntimeException(
                'scripts/zefoy_browser_fetch.py ontbreekt.'
            );
        }

        $projectPython = base_path('.venv/bin/python');
        $python = is_file($projectPython)
            ? $projectPython
            : (string) env('TIKTOK_PYTHON', '/opt/tiktok-venv/bin/python');

        if (!is_file($python) && $python !== 'python3') {
            $python = 'python3';
        }

        $process = new Process(array_merge([
            $python,
            $script,
        ], $arguments));

        $process->setTimeout($timeoutSeconds);
        $process->setIdleTimeout(null);
        $process->run();

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());
        $payload = json_decode($stdout, true);

        if (!$process->isSuccessful()) {
            $message = is_array($payload)
                ? (string) ($payload['message'] ?? 'Zefoy browserflow faalde.')
                : 'Zefoy browserflow faalde.';

            throw new RuntimeException(
                $message
                .' exit='.(string) $process->getExitCode()
                .' stderr='.mb_substr($stderr, 0, 800)
            );
        }

        if (!is_array($payload)) {
            throw new RuntimeException(
                'Zefoy browserflow gaf geen geldige JSON terug.'
            );
        }

        return $payload;
    }
}
