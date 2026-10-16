<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Throwable;

class Render4k120Video extends Command
{
    protected $signature = 'video-optimizer:render {token}';

    protected $description = 'Render private 4K/120 FPS videos in the background';

    public function handle(): int
    {
        $token = (string) $this->argument('token');
        if (! preg_match('/^[a-f0-9]{40}$/', $token)) {
            return self::FAILURE;
        }

        $directory = storage_path('app/private/video-optimizer');
        $statusFile = $directory.'/'.$token.'.status.json';
        $output = $directory.'/'.$token.'.mp4';
        $partial = $directory.'/'.$token.'.partial.mp4';
        $iphoneOutput = $directory.'/'.$token.'.iphone.mp4';
        $iphonePartial = $directory.'/'.$token.'.iphone.partial.mp4';

        if (! is_file($statusFile)) {
            return self::FAILURE;
        }

        $state = json_decode((string) file_get_contents($statusFile), true);
        $name = (string) ($state['source'] ?? '');
        if (! is_array($state) || ($state['status'] ?? '') !== 'queued'
            || ! preg_match('/^[a-f0-9]{40}\.source\.(mp4|mov)$/', $name)
            || ! str_starts_with($name, $token.'.')) {
            return self::FAILURE;
        }

        $source = $directory.'/'.$name;
        if (! is_file($source)) {
            return self::FAILURE;
        }

        $lock = fopen($directory.'/4k120-render.lock', 'c');
        if ($lock === false) {
            return self::FAILURE;
        }

        try {
            if (! flock($lock, LOCK_EX)) {
                throw new \RuntimeException('Render lock unavailable.');
            }

            $this->saveStatus($statusFile, array_merge($state, [
                'status' => 'processing', 'progress' => 1,
                'heartbeat_at' => time(),
            ]));

            $probe = new Process([
                'ffprobe', '-v', 'error', '-select_streams', 'v:0',
                '-show_entries', 'stream=width,height,avg_frame_rate', '-of', 'json', $source,
            ]);
            $probe->setTimeout(30);
            $probe->mustRun();
            $stream = json_decode($probe->getOutput(), true)['streams'][0] ?? [];
            if ((int) ($stream['width'] ?? 0) <= 0 || (int) ($stream['height'] ?? 0) <= 0) {
                throw new \RuntimeException('Invalid input dimensions.');
            }

            $portrait = (int) $stream['height'] > (int) $stream['width'];
            $width = $portrait ? 2160 : 3840;
            $height = $portrait ? 3840 : 2160;
            $duration = (float) ($state['duration'] ?? 0);
            $sourceRate = explode('/', (string) ($stream['avg_frame_rate'] ?? '0/1'));
            $sourceFps = (float) ($sourceRate[1] ?? 0) > 0
                ? (float) ($sourceRate[0] ?? 0) / (float) $sourceRate[1] : 0.0;
            $needsInterpolation = $sourceFps < 119.5;
            $renderState = $state;

            // Export a separate Photos-compatible MP4 from the ORIGINAL source.
            // H.264 Main@4.2, 1080p/60 FPS, 8-bit yuv420p and AAC-LC are
            // dramatically more broadly decodable on iOS than 4K/120 AVC.
            // Never relabel this copy as 4K/120 FPS.
            try {
                $phoneWidth = $portrait ? 1080 : 1920;
                $phoneHeight = $portrait ? 1920 : 1080;
                $phoneFilter = 'fps=60,scale='.$phoneWidth.':'.$phoneHeight
                    .':force_original_aspect_ratio=decrease:force_divisible_by=2:flags=bicubic,'
                    .'setsar=1,format=yuv420p';

                $phoneEncode = new Process([
                    'ffmpeg', '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
                    '-threads', '1', '-filter_threads', '1', '-i', $source,
                    '-map', '0:v:0', '-map', '0:a?', '-sn', '-dn',
                    '-map_metadata', '-1', '-map_chapters', '-1',
                    '-vf', $phoneFilter, '-fps_mode', 'cfr',
                    '-c:v', 'libx264', '-threads:v', '2', '-preset', 'veryfast',
                    '-profile:v', 'main', '-level:v', '4.2', '-pix_fmt', 'yuv420p',
                    '-maxrate', '3M', '-bufsize', '6M', '-r', '60',
                    '-c:a', 'aac', '-b:a', '160k', '-ar', '48000',
                    '-movflags', '+faststart', '-video_track_timescale', '60000',
                    '-f', 'mp4', $iphonePartial,
                ]);
                $phoneEncode->setTimeout(3600);
                $phoneEncode->run();

                if (! $phoneEncode->isSuccessful() || ! is_file($iphonePartial)
                    || filesize($iphonePartial) < 1024) {
                    throw new \RuntimeException('iPhone MP4 encoding failed: '
                        .mb_substr($phoneEncode->getErrorOutput(), 0, 250));
                }

                $checkPhone = new Process([
                    'ffprobe', '-v', 'error', '-select_streams', 'v:0',
                    '-show_entries', 'stream=codec_name,profile,level,width,height,pix_fmt,avg_frame_rate:format=duration',
                    '-of', 'json', $iphonePartial,
                ]);
                $checkPhone->setTimeout(30);
                $checkPhone->mustRun();
                $phoneInfo = json_decode($checkPhone->getOutput(), true);
                $phoneVideo = $phoneInfo['streams'][0] ?? [];
                $phoneRate = explode('/', (string) ($phoneVideo['avg_frame_rate'] ?? '0/1'));
                $phoneFps = (float) ($phoneRate[1] ?? 0) > 0
                    ? (float) $phoneRate[0] / (float) $phoneRate[1] : 0.0;

                if (($phoneVideo['codec_name'] ?? null) !== 'h264'
                    || ($phoneVideo['pix_fmt'] ?? null) !== 'yuv420p'
                    || (int) ($phoneVideo['level'] ?? 999) > 42
                    || (int) ($phoneVideo['width'] ?? 0) > 1920
                    || (int) ($phoneVideo['height'] ?? 0) > 1920
                    || abs($phoneFps - 60) > 0.1
                    || abs((float) ($phoneInfo['format']['duration'] ?? 0) - $duration) > 1.0) {
                    throw new \RuntimeException('iPhone MP4 profile verification failed.');
                }

                // Verify actual decoder compatibility, not merely MP4 metadata.
                $decodePhone = new Process([
                    'ffmpeg', '-v', 'error', '-xerror', '-threads', '1',
                    '-i', $iphonePartial, '-map', '0:v:0',
                    '-t', '2', '-f', 'null', '-',
                ]);
                $decodePhone->setTimeout(90);
                $decodePhone->mustRun();

                if (! rename($iphonePartial, $iphoneOutput)) {
                    throw new \RuntimeException('Cannot finalize iPhone-compatible MP4.');
                }

                $renderState = array_merge($state, [
                    'iphone_ready' => true,
                    'iphone_width' => (int) $phoneVideo['width'],
                    'iphone_height' => (int) $phoneVideo['height'],
                    'iphone_fps' => round($phoneFps, 2),
                ]);
                $this->saveStatus($statusFile, array_merge($renderState, [
                    'status' => 'processing', 'progress' => 5,
                ]));
            } catch (Throwable $phoneError) {
                @unlink($iphonePartial);
                @unlink($iphoneOutput);
                Log::warning('Phone-compatible MP4 export failed', [
                    'render_token' => $token,
                    'reason' => mb_substr($phoneError->getMessage(), 0, 300),
                ]);
                $renderState['iphone_error'] = 'Could not produce the phone-compatible video.';
                $this->saveStatus($statusFile, array_merge($renderState, [
                    'status' => 'processing', 'progress' => 1,
                ]));
            }

            // Motion-compensated interpolation creates intermediate frames from estimated
            // motion, not simply blending two frames (which causes ghosting).
            // Work at source size: doing motion search on 4K frames exhausts 1 GB RAM.
            // Lanczos + moderate sharpening improves perceived clarity but is NOT
            // learned AI super-resolution and cannot recover lost source detail.
            $filters = [];
            if ((int) $stream['width'] < 1920 || (int) $stream['height'] < 1080) {
                $filters[] = 'hqdn3d=1.2:1.2:4:4';
            }
            // Sharpen before upscaling to avoid a second 4K-sized filter buffer.
            $filters[] = 'unsharp=5:5:0.35:5:5:0.0';
            if ($needsInterpolation) {
                $filters[] = 'minterpolate=fps=120:mi_mode=mci:mc_mode=obmc:'
                    .'me_mode=bidir:me=epzs:mb_size=16:vsbmc=1:'
                    .'scd=fdiff:scd_threshold=10';
            }
            $filters[] = 'scale='.$width.':'.$height
                .':force_original_aspect_ratio=decrease:flags=lanczos';
            $filters[] = 'pad='.$width.':'.$height.':(ow-iw)/2:(oh-ih)/2';
            $filters[] = 'setsar=1';
            $filters[] = 'format=yuv420p';
            $filter = implode(',', $filters);

            // Do not silently lower the quality to ultrafast / 8 Mbit for longer
            // clips. Reject insufficient disk space with a clear error instead.
            $maxRateMbps = $duration > 90 ? 12 : 18;
            $estimatedBytes = (int) ceil($duration * (($maxRateMbps + 0.4) * 1000000 / 8) * 1.2);
            $freeBytes = disk_free_space($directory);
            if ($freeBytes !== false && $freeBytes < $estimatedBytes + (20 * 1048576)) {
                throw new \RuntimeException('RENDER_STORAGE_LOW');
            }

            // Constrain x264 encoder threads (not only input decoder threads)
            // to stay within the 1 GB Railway container memory limit.
            $ffmpeg = new Process([
                'ffmpeg', '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
                '-threads', '1', '-filter_threads', '1',
                '-i', $source, '-map', '0:v:0', '-map', '0:a?', '-sn', '-dn',
                '-vf', $filter, '-fps_mode', 'cfr', '-r', '120',
                '-c:v', 'libx264', '-threads:v', '2',
                '-preset', 'superfast', '-crf', '18',
                '-maxrate', $maxRateMbps.'M',
                '-bufsize', ($maxRateMbps * 2).'M',
                '-pix_fmt', 'yuv420p',
                '-c:a', 'aac', '-b:a', '160k', '-movflags', '+faststart',
                '-progress', 'pipe:1', '-nostats', '-f', 'mp4', $partial,
            ]);
            $ffmpeg->setTimeout(10800);
            $previousProgress = 1;
            $lastHeartbeat = time();
            $ffmpeg->run(function (string $type, string $buffer) use ($statusFile, $renderState, $duration, &$previousProgress, &$lastHeartbeat): void {
                if ($type !== Process::OUT || $duration <= 0) {
                    return;
                }

                if (preg_match_all('/out_time_us=(\d+)/', $buffer, $matches)) {
                    $microseconds = (int) end($matches[1]);
                    $progress = min(98, max(1, (int) (($microseconds / 1000000) / $duration * 98)));
                    if ($progress >= $previousProgress + 3 || time() - $lastHeartbeat >= 15) {
                        $previousProgress = $progress;
                        $lastHeartbeat = time();
                        $this->saveStatus($statusFile, array_merge($renderState, [
                            'status' => 'processing', 'progress' => $progress,
                            'heartbeat_at' => $lastHeartbeat,
                        ]));
                    }
                }
            });

            if (! $ffmpeg->isSuccessful() || ! is_file($partial) || filesize($partial) < 1024) {
                $stderr = $ffmpeg->getErrorOutput();
                if (str_contains($stderr, 'No space left on device')) {
                    throw new \RuntimeException('RENDER_STORAGE_LOW');
                }
                if (str_contains($stderr, 'Cannot allocate memory') || $ffmpeg->getExitCode() === 137) {
                    throw new \RuntimeException('RENDER_MEMORY_LOW');
                }
                throw new \RuntimeException('FFmpeg error: '.mb_substr($stderr, 0, 300));
            }

            $verification = new Process([
                'ffprobe', '-v', 'error', '-select_streams', 'v:0',
                '-show_entries', 'stream=width,height,avg_frame_rate:format=duration',
                '-of', 'json', $partial,
            ]);
            $verification->setTimeout(30);
            $verification->mustRun();
            $info = json_decode($verification->getOutput(), true);
            $video = $info['streams'][0] ?? [];
            $parts = explode('/', (string) ($video['avg_frame_rate'] ?? '0/1'));
            $fps = (float) ($parts[1] ?? 0) > 0 ? (float) $parts[0] / (float) $parts[1] : 0;
            $finishedDuration = (float) ($info['format']['duration'] ?? 0);
            if ((int) ($video['width'] ?? 0) !== $width
                || (int) ($video['height'] ?? 0) !== $height
                || abs($fps - 120) > 0.1
                || abs($finishedDuration - $duration) > 1) {
                throw new \RuntimeException('Output resolution, FPS, or duration validation failed.');
            }

            if (! rename($partial, $output)) {
                throw new \RuntimeException('Unable to finalize MP4.');
            }

            $this->saveStatus($statusFile, array_merge($renderState, [
                'status' => 'ready', 'progress' => 100,
                'width' => $width, 'height' => $height, 'fps' => round($fps, 2),
                'duration' => $finishedDuration,
                'frame_generation' => $needsInterpolation ? 'motion_compensated' : 'source_fps',
                'upscaling' => 'lanczos_with_unsharp',
            ]));

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::warning('4K 120 FPS processing failed', [
                'render_token' => $token, 'message' => mb_substr($e->getMessage(), 0, 300),
            ]);
            $this->saveStatus($statusFile, array_merge($renderState ?? $state, [
                'status' => 'failed', 'progress' => 0,
                'message' => match ($e->getMessage()) {
                    'RENDER_STORAGE_LOW' => 'The Railway volume has insufficient space for 4K rendering.',
                    'RENDER_MEMORY_LOW' => 'The server has insufficient memory for 4K rendering. Upgrade RAM.',
                    default => str_contains(strtolower($e->getMessage()), 'timed out')
                        ? 'The 4K render exceeded its processing time limit.'
                        : 'The 4K encoding failed. Try a different H.264/HEVC video or a larger worker.',
                },
            ]));

            return self::FAILURE;
        } finally {
            @unlink($source);
            @unlink($partial);
            @unlink($iphonePartial);
            if (is_resource($lock)) {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        }
    }

    /**
     * @param array<string, mixed> $status
     */
    private function saveStatus(string $path, array $status): void
    {
        $status['heartbeat_at'] = time();
        $temporary = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        File::put($temporary, json_encode($status, JSON_THROW_ON_ERROR));
        if (! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException('Unable to write render status.');
        }
    }
}
