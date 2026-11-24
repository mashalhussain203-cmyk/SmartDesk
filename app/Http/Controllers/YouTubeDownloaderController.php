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

final class YouTubeDownloaderController extends Controller
{
    private const SESSION_PREFIX = 'youtube_downloader.';
    private const EXPIRES_AFTER = 1800;

    public function index(): View
    {
        return view('tools.youtube-downloader');
    }

    public function analyze(Request $request): JsonResponse
    {
        $input = $request->validate(['url' => ['required', 'string', 'max:2048']]);
        $url = $this->validYouTubeUrl($input['url']);
        if ($url === null) {
            return response()->json(['message' => 'Plak een geldige openbare YouTube-video-, Shorts- of youtu.be-link.'], 422);
        }

        try {
            $process = new Process([$this->python(), $this->script(), 'info', $url]);
            $process->setTimeout(90);
            $process->run();
            $details = json_decode(trim($process->getOutput()), true);

            if (! $process->isSuccessful() || ! is_array($details) || ! ($details['success'] ?? false)) {
                Log::warning('YouTube downloader analysis unavailable', [
                    'error' => mb_substr($process->getErrorOutput(), 0, 500),
                    'exit' => $process->getExitCode(),
                ]);
                return response()->json([
                    'message' => is_array($details) ? (string) ($details['message'] ?? 'YouTube kon geen downloadgegevens teruggeven.') : 'YouTube kon deze video niet analyseren. Controleer of de video openbaar en beschikbaar is.',
                ], 422);
            }

            $qualities = array_values(array_unique(array_filter(
                array_map('intval', (array) ($details['qualities'] ?? [])),
                fn ($height) => $height >= 1 && $height <= 4320
            )));
            rsort($qualities);
            $hasAudio = (bool) ($details['has_audio'] ?? false);
            if ($qualities === [] && ! $hasAudio) {
                return response()->json(['message' => 'Geen downloadbare video- of audiostream gevonden.'], 422);
            }

            $token = bin2hex(random_bytes(20));
            $request->session()->put(self::SESSION_PREFIX.$token, [
                'url' => $url,
                'qualities' => $qualities,
                'has_audio' => $hasAudio,
                'expires_at' => time() + self::EXPIRES_AFTER,
            ]);

            return response()->json([
                'token' => $token,
                'title' => mb_substr((string) ($details['title'] ?? 'YouTube-video'), 0, 180),
                'author' => mb_substr((string) ($details['author'] ?? 'YouTube'), 0, 120),
                'thumbnail' => $this->safeThumbnail($details['thumbnail'] ?? null),
                'duration' => (int) ($details['duration'] ?? 0),
                'qualities' => $qualities,
                'has_audio' => $hasAudio,
                'watch_url' => $url,
            ])->header('Cache-Control', 'private, no-store');
        } catch (Throwable $exception) {
            Log::warning('YouTube downloader analysis failed', ['error' => $exception->getMessage()]);
            return response()->json(['message' => 'YouTube-analyse is tijdelijk niet beschikbaar. Probeer het later opnieuw.'], 503);
        }
    }

    public function prepare(Request $request): JsonResponse
    {
        $input = $request->validate([
            'token' => ['required', 'string', 'regex:/^[a-f0-9]{40}$/'],
            'format' => ['required', 'in:mp4,mp3'],
            'quality' => ['nullable', 'integer', 'min:1', 'max:4320'],
        ]);
        $key = self::SESSION_PREFIX.$input['token'];
        $record = $request->session()->get($key);

        if (! $this->allowedRecord($record)) {
            return response()->json(['message' => 'Deze analysesessie is verlopen. Plak de YouTube-link opnieuw.'], 422);
        }
        if ($input['format'] === 'mp4' && ! in_array((int) ($input['quality'] ?? 0), $record['qualities'], true)) {
            return response()->json(['message' => 'Deze resolutie is niet beschikbaar voor de gekozen video.'], 422);
        }
        if ($input['format'] === 'mp3' && ! $record['has_audio']) {
            return response()->json(['message' => 'Er is geen audiostream beschikbaar voor deze video.'], 422);
        }

        if (isset($record['job'])) {
            $previous = $this->readState($this->jobDirectory($record['job']));
            if (! in_array((string) ($previous['status'] ?? 'queued'), ['ready', 'failed'], true)
                && time() - (int) ($record['started_at'] ?? time()) < 600) {
                return response()->json(['message' => 'Je vorige download wordt nog verwerkt.'], 409);
            }
        }

        $job = bin2hex(random_bytes(20));
        $directory = storage_path('app/private/youtube-downloader/'.$job);
        if (! function_exists('exec') || ! is_file($this->script())) {
            return response()->json(['message' => 'De YouTube-downloadservice is niet beschikbaar op de server.'], 503);
        }

        try {
            File::ensureDirectoryExists($directory, 0700, true);
            File::put($directory.'/status.json', json_encode(['status' => 'queued', 'updated_at' => time()], JSON_THROW_ON_ERROR));
            $command = implode(' ', array_map('escapeshellarg', [
                $this->python(), $this->script(), 'download', $record['url'],
                $input['format'], (string) ($input['quality'] ?? 0), $directory,
            ])).' > '.escapeshellarg($directory.'/worker.log').' 2>&1 < /dev/null & echo $!';
            exec('nohup '.$command, $workerOutput, $workerExitCode);
            $workerPid = isset($workerOutput[0]) ? (int) trim($workerOutput[0]) : 0;
            if ($workerExitCode !== 0 || $workerPid < 1) {
                throw new \\RuntimeException('YouTube worker could not be started.');
            }

            $record['job'] = $job;
            $record['format'] = $input['format'];
            $record['quality'] = (int) ($input['quality'] ?? 0);
            $record['started_at'] = time();
            $request->session()->put($key, $record);

            if (random_int(1, 15) === 1) {
                $this->cleanupOldJobs();
            }

            return response()->json([
                'status_url' => route('youtube-downloader.status', ['token' => $input['token']]),
            ], 202)->header('Cache-Control', 'private, no-store');
        } catch (Throwable $exception) {
            Log::warning('YouTube downloader start failed', ['error' => $exception->getMessage()]);
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
        if (in_array($status, ['queued', 'processing'], true) && time() - (int) $record['started_at'] > 600) {
            $status = 'failed';
            $state['message'] = 'De download duurde te lang. Probeer een lagere kwaliteit.';
        }

        $payload = ['status' => $status];
        if ($status === 'failed') {
            $payload['message'] = (string) ($state['message'] ?? 'Download mislukt. Probeer het opnieuw.');
            Log::warning('YouTube downloader worker failed', ['detail' => mb_substr((string) ($state['detail'] ?? ''), 0, 350)]);
        } elseif ($status === 'ready' && $this->validOutputFile($directory, $state, $record['format'])) {
            $payload['download_url'] = route('youtube-downloader.download', ['token' => $token]);
        } elseif ($status === 'ready') {
            $payload = ['status' => 'failed', 'message' => 'Het gemaakte bestand is niet beschikbaar.'];
        }

        return response()->json($payload)->header('Cache-Control', 'private, no-store');
    }

    public function download(Request $request, string $token): BinaryFileResponse
    {
        $record = $request->session()->get(self::SESSION_PREFIX.$token);
        abort_unless($this->allowedRecord($record) && isset($record['job']), 404);
        $directory = $this->jobDirectory($record['job']);
        $state = $this->readState($directory);
        abort_unless(($state['status'] ?? null) === 'ready' && $this->validOutputFile($directory, $state, $record['format']), 404);

        $extension = $record['format'];
        $filename = 'mashal-studio-youtube-'.$record['job'].($extension === 'mp3' ? '.mp3' : '-'.$record['quality'].'p.mp4');
        return response()->download($directory.'/'.$state['file'], $filename, [
            'Content-Type' => $extension === 'mp3' ? 'audio/mpeg' : 'video/mp4',
            'Cache-Control' => 'private, no-store, no-transform',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function validYouTubeUrl(string $input): ?string
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
        if (! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtu.be', 'www.youtube-nocookie.com'], true)) {
            return null;
        }
        $path = (string) ($parts['path'] ?? '');
        $id = null;
        if ($host === 'youtu.be') {
            $id = trim($path, '/');
        } else {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $id = $query['v'] ?? null;
            if (! $id && preg_match('~^/(?:shorts|embed|live)/([A-Za-z0-9_-]{11})(?:/|$)~', $path, $matches)) {
                $id = $matches[1];
            }
        }
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id)
            ? 'https://www.youtube.com/watch?v='.$id
            : null;
    }

    private function safeThumbnail(mixed $url): ?string
    {
        if (! is_string($url)) return null;
        $parts = parse_url($url);
        return is_array($parts) && strtolower((string) ($parts['scheme'] ?? '')) === 'https'
            && in_array(strtolower((string) ($parts['host'] ?? '')), ['i.ytimg.com', 'img.youtube.com'], true)
            ? $url : null;
    }

    private function allowedRecord(mixed $record): bool
    {
        return is_array($record) && isset($record['url'], $record['qualities'], $record['expires_at'])
            && (int) $record['expires_at'] > time() && is_array($record['qualities']);
    }

    private function python(): string
    {
        $project = base_path('.venv/bin/python');
        $configured = (string) env('TIKTOK_PYTHON', '/opt/tiktok-venv/bin/python');
        return is_file($project) ? $project : (is_file($configured) ? $configured : 'python3');
    }

    private function script(): string
    {
        return base_path('scripts/youtube_downloader.py');
    }

    private function jobDirectory(string $job): string
    {
        abort_unless((bool) preg_match('/^[a-f0-9]{40}$/', $job), 404);
        return storage_path('app/private/youtube-downloader/'.$job);
    }

    private function readState(string $directory): array
    {
        $file = $directory.'/status.json';
        if (! is_file($file)) return [];
        $state = json_decode((string) File::get($file), true);
        return is_array($state) ? $state : [];
    }

    private function validOutputFile(string $directory, array $state, string $format): bool
    {
        $expected = $format === 'mp3' ? 'download.mp3' : 'download.mp4';
        if (($state['file'] ?? null) !== $expected) return false;
        $file = $directory.'/'.$expected;
        $size = is_file($file) ? filesize($file) : false;
        return $size !== false && $size > 0 && $size <= 520 * 1024 * 1024;
    }

    private function cleanupOldJobs(): void
    {
        $root = storage_path('app/private/youtube-downloader');
        if (! is_dir($root)) return;
        foreach (File::directories($root) as $directory) {
            if (preg_match('/^[a-f0-9]{40}$/', basename($directory))
                && (filemtime($directory) ?: time()) < time() - 3600) {
                File::deleteDirectory($directory);
            }
        }
    }
}
