<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Throwable;

class VideoOptimizerController extends Controller
{
    private const MAX_BYTES = 104857600;

    public function index(): View
    {
        return view('tools.video-optimizer');
    }

    public function optimize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,mov', 'max:102400'],
            'mode' => ['required', Rule::in(['original', 'ffmpeg', '4k120'])],
            'convert_hevc' => ['nullable', 'boolean'],
        ]);

        $uploaded = $validated['video'];
        if ($uploaded->getSize() > self::MAX_BYTES) {
            return response()->json(['message' => __('The video must be no larger than 100 MB.')], 422);
        }

        $directory = storage_path('app/private/video-optimizer');
        File::ensureDirectoryExists($directory, 0750, true);
        $this->cleanExpiredFiles($directory);

        $token = bin2hex(random_bytes(20));
        $output = $directory.DIRECTORY_SEPARATOR.$token.'.mp4';
        $successful = false;

        try {
            $probe = new Process([
                'ffprobe', '-v', 'error', '-show_entries',
                'format=duration:stream=codec_type,codec_name,width,height,r_frame_rate',
                '-of', 'json', $uploaded->getRealPath(),
            ]);
            $probe->setTimeout(30);
            $probe->run();

            if (! $probe->isSuccessful()) {
                return response()->json([
                    'message' => __('This video cannot be read. Upload an MP4 or MOV with H.264 or H.265 video.'),
                ], 422);
            }

            $details = json_decode($probe->getOutput(), true);
            $streams = is_array($details['streams'] ?? null) ? $details['streams'] : [];
            $videoStream = collect($streams)->first(fn ($stream) => ($stream['codec_type'] ?? '') === 'video');

            if (! is_array($videoStream) || ! in_array($videoStream['codec_name'] ?? '', ['h264', 'hevc'], true)) {
                return response()->json([
                    'message' => __('Only H.264 and H.265 (HEVC) videos are supported.'),
                ], 422);
            }

            $duration = (float) ($details['format']['duration'] ?? 0);
            if ($duration <= 0 || $duration > 900) {
                return response()->json([
                    'message' => __('Your video must have a valid duration of at most 15 minutes.'),
                ], 422);
            }

            if ($validated['mode'] === '4k120') {
                return $this->queue4k120($request, $uploaded, $directory, $token, $output, $duration);
            }

            $convert = $request->boolean('convert_hevc') && ($videoStream['codec_name'] ?? '') === 'hevc';
            $transcode = $validated['mode'] === 'ffmpeg' || $convert;

            $command = [
                'ffmpeg', '-hide_banner', '-nostdin', '-loglevel', 'error', '-y',
                '-i', $uploaded->getRealPath(),
                '-map', '0:v:0', '-map', '0:a?',
            ];

            if ($transcode) {
                array_push(
                    $command,
                    '-c:v', 'libx264', '-preset', 'medium', '-crf', '18',
                    '-pix_fmt', 'yuv420p', '-fps_mode', 'passthrough',
                    '-c:a', 'aac', '-b:a', '192k',
                );
            } else {
                array_push($command, '-c:v', 'copy', '-c:a', 'copy');
            }

            array_push($command, '-movflags', '+faststart', '-f', 'mp4', $output);

            $process = new Process($command);
            $process->setTimeout(600);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($output) || filesize($output) === 0) {
                Log::warning('Video optimization failed', ['stderr' => mb_substr($process->getErrorOutput(), 0, 1000)]);
                return response()->json([
                    'message' => __('Processing failed. Try the other engine or another MP4/MOV file.'),
                ], 422);
            }

            // Never report the source dimensions as proof of a processed export.
            $check = new Process([
                'ffprobe', '-v', 'error', '-select_streams', 'v:0',
                '-show_entries', 'stream=width,height,avg_frame_rate:format=duration',
                '-of', 'json', $output,
            ]);
            $check->setTimeout(30);
            $check->run();

            if (! $check->isSuccessful()) {
                return response()->json([
                    'message' => __('The exported video could not be verified. Please try again.'),
                ], 422);
            }

            $verified = json_decode($check->getOutput(), true);
            $stream = $verified['streams'][0] ?? [];
            $rate = explode('/', (string) ($stream['avg_frame_rate'] ?? '0/1'));
            $verifiedFps = (float) ($rate[1] ?? 0) > 0
                ? (float) $rate[0] / (float) $rate[1]
                : 0.0;

            if ((int) ($stream['width'] ?? 0) < 1
                || (int) ($stream['height'] ?? 0) < 1
                || $verifiedFps <= 0) {
                return response()->json([
                    'message' => __('The exported video has invalid technical specifications.'),
                ], 422);
            }

            $successful = true;
            $request->session()->put('video_optimizer.'.$token, [
                'path' => $output,
                'expires_at' => time() + 3600,
                'duration' => $duration,
            ]);

            return response()->json([
                'message' => __('Your optimized video is ready.'),
                'download_url' => route('video-optimizer.download', ['token' => $token]),
                'token' => $token,
                'output_size' => filesize($output),
                'input_size' => $uploaded->getSize(),
                'width' => (int) $stream['width'],
                'height' => (int) $stream['height'],
                'fps' => round($verifiedFps, 2),
                'duration' => round((float) ($verified['format']['duration'] ?? $duration), 2),
                'mode' => $transcode ? 'ffmpeg' : 'original',
                'note' => $transcode
                    ? __('Re-encoded at high quality, retaining the source frame timing.')
                    : __('Video and audio streams were copied without re-encoding.'),
            ])->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => __('The video engine is temporarily unavailable. Please try again later.'),
            ], 503);
        } finally {
            if (! $successful && is_file($output)) {
                @unlink($output);
            }
        }
    }


    private function queue4k120(
        Request $request,
        \Illuminate\Http\UploadedFile $uploaded,
        string $directory,
        string $token,
        string $output,
        float $duration
    ): JsonResponse {
        if ($duration > 180) {
            return response()->json([
                'message' => __('4K 120 FPS supports videos up to 3 minutes (180 seconds) on this server.'),
            ], 422);
        }

        foreach ((array) $request->session()->get('video_optimizer', []) as $previous) {
            if (! is_array($previous) || (int) ($previous['expires_at'] ?? 0) <= time()) {
                continue;
            }

            $previousFile = $previous['status_file'] ?? null;
            if (is_string($previousFile) && is_file($previousFile)) {
                $state = json_decode((string) file_get_contents($previousFile), true);
                if (in_array($state['status'] ?? null, ['queued', 'processing'], true)
                    && (int) ($state['created_at'] ?? 0) > time() - 11400) {
                    return response()->json([
                        'message' => __('You already have a 4K 120 FPS render running.'),
                    ], 429);
                }
            }
        }

        // Avoid competing 4K jobs on this memory-constrained Railway instance.
        foreach (glob($directory.'/*.status.json') ?: [] as $jobFile) {
            $job = json_decode((string) @file_get_contents($jobFile), true);
            if (is_array($job)
                && in_array($job['status'] ?? null, ['queued', 'processing'], true)
                && (int) ($job['created_at'] ?? 0) > time() - 11400) {
                return response()->json([
                    'message' => __('The 4K renderer is busy. Wait for the current export to finish.'),
                ], 429);
            }
        }

        $extension = strtolower((string) $uploaded->getClientOriginalExtension());
        if (! in_array($extension, ['mp4', 'mov'], true)) {
            $extension = 'mp4';
        }

        $filename = $token.'.source.'.$extension;
        $inputPath = $directory.'/'.$filename;
        $statusFile = $directory.'/'.$token.'.status.json';

        try {
            $uploaded->move($directory, $filename);

            // The mounted private volume is only 500 MB. Leave room for output.
            $freeBytes = disk_free_space($directory);
            if ($freeBytes !== false && $freeBytes < ($duration > 90 ? 205 : 150) * 1048576) {
                throw new \RuntimeException('RENDER_STORAGE_LOW');
            }
            File::put($statusFile, json_encode([
                'status' => 'queued',
                'progress' => 0,
                'created_at' => time(),
                'duration' => $duration,
                'source' => $filename,
            ], JSON_THROW_ON_ERROR));

            $request->session()->put('video_optimizer.'.$token, [
                'path' => $output,
                'status_file' => $statusFile,
                'expires_at' => time() + 21600,
                'duration' => $duration,
            ]);

            $shell = 'nohup php '.escapeshellarg(base_path('artisan'))
                .' video-optimizer:render '.escapeshellarg($token)
                .' > '.escapeshellarg($directory.'/'.$token.'.log')
                .' 2>&1 < /dev/null &';

            $launcher = Process::fromShellCommandline($shell, base_path());
            $launcher->setTimeout(15);
            $launcher->run();

            if (! $launcher->isSuccessful()) {
                throw new \RuntimeException('Video worker launch failed.');
            }

            return response()->json([
                'message' => __('Rendering 4K 120 FPS in the background. Keep this page open.'),
                'mode' => '4k120',
                'processing' => true,
                'token' => $token,
                'status_url' => route('video-optimizer.status', ['token' => $token]),
            ], 202)->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);
            $request->session()->forget('video_optimizer.'.$token);
            @unlink($inputPath);
            @unlink($statusFile);

            return response()->json([
                'message' => $exception->getMessage() === 'RENDER_STORAGE_LOW'
                    ? __('Not enough free storage for a 4K render. Clear old exports or expand the Railway volume.')
                    : __('Unable to start the 4K render on this server.'),
            ], 503);
        }
    }

    public function active(Request $request): JsonResponse
    {
        $entries = (array) $request->session()->get('video_optimizer', []);
        $candidate = null;
        $newest = 0;

        foreach ($entries as $token => $entry) {
            if (! is_string($token) || ! preg_match('/^[a-f0-9]{40}$/', $token)
                || ! is_array($entry) || (int) ($entry['expires_at'] ?? 0) < time()) {
                continue;
            }

            $expected = storage_path('app/private/video-optimizer/'.$token.'.status.json');
            if (($entry['status_file'] ?? null) !== $expected || ! is_file($expected)) {
                continue;
            }

            $state = json_decode((string) file_get_contents($expected), true);
            $created = (int) ($state['created_at'] ?? 0);
            if (! is_array($state) || $created < $newest
                || ! in_array($state['status'] ?? '', ['queued', 'processing', 'ready'], true)) {
                continue;
            }

            $candidate = $token;
            $newest = $created;
        }

        return response()->json($candidate === null
            ? ['active' => false]
            : [
                'active' => true,
                'status_url' => route('video-optimizer.status', ['token' => $candidate]),
            ])->header('Cache-Control', 'private, no-store');
    }

    public function status(Request $request, string $token): JsonResponse
    {
        $entry = $request->session()->get('video_optimizer.'.$token);
        $directory = storage_path('app/private/video-optimizer');
        $statusFile = $directory.'/'.$token.'.status.json';

        if (! is_array($entry)
            || ($entry['expires_at'] ?? 0) < time()
            || ($entry['status_file'] ?? '') !== $statusFile
            || ! is_file($statusFile)) {
            abort(404);
        }

        $state = json_decode((string) file_get_contents($statusFile), true);
        if (! is_array($state)) {
            return response()->json(['message' => __('Video status temporarily unavailable.')], 503);
        }

        $status = (string) ($state['status'] ?? 'failed');
        if (in_array($status, ['queued', 'processing'], true)
            && (int) ($state['created_at'] ?? 0) < time() - 11400) {
            $status = 'failed';
            $state['message'] = __('4K processing timed out. Try a shorter clip.');
        }

        $result = [
            'status' => $status,
            'progress' => max(0, min(100, (int) ($state['progress'] ?? 0))),
            'message' => (string) ($state['message'] ?? ''),
        ];

        if ($status === 'ready' && is_file($directory.'/'.$token.'.mp4')) {
            $result['download_url'] = route('video-optimizer.download', ['token' => $token]);
            $result['token'] = $token;
            $result['note'] = __('Verified 4K at 120 FPS, with synthesized intermediate frames and original playback duration.');
            $result['width'] = (int) ($state['width'] ?? 0);
            $result['height'] = (int) ($state['height'] ?? 0);
            $result['fps'] = (float) ($state['fps'] ?? 0);
            $result['duration'] = (float) ($state['duration'] ?? 0);
        }

        return response()->json($result)->header('Cache-Control', 'private, no-store');
    }

    public function download(Request $request, string $token): BinaryFileResponse
    {
        $entry = $request->session()->get('video_optimizer.'.$token);
        if (! is_array($entry) || ($entry['expires_at'] ?? 0) < time()) {
            abort(404);
        }

        $expected = storage_path('app/private/video-optimizer/'.$token.'.mp4');
        if (($entry['path'] ?? '') !== $expected || ! is_file($expected)) {
            abort(404);
        }

        if (isset($entry['status_file'])) {
            $state = is_file($entry['status_file'])
                ? json_decode((string) file_get_contents($entry['status_file']), true)
                : null;

            if (($state['status'] ?? '') !== 'ready') {
                abort(404);
            }
        }

        $filename = isset($entry['status_file'])
            ? 'mashal-4k-120fps.mp4'
            : 'mashal-optimized-original.mp4';

        return response()->download($expected, $filename, [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function cleanExpiredFiles(string $directory): void
    {
        foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            if (! is_file($file)) {
                continue;
            }

            $name = basename($file);
            if (! preg_match('/^([a-f0-9]{40})(?:\.source)?\.(?:mp4|mov)$/', $name)
                && ! preg_match('/^[a-f0-9]{40}\.(?:status\.json|log|partial\.mp4)$/', $name)) {
                continue;
            }

            $token = substr($name, 0, 40);
            $hasRenderStatus = is_file($directory.DIRECTORY_SEPARATOR.$token.'.status.json');
            $maxAge = $hasRenderStatus ? 21600 : 3600;

            if (filemtime($file) < time() - $maxAge) {
                @unlink($file);
            }
        }
    }
}
