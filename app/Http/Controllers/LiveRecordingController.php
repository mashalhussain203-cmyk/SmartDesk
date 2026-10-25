<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LiveArchiveS3;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LiveRecordingController extends Controller
{
    private const ACCOUNTS = ['knock1knock', 'emyii', 'cutefacebigass', 'ricasashaa', 'julesxdann', 'leo_kitty', 'dellris'];

    private const MONITORED_ACCOUNTS = ['knock1knock', 'emyii', 'cutefacebigass', 'ricasashaa', 'julesxdann', 'leo_kitty'];

    /** Only the existing SmartDesk admin account may access this controller. */
    private function requireAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin() === true, 403);
    }

    public function index(LiveArchiveS3 $archive): View
    {
        $this->requireAdmin();

        $recordings = [];
        $archiveError = null;
        if ($archive->configured()) {
            try {
                foreach (self::ACCOUNTS as $account) {
                    $recordings = array_merge($recordings, $archive->listVideos($account));
                }
            } catch (\Throwable $exception) {
                report($exception);
                $archiveError = 'De privéopslag is tijdelijk niet bereikbaar.';
            }
        } else foreach (self::ACCOUNTS as $account) {
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
            'accounts' => self::MONITORED_ACCOUNTS,
            'statuses' => $this->statuses($archive),
            'archiveError' => $archiveError,
        ]);
    }

    public function status(LiveArchiveS3 $archive)
    {
        $this->requireAdmin();

        return response()->json($this->statuses($archive))->header('Cache-Control', 'private, no-store');
    }

    public function play(Request $request, LiveArchiveS3 $archive, string $account, string $filename)
    {
        $this->requireAdmin();

        if ($archive->configured()) return $archive->streamVideo($request, $account, $filename, false);
        return response()->file($this->safePath($account, $filename), [
            'Content-Type' => 'video/mp4',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function download(Request $request, LiveArchiveS3 $archive, string $account, string $filename)
    {
        $this->requireAdmin();

        if ($archive->configured()) return $archive->streamVideo($request, $account, $filename, true);
        return response()->download($this->safePath($account, $filename), $filename, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function destroy(Request $request, LiveArchiveS3 $archive, string $account, string $filename)
    {
        $this->requireAdmin();
        if ($archive->configured()) {
            $archive->removeVideo($account, $filename);
        } else {
            $this->safePath($account, $filename);
            Storage::disk('local')->delete("live-recordings/{$account}/{$filename}");
        }

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

    private function statuses(LiveArchiveS3 $archive): array
    {
        $data = [];
        foreach (self::MONITORED_ACCOUNTS as $account) {
            $path = "live-recordings/{$account}/status.json";
            $status = null;
            if ($archive->configured()) {
                try {
                    $status = $archive->getStatus($account);
                } catch (\Throwable $exception) {
                    report($exception);
                    $data[$account] = ['status' => 'error', 'message' => 'Privéopslag tijdelijk niet bereikbaar'];
                    continue;
                }
            } elseif (Storage::disk('local')->exists($path)) {
                $parsed = json_decode(Storage::disk('local')->get($path), true);
                if (is_array($parsed)) $status = $parsed;
            }

            $updatedAt = isset($status['checked_at']) ? strtotime((string) $status['checked_at']) : false;
            if (! $updatedAt || $updatedAt < time() - 180) {
                // If the recorder has not refreshed status.json, probe the public room page.
                // Cache each result to avoid repeatedly requesting the provider on 15-second UI refreshes.
                $probe = Cache::remember('live-room-probe:'.$account, now()->addSeconds(60), function () use ($account): array {
                    try {
                        $response = Http::timeout(8)
                            ->connectTimeout(4)
                            ->withUserAgent('Mozilla/5.0 (compatible; SmartDeskLiveStatus/1.0)')
                            ->acceptHtml()
                            ->get('https://chaturbate.com/'.$account.'/');

                        if (! $response->successful()) {
                            return ['status' => 'unknown', 'message' => 'Bronpagina reageert niet (HTTP '.$response->status().')'];
                        }

                        $html = strtolower($response->body());
                        $liveMarkers = [
                            '"room_status":"public"', '"room_status": "public"',
                            '"room_status":"private"', '"room_status": "private"',
                            '"room_status":"group"', '"room_status": "group"',
                            '"room_status":"away"', '"room_status": "away"',
                        ];
                        foreach ($liveMarkers as $marker) {
                            if (str_contains($html, $marker)) {
                                return ['status' => 'live', 'message' => 'Live-status gecontroleerd via openbare bronpagina', 'checked_at' => gmdate('c')];
                            }
                        }

                        $offlineMarkers = [
                            '"room_status":"offline"', '"room_status": "offline"',
                            'is currently offline', 'model is offline',
                        ];
                        foreach ($offlineMarkers as $marker) {
                            if (str_contains($html, $marker)) {
                                return ['status' => 'offline', 'message' => 'Offline-status gecontroleerd via openbare bronpagina', 'checked_at' => gmdate('c')];
                            }
                        }

                        return ['status' => 'unknown', 'message' => 'Bronpagina geladen, maar live-status niet herkenbaar'];
                    } catch (\Throwable $exception) {
                        report($exception);
                        return ['status' => 'unknown', 'message' => 'Live-status tijdelijk niet bereikbaar'];
                    }
                });

                $probeUpdatedAt = isset($probe['checked_at']) ? strtotime((string) $probe['checked_at']) : false;
                $data[$account] = [
                    'status' => in_array($probe['status'] ?? 'unknown', ['live', 'offline'], true) ? $probe['status'] : 'unknown',
                    'checked_at' => $probeUpdatedAt ? gmdate('c', $probeUpdatedAt) : null,
                    'message' => mb_substr((string) ($probe['message'] ?? 'Status niet beschikbaar'), 0, 180),
                    'last_saved' => null,
                ];
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
                'last_saved' => is_string($status['last_saved'] ?? null)
                    && preg_match('/^[A-Za-z0-9_.-]+\\.mp4$/D', $status['last_saved'])
                    ? $status['last_saved'] : null,
            ];
        }

        return $data;
    }
}
