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
                $data[$account] = Cache::remember('live-room-probe:'.$account, 60, function () use ($account): array {
                    try {
                        $response = Http::connectTimeout(4)->timeout(7)
                            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; SmartDeskLiveStatus/1.0)'])
                            ->get('https://chaturbate.com/'.rawurlencode($account).'/');

                        if (! $response->successful()) {
                            return ['status' => 'unknown', 'message' => 'Bronpagina tijdelijk niet bereikbaar'];
                        }

                        $html = $response->body();
                        if (preg_match('/["\\']room_status["\\']\s*:\s*["\\'](public|private|group|away|chat|hidden|offline|disconnected)["\\']/i', $html, $match)) {
                            $roomState = strtolower($match[1]);
                            $isLive = in_array($roomState, ['public', 'private', 'group', 'away', 'chat'], true);
                            return ['status' => $isLive ? 'live' : 'offline',
                                'message' => $isLive ? 'Bronpagina bevestigt een actieve kamer' : 'Bronpagina meldt offline',
                                'checked_at' => now()->toIso8601String()];
                        }

                        return ['status' => 'unknown', 'message' => 'Geen betrouwbare live-status beschikbaar'];
                    } catch (\Throwable $exception) {
                        report($exception);
                        return ['status' => 'unknown', 'message' => 'Live-controle tijdelijk niet beschikbaar'];
                    }
                });
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
