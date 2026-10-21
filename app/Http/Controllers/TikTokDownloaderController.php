<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Throwable;

final class TikTokDownloaderController extends Controller
{
    private const SESSION_PREFIX = 'tiktok_downloader.';
    private const EXPIRES_AFTER = 1800;

    public function index(): View
    {
        return view('tools.tiktok-downloader');
    }

    public function analyze(Request $request): JsonResponse
    {
        $input = $request->validate(['url' => ['required', 'string', 'max:2000']]);
        $url = $this->validTikTokUrl($input['url']);

        if ($url === null) {
            return response()->json(['message' => 'Plak een geldige publieke TikTok-video- of fotolink.'], 422);
        }

        try {
            $process = new Process([$this->python(), $this->script(), 'info', $url]);
            $process->setTimeout(40);
            $process->run();

            $details = json_decode(trim($process->getOutput()), true);
            if (! $process->isSuccessful() || ! is_array($details) || ! ($details['success'] ?? false)) {
                Log::warning('TikTok downloader: analysis unavailable', [
                    'error' => mb_substr($process->getErrorOutput(), 0, 300),
                ]);
                return response()->json([
                    'message' => 'Deze TikTok-post kan momenteel niet worden opgehaald. Controleer of de video openbaar is.',
                ], 422);
            }

            $modes = array_values(array_intersect(
                ['video', 'video_hd', 'audio', 'photos'],
                (array) ($details['modes'] ?? [])
            ));
            if ($modes === []) {
                return response()->json(['message' => 'Er zijn voor deze post geen downloadbare media gevonden.'], 422);
            }

            $token = bin2hex(random_bytes(20));
            $request->session()->put(self::SESSION_PREFIX.$token, [
                'url' => $url,
                'modes' => $modes,
                'expires_at' => time() + self::EXPIRES_AFTER,
            ]);

            return response()->json([
                'token' => $token,
                'title' => mb_substr((string) ($details['title'] ?? 'TikTok video'), 0, 180),
                'creator' => mb_substr((string) ($details['creator'] ?? ''), 0, 100),
                'thumbnail' => $details['thumbnail'] ?? null,
                'duration' => $details['duration'] ?? null,
                'photo_count' => (int) ($details['photo_count'] ?? 0),
                'modes' => $modes,
            ])->header('Cache-Control', 'private, no-store');
        } catch (Throwable $exception) {
            Log::warning('TikTok downloader analysis failed', ['error' => $exception->getMessage()]);
            return response()->json(['message' => 'Downloadanalyse tijdelijk niet beschikbaar. Probeer het later opnieuw.'], 503);
        }
    }

    public function prepare(Request $request): JsonResponse
    {
        $input = $request->validate([
            'token' => ['required', 'string', 'regex:/^[a-f0-9]{40}$/'],
            'format' => ['required', 'in:video,video_hd,audio,photos'],
        ]);
        $key = self::SESSION_PREFIX.$input['token'];
        $record = $request->session()->get($key);

        if (! $this->allowedRecord($record) || ! in_array($input['format'], $record['modes'], true)) {
            return response()->json(['message' => 'Je link is verlopen. Analyseer de TikTok-link opnieuw.'], 422);
        }

        if (isset($record['job'])) {
            $previous = $this->readState($this->jobDirectory($record['job']));
            $previousStatus = (string) ($previous['status'] ?? 'queued');
            if (! in_array($previousStatus, ['ready', 'failed'], true)
                && time() - (int) ($record['started_at'] ?? time()) < 240) {
                return response()->json(['message' => 'Wacht tot je huidige download klaar is.'], 409);
            }
        }

        $job = bin2hex(random_bytes(20));
        $directory = storage_path('app/private/tiktok-downloader/'.$job);
        $script = $this->script();

        if (! is_file($script) || ! function_exists('exec')) {
            return response()->json(['message' => 'De downloadservice is momenteel niet beschikbaar.'], 503);
        }

        try {
            File::ensureDirectoryExists($directory, 0700, true);
            File::put($directory.'/status.json', json_encode([
                'status' => 'queued', 'updated_at' => time(),
            ], JSON_THROW_ON_ERROR));

            $command = implode(' ', array_map('escapeshellarg', [
                $this->python(),
                $script,
                'download',
                $record['url'],
                $input['format'],
                $directory,
            ]));
            $command .= ' > '.escapeshellarg($directory.'/worker.log').' 2>&1 < /dev/null &';
            exec($command);

            $record['job'] = $job;
            $record['format'] = $input['format'];
            $record['started_at'] = time();
            $request->session()->put($key, $record);

            if (random_int(1, 20) === 1) {
                $this->cleanupOldJobs();
            }

            return response()->json([
                'status_url' => route('tiktok-downloader.status', ['token' => $input['token']]),
            ], 202)->header('Cache-Control', 'private, no-store');
        } catch (Throwable $exception) {
            Log::warning('TikTok downloader start failed', ['error' => $exception->getMessage()]);
            return response()->json(['message' => 'De download kon niet worden gestart.'], 503);
        }
    }

    public function status(Request $request, string $token): JsonResponse
    {
        $record = $request->session()->get(self::SESSION_PREFIX.$token);
        if (! $this->allowedRecord($record) || ! isset($record['job'])) {
            return response()->json(['message' => 'Downloadsessie niet gevonden.'], 404);
        }

        $directory = $this->jobDirectory($record['job']);
        $state = $this->readState($directory);
        $status = (string) ($state['status'] ?? 'queued');

        if (in_array($status, ['queued', 'processing'], true) &&
            time() - (int) $record['started_at'] > 240) {
            $status = 'failed';
            $state['message'] = 'De download duurt te lang. Probeer het opnieuw.';
        }

        $payload = ['status' => $status];
        if ($status === 'failed') {
            $payload['message'] = (string) ($state['message'] ?? 'Download mislukt. Probeer het opnieuw.');
        } elseif ($status === 'ready' && $this->validOutputFile($directory, $state, $record['format'])) {
            $payload['download_url'] = route('tiktok-downloader.download', ['token' => $token]);
        } elseif ($status === 'ready') {
            $payload = ['status' => 'failed', 'message' => 'Het bestand is niet beschikbaar.'];
        }

        return response()->json($payload)->header('Cache-Control', 'private, no-store');
    }

    public function download(Request $request, string $token): BinaryFileResponse
    {
        $record = $request->session()->get(self::SESSION_PREFIX.$token);
        abort_unless($this->allowedRecord($record) && isset($record['job']), 404);

        $directory = $this->jobDirectory($record['job']);
        $state = $this->readState($directory);
        abort_unless(($state['status'] ?? null) === 'ready' &&
            $this->validOutputFile($directory, $state, $record['format']), 404);

        $extension = $record['format'] === 'audio' ? 'mp3' : ($record['format'] === 'photos' ? 'zip' : 'mp4');
        $filename = 'mashal-tiktok-'.substr($record['job'], 0, 12).'.'.$extension;

        return response()->download($directory.'/'.$state['file'], $filename, [
            'Content-Type' => $extension === 'mp3' ? 'audio/mpeg' :
                ($extension === 'zip' ? 'application/zip' : 'video/mp4'),
            'Cache-Control' => 'private, no-store, no-transform',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function validTikTokUrl(string $input): ?string
    {
        $input = trim($input);
        if (preg_match('~https?://[^\s<>]+~i', $input, $matches)) {
            $input = rtrim($matches[0], '.,)');
        }

        $parts = parse_url($input);
        if (! is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if (! in_array($host, [
            'tiktok.com', 'www.tiktok.com', 'm.tiktok.com', 'vm.tiktok.com', 'vt.tiktok.com',
        ], true)) {
            return null;
        }

        $path = (string) ($parts['path'] ?? '');
        $isShort = in_array($host, ['vm.tiktok.com', 'vt.tiktok.com'], true);
        if ($isShort ? ! preg_match('~^/[A-Za-z0-9]{4,40}/?$~', $path)
            : ! preg_match('~/(?:video|photo)/\d{10,25}(?:/|$)|^/t/[A-Za-z0-9]+/?$~', $path)) {
            return null;
        }

        return filter_var($input, FILTER_VALIDATE_URL) ? $input : null;
    }

    private function allowedRecord(mixed $record): bool
    {
        return is_array($record) && isset($record['url'], $record['modes'], $record['expires_at'])
            && (int) $record['expires_at'] > time() && is_array($record['modes']);
    }

    private function python(): string
    {
        $project = base_path('.venv/bin/python');
        $configured = (string) env('TIKTOK_PYTHON', '/opt/tiktok-venv/bin/python');

        return is_file($project) ? $project : (is_file($configured) ? $configured : 'python3');
    }

    private function script(): string
    {
        return base_path('scripts/tiktok_downloader.py');
    }

    private function jobDirectory(string $job): string
    {
        abort_unless((bool) preg_match('/^[a-f0-9]{40}$/', $job), 404);

        return storage_path('app/private/tiktok-downloader/'.$job);
    }

    private function readState(string $directory): array
    {
        $file = $directory.'/status.json';
        if (! is_file($file)) {
            return [];
        }

        $state = json_decode((string) File::get($file), true);

        return is_array($state) ? $state : [];
    }

    private function validOutputFile(string $directory, array $state, string $format): bool
    {
        $extension = $format === 'audio' ? 'mp3' : ($format === 'photos' ? 'zip' : 'mp4');
        $filename = (string) ($state['file'] ?? '');
        if (! preg_match('/^download\.(mp4|mp3|zip)$/', $filename)
            || $filename !== 'download.'.$extension) {
            return false;
        }

        $file = $directory.'/'.$filename;
        $size = is_file($file) ? filesize($file) : false;

        return $size !== false && $size > 0 && $size <= 210 * 1024 * 1024;
    }

    private function cleanupOldJobs(): void
    {
        $root = storage_path('app/private/tiktok-downloader');
        if (! is_dir($root)) {
            return;
        }
        foreach (File::directories($root) as $directory) {
            if (preg_match('/^[a-f0-9]{40}$/', basename($directory))
                && (filemtime($directory) ?: time()) < time() - 3600) {
                File::deleteDirectory($directory);
            }
        }
    }
}
