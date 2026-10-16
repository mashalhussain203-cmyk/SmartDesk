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
            'mode' => ['required', Rule::in(['original', 'ffmpeg'])],
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

            $successful = true;
            $request->session()->put('video_optimizer.'.$token, [
                'path' => $output,
                'expires_at' => time() + 3600,
            ]);

            return response()->json([
                'message' => __('Your optimized video is ready.'),
                'download_url' => route('video-optimizer.download', ['token' => $token]),
                'output_size' => filesize($output),
                'input_size' => $uploaded->getSize(),
                'width' => (int) ($videoStream['width'] ?? 0),
                'height' => (int) ($videoStream['height'] ?? 0),
                'duration' => round($duration, 2),
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

        $request->session()->forget('video_optimizer.'.$token);

        return response()->download($expected, 'mashal-optimized-video.mp4', [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend(true);
    }

    private function cleanExpiredFiles(string $directory): void
    {
        foreach (glob($directory.DIRECTORY_SEPARATOR.'*.mp4') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < time() - 3600) {
                @unlink($file);
            }
        }
    }
}
