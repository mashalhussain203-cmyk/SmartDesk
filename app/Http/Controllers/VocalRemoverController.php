<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Throwable;

final class VocalRemoverController extends Controller
{
    private const MAX_BYTES = 1024 * 1024 * 1024;

    public function start(Request $request, ?UploadedFile $assembledUpload = null): JsonResponse
    {
        if ($assembledUpload === null) {
            $validated = $request->validate([
                'audio' => ['required', 'file', 'mimes:mp3,wav,m4a,aac,flac,ogg,mp4,mov,webm', 'max:1048576'],
            ]);
            $upload = $validated['audio'];
        } else {
            $upload = $assembledUpload;
            abort_unless($upload->isValid() && $upload->getSize() > 0, 422, 'Het audiobestand is leeg of ongeldig.');
        }
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
                if (in_array($state['status'] ?? '', ['queued', 'processing'], true)) {
                    // The worker refreshes updated_at every few seconds. Use its heartbeat,
                    // not created_at: a crashed worker must not block every future upload
                    // for 30 minutes just because its old status.json still says processing.
                    $lastActivity = (int) ($state['updated_at'] ?? $state['created_at'] ?? 0);
                    if ($lastActivity > 0 && time() - $lastActivity < 120) {
                        return response()->json(['message' => __('De gratis audio-engine verwerkt momenteel een ander bestand. Probeer het zo opnieuw.')], 429);
                    }

                    $state['status'] = 'failed';
                    $state['stage'] = 'failed';
                    $state['error'] = __('De vorige audioverwerking is vastgelopen. Je kunt opnieuw proberen.');
                    $state['updated_at'] = time();
                    File::put($dir.'/status.json', json_encode($state, JSON_THROW_ON_ERROR));
                    Log::warning('Stale Vocal Remover job released', ['job' => basename($dir)]);
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

            // Railpack installs requirements-tiktok.txt into /app/.tiktok-python
            // and exposes its matching Python as python3 on PATH.
            $python = (string) (getenv('PYTHON') ?: 'python3');
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


    public function uploadChunk(Request $request, string $uploadId): JsonResponse
    {
        abort_unless((bool) preg_match('/^[a-f0-9]{40}$/', $uploadId), 404);
        $validated = $request->validate([
            'chunk' => ['required', 'file', 'max:16384'],
            'index' => ['required', 'integer', 'min:0', 'max:127'],
            'total' => ['required', 'integer', 'min:1', 'max:128'],
        ]);
        $key = 'vocal_remover_upload.'.$uploadId;
        $request->session()->put($key, ['expires_at' => time() + 7200]);
        $root = storage_path('app/private/vocal-remover/.uploads/'.$uploadId);
        File::ensureDirectoryExists($root, 0700, true);
        $chunk = $validated['chunk'];
        $chunk->move($root, sprintf('chunk-%03d', (int) $validated['index']));
        File::put($root.'/meta.json', json_encode([
            'total' => (int) $validated['total'],
            'updated_at' => time(),
        ], JSON_THROW_ON_ERROR));
        return response()->json(['received' => (int) $validated['index']])
            ->header('Cache-Control', 'private, no-store');
    }

    public function finishUpload(Request $request, string $uploadId): JsonResponse
    {
        abort_unless((bool) preg_match('/^[a-f0-9]{40}$/', $uploadId), 404);
        $expires = (int) $request->session()->get('vocal_remover_upload.'.$uploadId.'.expires_at', 0);
        abort_unless($expires > time(), 404);
        $validated = $request->validate([
            'total' => ['required', 'integer', 'min:1', 'max:128'],
            'extension' => ['required', 'in:mp3,wav,m4a,aac,flac,ogg,mp4,mov,webm'],
        ]);
        $root = storage_path('app/private/vocal-remover/.uploads/'.$uploadId);
        $total = (int) $validated['total'];
        $meta = is_file($root.'/meta.json') ? json_decode((string) File::get($root.'/meta.json'), true) : [];
        abort_unless(is_array($meta) && (int) ($meta['total'] ?? 0) === $total, 422, 'Uploadgegevens kloppen niet.');
        $assembled = storage_path('app/private/vocal-remover/.uploads/'.$uploadId.'.'.$validated['extension']);
        $output = fopen($assembled, 'wb');
        if (! $output) {
            throw new \RuntimeException('Uploadbestand kan niet worden samengesteld.');
        }
        $bytes = 0;
        try {
            for ($i = 0; $i < $total; $i++) {
                $part = $root.'/'.sprintf('chunk-%03d', $i);
                abort_unless(is_file($part), 422, 'Een uploaddeel ontbreekt. Probeer opnieuw.');
                $size = filesize($part);
                $bytes += $size;
                abort_if($bytes > self::MAX_BYTES, 413, 'Audio mag maximaal 1 GB zijn.');
                $input = fopen($part, 'rb');
                stream_copy_to_stream($input, $output);
                fclose($input);
            }
        } finally {
            fclose($output);
        }
        abort_if($bytes < 1, 422, 'Het audiobestand is leeg.');
        $file = new \Illuminate\Http\UploadedFile($assembled, 'audio.'.$validated['extension'], null, UPLOAD_ERR_OK, true);
        try {
            return $this->start($request, $file);
        } finally {
            File::deleteDirectory($root);
            @unlink($assembled);
            $request->session()->forget('vocal_remover_upload.'.$uploadId);
        }
    }

    public function status(Request $request, string $token): JsonResponse
    {
        abort_unless($this->owns($request, $token), 404);
        $dir = storage_path('app/private/vocal-remover/'.$token);
        $state = $this->readState($dir);
        $status = (string) ($state['status'] ?? 'queued');
        if (in_array($status, ['queued', 'processing'], true)) {
            $lastActivity = (int) ($state['updated_at'] ?? $state['created_at'] ?? 0);
            if ($lastActivity > 0 && time() - $lastActivity > 120) {
                $status = 'failed';
                $state['status'] = 'failed';
                $state['stage'] = 'failed';
                $state['error'] = __('De audioverwerking is gestopt of reageert niet meer. Start de verwerking opnieuw.');
                $state['updated_at'] = time();
                File::put($dir.'/status.json', json_encode($state, JSON_THROW_ON_ERROR));
            }
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
