<?php

namespace App\Http\Controllers;

use App\Services\TikTokVideoStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TikTokCounterController extends Controller
{
    public function index(): View
    {
        return view('tools.tiktok-counter');
    }

    public function lookup(
        Request $request,
        TikTokVideoStatsService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        try {
            $video = $service->resolveVideo($validated['url']);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'url' => $e->getMessage(),
                ]);
        }

        return redirect()->route(
            'tiktok-counter.show',
            [
                'videoId' => $video['video_id'],
                'url' => $video['url'],
            ]
        );
    }

    public function show(
        string $videoId,
        Request $request,
        TikTokVideoStatsService $service
    ): View|RedirectResponse {
        $videoUrl = trim((string) $request->query('url', ''));

        if ($videoUrl === '') {
            return redirect()
                ->route('tiktok-counter.index')
                ->withErrors([
                    'url' => 'De TikTok URL ontbreekt. Plak de video opnieuw.',
                ]);
        }

        try {
            $video = $service->resolveVideo($videoUrl);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('tiktok-counter.index')
                ->withInput([
                    'url' => $videoUrl,
                ])
                ->withErrors([
                    'url' => $e->getMessage(),
                ]);
        }

        if ((string) $video['video_id'] !== (string) $videoId) {
            return redirect()->route(
                'tiktok-counter.show',
                [
                    'videoId' => $video['video_id'],
                    'url' => $video['url'],
                ]
            );
        }

        return view('tools.tiktok-counter', [
            'videoId' => $video['video_id'],
            'videoUrl' => $video['url'],
        ]);
    }

    /**
     * Live JSON endpoint.
     *
     * The browser can call this every 4 seconds.
     * The service itself makes sure multiple visitors share one TikTok snapshot.
     */
    public function stats(
        string $videoId,
        Request $request,
        TikTokVideoStatsService $service
    ): JsonResponse {
        $videoUrl = trim((string) $request->query('url', ''));

        if ($videoUrl === '') {
            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'TikTok URL ontbreekt.',
                    'request_id' => $request->query('_request'),
                    'updated_at' => now()->toIso8601String(),
                ], 422)
            );
        }

        try {
            $stats = $service->getLiveStats(
                $videoUrl,
                $videoId
            );

            return $this->noStore(
                response()->json([
                    'success' => true,

                    'video_id' => $videoId,

                    'stats' => [
                        'views' => $stats['views'] ?? null,
                        'likes' => $stats['likes'] ?? null,
                        'comments' => $stats['comments'] ?? null,
                        'shares' => $stats['shares'] ?? null,
                    ],

                    'author_name' => $stats['author_name'] ?? null,
                    'title' => $stats['title'] ?? null,
                    'thumbnail_url' => $stats['thumbnail_url'] ?? null,

                    /*
                     * Debug / observability.
                     */
                    'source' => $stats['source'] ?? 'tiktok',
                    'fetched_fresh' => (bool) ($stats['fetched_fresh'] ?? false),
                    'stale_fallback' => (bool) ($stats['stale_fallback'] ?? false),
                    'snapshot_age_ms' => (int) ($stats['snapshot_age_ms'] ?? 0),
                    'warning' => $stats['warning'] ?? null,
                    'last_error' => $stats['last_error'] ?? null,
                    'debug' => $stats['_debug'] ?? null,

                    'request_id' => $request->query('_request'),
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            $message = $e->getMessage();
            $debug = null;

            if (str_starts_with($message, 'TIKTOK_DEBUG:')) {
                $debugJson = substr($message, strlen('TIKTOK_DEBUG:'));
                $decoded = json_decode($debugJson, true);

                if (is_array($decoded)) {
                    $debug = $decoded;
                    $message = (string) ($decoded['message'] ?? 'TikTok debug-fout.');
                }
            }

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_type' => class_basename($e),
                    'debug' => $debug,
                    'request_id' => $request->query('_request'),
                    'updated_at' => now()->toIso8601String(),
                ], 502)
            );
        }
    }

    private function noStore(JsonResponse $response): JsonResponse
    {
        return $response
            ->header(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0, private'
            )
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Surrogate-Control', 'no-store');
    }
}
