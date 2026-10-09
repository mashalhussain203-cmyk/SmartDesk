<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LiveRecorderIngestController extends Controller
{
    private const ACCOUNTS = ['knock1knock', 'emyii'];
    private const CHUNK_BYTES = 2 * 1024 * 1024;
    private const MAX_PARTS = 15000;

    private function authorizeWorker(Request $request, string $account): void
    {
        abort_unless(in_array($account, self::ACCOUNTS, true), 404);
        $configured = (string) config('live_recorder.secret');
        $given = (string) ($request->bearerToken() ?? '');
        abort_unless(strlen($configured) >= 32 && hash_equals($configured, $given), 403);
    }

    public function status(Request $request, string $account): JsonResponse
    {
        $this->authorizeWorker($request, $account);
        $validated = $request->validate([
            'status' => ['required', 'in:offline,live,recording,uploading,error,needs_setup,unknown'],
            'message' => ['nullable', 'string', 'max:180'],
        ]);

        Storage::disk('local')->put(
            "live-recordings/{$account}/status.json",
            json_encode([
                'status' => $validated['status'],
                'message' => $validated['message'] ?? '',
                'checked_at' => now()->toIso8601String(),
            ], JSON_THROW_ON_ERROR)
        );

        return response()->json(['ok' => true]);
    }

    public function begin(Request $request, string $account): JsonResponse
    {
        $this->authorizeWorker($request, $account);
        $validated = $request->validate([
            'filename' => ['required', 'regex:/^[A-Za-z0-9_.-]+\.mp4$/D', 'max:180'],
        ]);

        $uploadId = (string) Str::uuid();
        $directory = "live-recording-uploads/{$account}/{$uploadId}";
        Storage::disk('local')->put(
            "{$directory}/meta.json",
            json_encode(['filename' => $validated['filename']], JSON_THROW_ON_ERROR)
        );

        return response()->json(['id' => $uploadId], 201);
    }

    public function part(Request $request, string $account, string $upload, string $index): JsonResponse
    {
        $this->authorizeWorker($request, $account);
        abort_unless(ctype_digit($index) && (int) $index < self::MAX_PARTS, 404);
        $directory = "live-recording-uploads/{$account}/{$upload}";
        abort_unless(Storage::disk('local')->exists("{$directory}/meta.json"), 404);

        $bytes = $request->getContent();
        abort_unless(strlen($bytes) > 0 && strlen($bytes) <= self::CHUNK_BYTES, 413);
        abort_unless(Storage::disk('local')->put("{$directory}/{$index}.chunk", $bytes), 500);

        return response()->json(['ok' => true]);
    }

    public function complete(Request $request, string $account, string $upload): JsonResponse
    {
        $this->authorizeWorker($request, $account);
        $validated = $request->validate([
            'parts' => ['required', 'integer', 'min:1', 'max:'.self::MAX_PARTS],
            'bytes' => ['required', 'integer', 'min:1', 'max:32212254720'],
        ]);

        $disk = Storage::disk('local');
        $directory = "live-recording-uploads/{$account}/{$upload}";
        abort_unless($disk->exists("{$directory}/meta.json"), 404);
        $meta = json_decode((string) $disk->get("{$directory}/meta.json"), true);
        abort_unless(is_array($meta) && preg_match('/^[A-Za-z0-9_.-]+\.mp4$/D', (string) ($meta['filename'] ?? '')), 422);

        $target = "live-recordings/{$account}/{$meta['filename']}";
        if ($disk->exists($target)) {
            // A retry after completion must not overwrite an existing private recording.
            return response()->json(['ok' => true, 'already_exists' => true]);
        }

        // Reject incomplete uploads before writing the destination.
        for ($i = 0; $i < $validated['parts']; $i++) {
            abort_unless($disk->exists("{$directory}/{$i}.chunk"), 409);
        }

        $temporary = "{$directory}/finished.partial";
        $output = fopen($disk->path($temporary), 'wb');
        abort_unless($output !== false, 500);
        $bytesWritten = 0;
        try {
            for ($i = 0; $i < $validated['parts']; $i++) {
                $source = $disk->readStream("{$directory}/{$i}.chunk");
                if ($source === false) {
                    abort(409);
                }
                try {
                    $written = stream_copy_to_stream($source, $output);
                    abort_unless($written !== false, 500);
                    $bytesWritten += $written;
                } finally {
                    fclose($source);
                }
            }
        } finally {
            fclose($output);
        }

        abort_unless($bytesWritten === (int) $validated['bytes'], 422);
        $disk->makeDirectory("live-recordings/{$account}");
        abort_unless($disk->move($temporary, $target), 500);
        $disk->deleteDirectory($directory);

        return response()->json(['ok' => true, 'filename' => $meta['filename']]);
    }
}
