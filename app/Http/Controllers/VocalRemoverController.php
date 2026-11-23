<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Throwable;

final class VocalRemoverController extends Controller
{
    private const MAX_BYTES = 1024 * 1024 * 1024;

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'audio' => ['required', 'file', 'mimes:mp3,wav,m4a,aac,flac,ogg,mp4,mov,webm', 'max:1048576'],
        ]);
        $upload = $validated['audio'];
        if ($upload->getSize() > self::MAX_BYTES) {
            return response()->json(['message' => __('Audio mag maximaal 1 GB zijn.')], 422);
        }

        $root = storage_path('app/private/vocal-remover');
        File::ensureDirectoryExists($root, 0700, true);
        $this->cleanup($root);
        $lock = @fopen($root.'/worker.lock', 'c');
        if (! is_resource($lock) || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) fclose($lock);
            return response()->json(['message' => __('De gratis audio-engine verwerkt momenteel een ander bestand. Probeer het zo opnieuw.')], 429);
        }

        try {
            foreach (File::directories($root) as $dir) {
                $state = $this->readState($dir);
                if (in_array($state['status'] ?? '', ['queued', 'processing'], true)
                    && time() - (int) ($state['created_at'] ?? time()) < 1800) {
                    return response()->json(['message' => __('Er wordt al audio verwerkt. Wacht tot die taak klaar is.')], 429);
                }
            }

            $token = bin2hex(random_bytes(20));
            $dir = $root.'/'.$token;
            File::ensureDirectoryExists($dir, 0700, true);
            $extension = strtolower($upload->getClientOriginalExtension() ?: 'audio');
            $uploadedSource = $upload->move($dir, 'upload.'.$extension);
            File::put($dir.'/status.json', json_encode([
                'status' => 'queued', 'created_at' => time(),
            ], JSON_THROW_ON_ERROR));

            $request->session()->put('vocal_remover.'.$token, [
                'expires_at' => time() + 3600,
            ]);

            // Packages are installed into /app/.tiktok-python by Railpack.
            // The worker adds that exact directory to sys.path; do not use the
            // unrelated optional /opt/tiktok-venv interpreter here.
            $python = 'python3';
            $command = 'nohup '.escapeshellarg($python).' '.escapeshellarg(base_path('scripts/vocal_remover.py'))
                .' '.escapeshellarg($token).' '.escapeshellarg($uploadedSource->getPathname())
                .' '.escapeshellarg($dir).' > '.escapeshellarg($dir.'/worker.log').' 2>&1 < /dev/null &';
            $launcher = Process::fromShellCommandline($command, base_path());
            $launcher->setTimeout(10);
            $launcher->run();
            if (! $launcher->isSuccessful()) {
                throw new \RuntimeException('Unable to launch audio worker');
            }

            return response()->json(['token' => $token], 202)->header('Cache-Control', 'private, no-store');
        } catch (Throwable $e) {
            Log::warning('Vocal Remover job could not start', ['error' => $e->getMessage()]);
            return response()->json(['message' => __('De audioverwerking kon niet worden gestart. Probeer het later opnieuw.')], 503);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    public function status(Request $request, string $token): JsonResponse
    {
        abort_unless($this->owns($request, $token), 404);
        $dir = storage_path('app/private/vocal-remover/'.$token);
        $state = $this->readState($dir);
        $status = (string) ($state['status'] ?? 'queued');
        if (in_array($status, ['queued', 'processing'], true)
            && time() - (int) ($state['created_at'] ?? time()) > 1800) {
            $status = 'failed';
        }

        if ($status === 'ready' && $this->validOutputs($dir)) {
            return response()->json([
                'status' => 'ready',
                'vocals_url' => route('vocal-remover.download', ['token' => $token, 'stem' => 'vocals']),
                'instrumental_url' => route('vocal-remover.download', ['token' => $token, 'stem' => 'no_vocals']),
            ])->header('Cache-Control', 'private, no-store');
        }

        return response()->json([
            'status' => $status,
            'stage' => (string) ($state['stage'] ?? 'queued'),
            'progress' => (int) ($state['progress'] ?? 0),
            'message' => $status === 'failed' ? (string) ($state['error'] ?? __('Verwerking mislukt. Controleer het bestand en probeer opnieuw.')) : null,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function download(Request $request, string $token, string $stem): BinaryFileResponse
    {
        abort_unless($this->owns($request, $token) && in_array($stem, ['vocals', 'no_vocals'], true), 404);
        $dir = storage_path('app/private/vocal-remover/'.$token);
        $state = $this->readState($dir);
        abort_unless(($state['status'] ?? '') === 'ready' && $this->validOutputs($dir), 404);
        $file = $dir.'/'.$stem.'.mp3';
        return response()->download($file, $stem === 'vocals' ? 'mashal-studio-vocals.mp3' : 'mashal-studio-instrumental.mp3', [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function owns(Request $request, string $token): bool
    {
        return (bool) preg_match('/^[a-f0-9]{40}$/', $token)
            && (int) $request->session()->get('vocal_remover.'.$token.'.expires_at', 0) > time();
    }

    private function readState(string $dir): array
    {
        $file = $dir.'/status.json';
        $state = is_file($file) ? json_decode((string) File::get($file), true) : [];
        return is_array($state) ? $state : [];
    }

    private function validOutputs(string $dir): bool
    {
        foreach (['vocals.mp3', 'no_vocals.mp3'] as $name) {
            if (! is_file($dir.'/'.$name) || filesize($dir.'/'.$name) < 1000) return false;
        }
        return true;
    }

    private function cleanup(string $root): void
    {
        foreach (File::directories($root) as $dir) {
            if (preg_match('/^[a-f0-9]{40}$/', basename($dir))
                && (filemtime($dir) ?: time()) < time() - 3600) {
                File::deleteDirectory($dir);
            }
        }
    }
}
