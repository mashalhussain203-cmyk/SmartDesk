<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class LiveChatChunkUploadService
{
    public const MAX_VIDEO_BYTES = 1073741824; // 1 GiB
    public const CHUNK_BYTES = 5 * 1024 * 1024; // 5 MiB
    private const ALLOWED_MIMES = [
        'video/mp4',
        'video/webm',
        'video/quicktime',
        'video/x-m4v',
    ];
    private const ALLOWED_EXTENSIONS = ['mp4', 'webm', 'mov', 'm4v'];

    public function start(string $ownerKey, array $data): array
    {
        $this->cleanupExpired();

        $size = (int) $data['size'];
        if ($size < 1 || $size > self::MAX_VIDEO_BYTES) {
            throw new RuntimeException('De video mag maximaal 1 GB groot zijn.');
        }

        $name = basename((string) $data['name']);
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('Dit videoformaat wordt niet ondersteund.');
        }

        $mime = strtolower(trim((string) ($data['mime'] ?? '')));
        if ($mime !== '' && ! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new RuntimeException('Dit videoformaat wordt niet ondersteund.');
        }

        $uploadId = (string) Str::uuid();
        $dir = $this->uploadDir($uploadId);
        File::ensureDirectoryExists($dir);

        $meta = [
            'upload_id' => $uploadId,
            'owner_hash' => hash('sha256', $ownerKey),
            'client_id' => (string) $data['client_id'],
            'name' => $name,
            'mime' => $mime,
            'size' => $size,
            'chunk_size' => self::CHUNK_BYTES,
            'total_chunks' => (int) ceil($size / self::CHUNK_BYTES),
            'created_at' => time(),
        ];

        $this->writeMeta($uploadId, $meta);

        return $meta;
    }

    public function status(string $ownerKey, string $uploadId): array
    {
        $meta = $this->metaFor($ownerKey, $uploadId);
        $received = [];
        for ($i = 0; $i < $meta['total_chunks']; $i++) {
            if (is_file($this->chunkPath($uploadId, $i))) {
                $received[] = $i;
            }
        }

        return [
            'upload_id' => $uploadId,
            'chunk_size' => $meta['chunk_size'],
            'total_chunks' => $meta['total_chunks'],
            'received' => $received,
        ];
    }

    public function storeChunk(string $ownerKey, string $uploadId, int $index, UploadedFile $chunk): array
    {
        $meta = $this->metaFor($ownerKey, $uploadId);
        if ($index < 0 || $index >= $meta['total_chunks']) {
            throw new RuntimeException('Ongeldig videodeel.');
        }
        if (! $chunk->isValid()) {
            throw new RuntimeException('Het videodeel kon niet worden ontvangen.');
        }
        if ($chunk->getSize() > self::CHUNK_BYTES) {
            throw new RuntimeException('Een videodeel is te groot.');
        }

        $target = $this->chunkPath($uploadId, $index);
        $chunk->move(dirname($target), basename($target));

        return $this->status($ownerKey, $uploadId);
    }

    public function complete(string $ownerKey, string $uploadId): array
    {
        $meta = $this->metaFor($ownerKey, $uploadId);
        for ($i = 0; $i < $meta['total_chunks']; $i++) {
            if (! is_file($this->chunkPath($uploadId, $i))) {
                throw new RuntimeException('De video is nog niet volledig geüpload.');
            }
        }

        $extension = strtolower(pathinfo($meta['name'], PATHINFO_EXTENSION));
        $relative = 'live-chat/videos/'.date('Y/m').'/'.Str::uuid().'.'.$extension;
        $absolute = Storage::disk('public')->path($relative);
        File::ensureDirectoryExists(dirname($absolute));

        $out = fopen($absolute, 'wb');
        if (! $out) {
            throw new RuntimeException('De video kon niet worden opgeslagen.');
        }

        try {
            for ($i = 0; $i < $meta['total_chunks']; $i++) {
                $in = fopen($this->chunkPath($uploadId, $i), 'rb');
                if (! $in) {
                    throw new RuntimeException('Een videodeel kon niet worden gelezen.');
                }
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
        } finally {
            fclose($out);
        }

        $actualSize = filesize($absolute) ?: 0;
        if ($actualSize !== (int) $meta['size']) {
            @unlink($absolute);
            throw new RuntimeException('De geüploade video is onvolledig. Probeer opnieuw.');
        }

        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($absolute) ?: '';
        if (! in_array($detectedMime, self::ALLOWED_MIMES, true)) {
            @unlink($absolute);
            throw new RuntimeException('Het geüploade bestand is geen ondersteunde video.');
        }

        $this->cancel($ownerKey, $uploadId);

        return [
            'path' => $relative,
            'name' => $meta['name'],
            'mime' => $detectedMime,
            'size' => $actualSize,
            'client_id' => $meta['client_id'],
        ];
    }

    public function cancel(string $ownerKey, string $uploadId): void
    {
        $this->metaFor($ownerKey, $uploadId);
        File::deleteDirectory($this->uploadDir($uploadId));
    }


    private function cleanupExpired(): void
    {
        $root = storage_path('app/live-chat-chunks');
        if (! is_dir($root)) {
            return;
        }

        foreach (File::directories($root) as $directory) {
            $metaPath = $directory.'/meta.json';
            $createdAt = 0;

            if (is_file($metaPath)) {
                $meta = json_decode((string) file_get_contents($metaPath), true);
                $createdAt = is_array($meta) ? (int) ($meta['created_at'] ?? 0) : 0;
            }

            if ($createdAt === 0) {
                $createdAt = (int) (@filemtime($directory) ?: 0);
            }

            if ($createdAt < time() - 86400) {
                File::deleteDirectory($directory);
            }
        }
    }

    private function metaFor(string $ownerKey, string $uploadId): array
    {
        if (! Str::isUuid($uploadId)) {
            throw new RuntimeException('Ongeldige upload.');
        }
        $path = $this->metaPath($uploadId);
        if (! is_file($path)) {
            throw new RuntimeException('Deze upload bestaat niet meer.');
        }
        $meta = json_decode((string) file_get_contents($path), true);
        if (! is_array($meta) || ! hash_equals((string) $meta['owner_hash'], hash('sha256', $ownerKey))) {
            throw new RuntimeException('Geen toegang tot deze upload.');
        }
        if ((int) ($meta['created_at'] ?? 0) < time() - 86400) {
            File::deleteDirectory($this->uploadDir($uploadId));
            throw new RuntimeException('Deze upload is verlopen. Start opnieuw.');
        }
        return $meta;
    }

    private function writeMeta(string $uploadId, array $meta): void
    {
        file_put_contents($this->metaPath($uploadId), json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), LOCK_EX);
    }

    private function uploadDir(string $uploadId): string
    {
        return storage_path('app/live-chat-chunks/'.$uploadId);
    }

    private function metaPath(string $uploadId): string
    {
        return $this->uploadDir($uploadId).'/meta.json';
    }

    private function chunkPath(string $uploadId, int $index): string
    {
        return $this->uploadDir($uploadId).'/chunk-'.$index.'.part';
    }
}
