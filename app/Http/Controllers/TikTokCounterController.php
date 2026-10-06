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
        $videoUrl = (string) $request->query('url', '');

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
            return redirect()
                ->route('tiktok-counter.index')
                ->withInput(['url' => $videoUrl])
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

    public function stats(
        string $videoId,
        Request $request,
        TikTokVideoStatsService $service
    ): JsonResponse {
        $videoUrl = (string) $request->query('url', '');

        if ($videoUrl === '') {
            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'TikTok URL ontbreekt.',
                ], 422)
            );
        }

        try {
            /*
             * IMPORTANT:
             * getLiveStats() does a fresh TikTok HTTP request every time.
             * There is intentionally no Cache::remember() here.
             */
            $stats = $service->getLiveStats(
                $videoUrl,
                $videoId
            );

            return $this->noStore(
                response()->json([
                    'success' => true,

                    'stats' => [
                        'views' => $stats['views'],
                        'likes' => $stats['likes'],
                        'comments' => $stats['comments'],
                        'shares' => $stats['shares'],
                    ],

                    'author_name' => $stats['author_name'] ?? null,
                    'title' => $stats['title'] ?? null,
                    'thumbnail_url' => $stats['thumbnail_url'] ?? null,

                    'updated_at' => now()->toIso8601String(),

                    /*
                     * These fields make it easy to verify in DevTools that
                     * every browser poll reached Laravel again.
                     */
                    'request_id' => $request->query('_request'),
                    'fetched_fresh' => true,
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'Nieuwe TikTok-data kon niet worden opgehaald.',
                    'updated_at' => now()->toIso8601String(),
                    'request_id' => $request->query('_request'),
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
