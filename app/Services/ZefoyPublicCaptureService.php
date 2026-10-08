<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Symfony\Component\Process\Process;

class ZefoyPublicCaptureService
{
    public function capture(): array
    {
        $cacheKey = 'zefoy:public-capture:v1';
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        $script = base_path('scripts/zefoy_public_capture.py');

        if (!is_file($script)) {
            throw new RuntimeException('scripts/zefoy_public_capture.py ontbreekt.');
        }

        $projectPython = base_path('.venv/bin/python');
        $python = is_file($projectPython)
            ? $projectPython
            : (string) env('TIKTOK_PYTHON', '/opt/tiktok-venv/bin/python');

        if (!is_file($python) && $python !== 'python3') {
            $python = 'python3';
        }

        $process = new Process([
            $python,
            $script,
        ]);
        $process->setTimeout(22);
        $process->setIdleTimeout(null);
        $process->run();

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());
        $payload = json_decode($stdout, true);

        if (!$process->isSuccessful()) {
            $message = is_array($payload)
                ? (string) ($payload['message'] ?? 'Zefoy capture faalde.')
                : 'Zefoy capture faalde.';

            throw new RuntimeException(
                $message
                .' exit='.(string) $process->getExitCode()
                .' stderr='.mb_substr($stderr, 0, 700)
            );
        }

        if (!is_array($payload) || ($payload['success'] ?? false) !== true) {
            throw new RuntimeException(
                is_array($payload)
                    ? (string) ($payload['message'] ?? 'Zefoy capture gaf success=false.')
                    : 'Zefoy capture gaf geen geldige JSON terug.'
            );
        }

        $normalized = [
            'blocked' => (bool) ($payload['blocked'] ?? false),
            'services' => [
                'hearts' => $this->normalizeService(
                    $payload['services']['hearts'] ?? null
                ),
                'comments' => $this->normalizeService(
                    $payload['services']['comments'] ?? null
                ),
                'favorites' => $this->normalizeService(
                    $payload['services']['favorites'] ?? null
                ),
            ],
            'source' => (string) (
                $payload['source'] ?? 'zefoy-public-browser-capture'
            ),
        ];

        Cache::put($cacheKey, $normalized, now()->addSeconds(45));

        return $normalized;
    }

    private function normalizeService(mixed $item): array
    {
        if (!is_array($item)) {
            return [
                'state' => 'unknown',
                'cooldown_seconds' => null,
            ];
        }

        $state = (string) ($item['state'] ?? 'unknown');

        if (!in_array(
            $state,
            ['available', 'visible', 'unavailable', 'unknown'],
            true
        )) {
            $state = 'unknown';
        }

        $cooldown = $item['cooldown_seconds'] ?? null;
        $cooldown = is_numeric($cooldown)
            ? max(0, (int) $cooldown)
            : null;

        return [
            'state' => $state,
            'cooldown_seconds' => $cooldown,
        ];
    }
}
