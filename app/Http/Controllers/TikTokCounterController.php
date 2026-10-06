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
    /**
     * Show the TikTok Live Count start page.
     */
    public function index(): View
    {
        return view('tools.tiktok-counter');
    }

    /**
     * Accept a TikTok URL and redirect to the live counter page.
     */
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

    /**
     * Show the live counter for one TikTok video.
     */
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

        /*
         * If the URL resolves to another TikTok id,
         * redirect to the canonical counter URL.
         */
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
     * JSON endpoint called by the frontend every 4 seconds.
     *
     * Every request calls TikTokVideoStatsService again.
     * Nothing is cached in this controller.
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
            /*
             * This must perform a fresh provider request every time.
             */
            $stats = $service->getLiveStats(
                $videoUrl,
                $videoId
            );

            return $this->noStore(
                response()->json([
                    'success' => true,

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
                     * Useful for DevTools:
                     * this proves each poll reached Laravel.
                     */
                    'request_id' => $request->query('_request'),
                    'fetched_fresh' => true,
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            /*
             * IMPORTANT:
             * We intentionally return the provider/service error message
             * so you can see exactly why the request failed instead of only
             * getting "Nieuwe TikTok-data kon niet worden opgehaald".
             *
             * Do not include secrets/tokens in exception messages.
             */
            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'error_type' => class_basename($e),
                    'request_id' => $request->query('_request'),
                    'updated_at' => now()->toIso8601String(),
                ], 502)
            );
        }
    }

    /**
     * Disable browser/proxy caching for all live-count JSON responses.
     */
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
