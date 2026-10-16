<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Identify abandoned 4K renders without allowing a dead queued/processing
 * status file to block all uploads for several hours.
 */
final class VideoRenderGuard
{
    private const STARTUP_GRACE_SECONDS = 120;
    private const HEARTBEAT_GRACE_SECONDS = 120;

    public static function inspect(string $directory, string $token): ?array
    {
        if (! preg_match('/^[a-f0-9]{40}$/', $token)) {
            return null;
        }

        $path = $directory.DIRECTORY_SEPARATOR.$token.'.status.json';
        if (! is_file($path)) {
            return null;
        }

        $state = json_decode((string) @file_get_contents($path), true);
        if (! is_array($state)) {
            return null;
        }

        if (! in_array($state['status'] ?? null, ['queued', 'processing'], true)) {
            return $state;
        }

        $now = time();
        $created = (int) ($state['created_at'] ?? 0);
        $heartbeat = (int) ($state['heartbeat_at'] ?? $created);

        // Newly launched processes need time to appear in /proc.
        if ($created > 0 && $now - $created < self::STARTUP_GRACE_SECONDS) {
            return $state;
        }

        // Workers can be on a retiring container during a rolling deploy.
        if ($heartbeat > 0 && $now - $heartbeat < self::HEARTBEAT_GRACE_SECONDS) {
            return $state;
        }

        // Look for the exact job token in the worker or FFmpeg command line.
        // Unlike a boolean status file, a real live process cannot outlive
        // a killed Railway container without showing up in /proc.
        if (self::processIsRunning($token)) {
            return $state;
        }

        // Recheck in case the renderer updated its heartbeat during the scan.
        $latest = json_decode((string) @file_get_contents($path), true);
        if (! is_array($latest) || ! in_array($latest['status'] ?? null, ['queued', 'processing'], true)) {
            return is_array($latest) ? $latest : $state;
        }

        $lastHeartbeat = (int) ($latest['heartbeat_at'] ?? $latest['created_at'] ?? 0);
        if ($lastHeartbeat > 0 && $now - $lastHeartbeat < self::HEARTBEAT_GRACE_SECONDS) {
            return $latest;
        }

        $latest['status'] = 'failed';
        $latest['progress'] = 0;
        $latest['message'] = 'The previous video renderer stopped unexpectedly. You can start a new export now.';
        $latest['recovered_at'] = $now;

        $temp = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        if (file_put_contents($temp, json_encode($latest, JSON_THROW_ON_ERROR)) !== false) {
            @rename($temp, $path);
        } else {
            @unlink($temp);
        }

        // Only delete unfinished intermediate media. Preserve completed
        // iPhone-compatible downloads and anything from a successful render.
        foreach ([$token.'.partial.mp4', $token.'.iphone.partial.mp4'] as $part) {
            @unlink($directory.DIRECTORY_SEPARATOR.$part);
        }

        Log::info('Recovered abandoned 4K render', ['token' => $token]);

        return $latest;
    }

    private static function processIsRunning(string $token): bool
    {
        foreach (glob('/proc/[0-9]*/cmdline') ?: [] as $path) {
            $cmdline = @file_get_contents($path);
            if (! is_string($cmdline) || $cmdline === '') {
                continue;
            }

            // Both "artisan video-optimizer:render {token}" and the FFmpeg
            // input/output paths contain the token.
            if (str_contains($cmdline, $token)
                && (str_contains($cmdline, 'video-optimizer:render')
                    || str_contains($cmdline, 'ffmpeg'))) {
                return true;
            }
        }

        return false;
    }
}
