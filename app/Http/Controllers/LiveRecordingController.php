<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LiveRecordingController extends Controller
{
    private const ACCOUNTS = ['knock1knock', 'emyii'];

    /** Only the existing SmartDesk admin account may access this controller. */
    private function requireAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin() === true, 403);
    }

    public function index(): View
    {
        $this->requireAdmin();

        $recordings = [];
        foreach (self::ACCOUNTS as $account) {
            foreach (Storage::disk('local')->files("live-recordings/{$account}") as $path) {
                $filename = basename($path);
                if (! preg_match('/^[A-Za-z0-9_.-]+\.mp4$/', $filename)) {
                    continue;
                }

                $recordings[] = [
                    'account' => $account,
                    'filename' => $filename,
                    'bytes' => Storage::disk('local')->size($path),
                    'created_at' => Storage::disk('local')->lastModified($path),
                ];
            }
        }

        usort($recordings, fn (array $a, array $b) => $b['created_at'] <=> $a['created_at']);

        return view('admin.live-recordings', [
            'recordings' => $recordings,
            'accounts' => self::ACCOUNTS,
            'statuses' => $this->statuses(),
        ]);
    }

    public function status()
    {
        $this->requireAdmin();

        return response()->json($this->statuses())->header('Cache-Control', 'private, no-store');
    }

    public function play(string $account, string $filename): BinaryFileResponse
    {
        $this->requireAdmin();

        return response()->file($this->safePath($account, $filename), [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function download(string $account, string $filename): BinaryFileResponse
    {
        $this->requireAdmin();

        return response()->download($this->safePath($account, $filename), $filename, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, string $account, string $filename)
    {
        $this->requireAdmin();
        $this->safePath($account, $filename);

        Storage::disk('local')->delete("live-recordings/{$account}/{$filename}");

        return redirect()->route('live.index')->with('success', 'Opname verwijderd.');
    }

    private function safePath(string $account, string $filename): string
    {
        abort_unless(in_array($account, self::ACCOUNTS, true), 404);
        abort_unless((bool) preg_match('/^[A-Za-z0-9_.-]+\.mp4$/D', $filename), 404);

        $path = "live-recordings/{$account}/{$filename}";
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->path($path);
    }

    private function statuses(): array
    {
        $data = [];
        foreach (self::ACCOUNTS as $account) {
            $path = "live-recordings/{$account}/status.json";
            $status = null;
            if (Storage::disk('local')->exists($path)) {
                $parsed = json_decode(Storage::disk('local')->get($path), true);
                if (is_array($parsed)) {
                    $status = $parsed;
                }
            }

            $updatedAt = isset($status['checked_at']) ? strtotime((string) $status['checked_at']) : false;
            if (! $updatedAt || $updatedAt < time() - 180) {
                $data[$account] = ['status' => 'unknown', 'message' => 'Recorder niet actief of status verouderd'];
                continue;
            }

            $state = (string) ($status['status'] ?? 'unknown');
            if (! in_array($state, ['offline', 'live', 'recording', 'uploading', 'error', 'needs_setup', 'unknown'], true)) {
                $state = 'unknown';
            }
            $data[$account] = [
                'status' => $state,
                'checked_at' => gmdate('c', $updatedAt),
                'message' => mb_substr((string) ($status['message'] ?? ''), 0, 180),
            ];
        }

        return $data;
    }
}
