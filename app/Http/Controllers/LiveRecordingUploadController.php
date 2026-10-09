<?php

namespace App\Http\Controllers;

use App\Services\LiveArchiveS3;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

final class LiveRecordingUploadController extends Controller
{
    private const ACCOUNTS = ['knock1knock', 'emyii', 'lucycums'];
    private const CHUNK_BYTES = 2 * 1024 * 1024;
    private const MAX_BYTES = 250 * 1024 * 1024;
    private const ROOT = 'live-recording-uploads/manual';

    private function requireAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin() === true, 403);
    }

    private function owner(): string
    {
        return (string) (auth()->id() ?: auth()->user()?->email);
    }

    private function directory(string $upload): string
    {
        abort_unless((bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/D', $upload), 404);
        return self::ROOT.'/'.$upload;
    }

    private function metadata(string $upload): array
    {
        $disk = Storage::disk('local');
        $path = $this->directory($upload).'/meta.json';
        abort_unless($disk->exists($path), 404);
        $meta = json_decode((string) $disk->get($path), true);
        abort_unless(is_array($meta) && hash_equals((string) ($meta['owner'] ?? ''), $this->owner()), 404);
        abort_unless(in_array($meta['account'] ?? null, self::ACCOUNTS, true), 404);
        abort_unless(isset($meta['bytes'], $meta['parts'], $meta['filename']), 404);
        return $meta;
    }

    public function begin(Request $request, LiveArchiveS3 $archive): JsonResponse
    {
        $this->requireAdmin();
        // Never pretend a temporary Railway filesystem is a durable private archive.
        if (app()->environment('production') && !$archive->configured()) {
            abort(503, 'Privéopslag is niet ingesteld; upload niet gestart.');
        }
        $validated = $request->validate([
            'account' => ['required', 'in:'.implode(',', self::ACCOUNTS)],
            'bytes' => ['required', 'integer', 'min:1024', 'max:'.self::MAX_BYTES],
            'format' => ['sometimes', 'in:mp4,webm'],
        ]);

        $disk = Storage::disk('local');
        // Remove abandoned private upload chunks after 24 hours.
        foreach ($disk->directories(self::ROOT) as $directory) {
            $metaPath = $directory.'/meta.json';
            if ($disk->exists($metaPath) && $disk->lastModified($metaPath) < time() - 86400) {
                $disk->deleteDirectory($directory);
            }
        }

        $upload = (string) Str::uuid();
        $account = $validated['account'];
        $bytes = (int) $validated['bytes'];
        $parts = (int) ceil($bytes / self::CHUNK_BYTES);
        $filename = now('UTC')->format('Y-m-d\TH-i-s').'_'.$account.'_manual_'.$upload.'.mp4';
        $directory = $this->directory($upload);
        abort_unless($disk->makeDirectory($directory), 500);
        abort_unless($disk->put($directory.'/meta.json', json_encode([
            'owner' => $this->owner(), 'account' => $account,
            'filename' => $filename, 'bytes' => $bytes, 'parts' => $parts,
            'format' => $validated['format'] ?? 'mp4',
        ], JSON_THROW_ON_ERROR)), 500);

        return response()->json(['id' => $upload, 'parts' => $parts, 'chunk_bytes' => self::CHUNK_BYTES], 201);
    }

    public function part(Request $request, string $upload, string $index): JsonResponse
    {
        $this->requireAdmin();
        $meta = $this->metadata($upload);
        abort_unless(ctype_digit($index) && (int) $index < $meta['parts'], 404);
        $index = (int) $index;
        $expected = min(self::CHUNK_BYTES, $meta['bytes'] - $index * self::CHUNK_BYTES);
        if ((int) $request->server('CONTENT_LENGTH', 0) > self::CHUNK_BYTES) abort(413);
        $bytes = $request->getContent();
        abort_unless(strlen($bytes) === $expected, 422);
        abort_unless(Storage::disk('local')->put($this->directory($upload).'/'.$index.'.chunk', $bytes), 500);
        return response()->json(['ok' => true]);
    }

    public function complete(string $upload, LiveArchiveS3 $archive): JsonResponse
    {
        $this->requireAdmin();
        $meta = $this->metadata($upload);
        $disk = Storage::disk('local');
        $directory = $this->directory($upload);

        for ($i = 0; $i < $meta['parts']; $i++) {
            abort_unless($disk->exists($directory.'/'.$i.'.chunk'), 409);
        }

        $path = $disk->path($directory.'/assembled.partial');
        $output = fopen($path, 'wb');
        abort_unless($output !== false, 500);
        $written = 0;
        try {
            for ($i = 0; $i < $meta['parts']; $i++) {
                $source = $disk->readStream($directory.'/'.$i.'.chunk');
                abort_unless($source !== false, 409);
                try {
                    $n = stream_copy_to_stream($source, $output);
                    abort_unless($n !== false, 500);
                    $written += $n;
                } finally {
                    fclose($source);
                }
            }
        } finally {
            fclose($output);
        }
        abort_unless($written === $meta['bytes'], 422);

        $finalPath = $path;
        if (($meta['format'] ?? 'mp4') === 'webm') {
            $input = fopen($path, 'rb');
            abort_unless($input !== false, 500);
            try {
                $webmHeader = fread($input, 4);
            } finally {
                fclose($input);
            }
            abort_unless($webmHeader === "\x1A\x45\xDF\xA3", 422, 'Dit bestand is geen WebM-opname.');

            // Browser screen recording is most reliably available as WebM.
            // Convert to an actual MP4 before it enters the private S3 archive.
            $convertedPath = $disk->path($directory.'/converted.mp4');
            $result = Process::timeout(120)->run([
                'ffmpeg', '-hide_banner', '-loglevel', 'error', '-nostdin', '-y',
                '-i', $path, '-t', '30',
                '-map', '0:v:0', '-map', '0:a:0?',
                '-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '26',
                '-pix_fmt', 'yuv420p', '-threads', '2',
                '-c:a', 'aac', '-b:a', '128k',
                '-movflags', '+faststart', '-fs', (string) self::MAX_BYTES,
                $convertedPath,
            ]);
            abort_unless($result->successful() && is_file($convertedPath), 422,
                'De browseropname kon niet naar MP4 worden omgezet.');
            $finalPath = $convertedPath;
        }

        $size = filesize($finalPath);
        abort_unless($size !== false && $size >= 1024 && $size <= self::MAX_BYTES, 422,
            'De MP4-opname is leeg of te groot.');
        $input = fopen($finalPath, 'rb');
        abort_unless($input !== false, 500);
        try {
            fseek($input, 4);
            $mp4Header = fread($input, 4);
        } finally {
            fclose($input);
        }
        abort_unless($mp4Header === 'ftyp', 422, 'Dit bestand is geen geldig MP4-containerbestand.');

        if ($archive->configured()) {
            $archive->storeVideoFromPath($meta['account'], $meta['filename'], $finalPath);
        } else {
            $destination = 'live-recordings/'.$meta['account'];
            $disk->makeDirectory($destination);
            $incoming = $destination.'/'.$upload.'.incoming';
            $stream = fopen($finalPath, 'rb');
            abort_unless($stream !== false, 500);
            try {
                abort_unless($disk->writeStream($incoming, $stream), 500);
            } finally {
                fclose($stream);
            }
            abort_unless($disk->move($incoming, $destination.'/'.$meta['filename']), 500);
        }

        $disk->deleteDirectory($directory);
        return response()->json(['ok' => true, 'filename' => $meta['filename']]);
    }

    public function cancel(string $upload): JsonResponse
    {
        $this->requireAdmin();
        $this->metadata($upload);
        Storage::disk('local')->deleteDirectory($this->directory($upload));
        return response()->json(['ok' => true]);
    }
}
