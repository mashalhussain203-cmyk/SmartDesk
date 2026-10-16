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
            ]));

            $probe = new Process([
                'ffprobe', '-v', 'error', '-select_streams', 'v:0',
                '-show_entries', 'stream=width,height', '-of', 'json', $source,
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

            // Interpolate at source size before the resource-intensive upscale.
            $filter = 'minterpolate=fps=120:mi_mode=blend,'
                .'scale='.$width.':'.$height.':force_original_aspect_ratio=decrease:flags=bicubic,'
                .'pad='.$width.':'.$height.':(ow-iw)/2:(oh-ih)/2,setsar=1';

            // Constrain x264 encoder threads (not only input decoder threads)
            // to stay within the 1 GB Railway container memory limit.
            $ffmpeg = new Process([
                'ffmpeg', '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
                '-threads', '1', '-filter_threads', '1',
                '-i', $source, '-map', '0:v:0', '-map', '0:a?', '-sn', '-dn',
                '-vf', $filter, '-fps_mode', 'cfr', '-r', '120',
                '-c:v', 'libx264', '-threads:v', '2',
                '-preset', 'ultrafast', '-tune', 'zerolatency', '-crf', '20',
                '-maxrate', $duration > 90 ? '8M' : '12M',
                '-bufsize', $duration > 90 ? '16M' : '24M',
                '-pix_fmt', 'yuv420p',
                '-c:a', 'aac', '-b:a', '160k', '-movflags', '+faststart',
                '-progress', 'pipe:1', '-nostats', '-f', 'mp4', $partial,
            ]);
            $ffmpeg->setTimeout(10800);
            $previousProgress = 1;
            $ffmpeg->run(function (string $type, string $buffer) use ($statusFile, $state, $duration, &$previousProgress): void {
                if ($type !== Process::OUT || $duration <= 0) {
                    return;
                }

                if (preg_match_all('/out_time_us=(\d+)/', $buffer, $matches)) {
                    $microseconds = (int) end($matches[1]);
                    $progress = min(98, max(1, (int) (($microseconds / 1000000) / $duration * 98)));
                    if ($progress >= $previousProgress + 3) {
                        $previousProgress = $progress;
                        $this->saveStatus($statusFile, array_merge($state, [
                            'status' => 'processing', 'progress' => $progress,
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

            $this->saveStatus($statusFile, array_merge($state, [
                'status' => 'ready', 'progress' => 100,
                'width' => $width, 'height' => $height, 'fps' => round($fps, 2),
                'duration' => $finishedDuration,
            ]));

            return self::SUCCESS;
        } catch (Throwable $e) {
            Log::warning('4K 120 FPS processing failed', [
                'render_token' => $token, 'message' => mb_substr($e->getMessage(), 0, 300),
            ]);
            $this->saveStatus($statusFile, array_merge($state, [
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
        $temporary = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        File::put($temporary, json_encode($status, JSON_THROW_ON_ERROR));
        if (! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException('Unable to write render status.');
        }
    }
}
