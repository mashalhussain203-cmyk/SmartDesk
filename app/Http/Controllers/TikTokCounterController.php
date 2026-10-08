<?php

namespace App\Http\Controllers;

use App\Services\TikTokVideoStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class TikTokCounterController extends Controller
{
    public function liveCountsIndex(): View
    {
        return view('tools.live-counts');
    }

    public function index(): View
    {
        return view('tools.tiktok-counter');
    }

    public function engagementIndex(): View
    {
        return view('tools.tiktok-engagement');
    }

    public function engagementLookup(
        Request $request,
        TikTokVideoStatsService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
            'service' => ['required', 'in:hearts,comments,favorites'],
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
            'tiktok-engagement.show',
            [
                'videoId' => $video['video_id'],
                'url' => $video['url'],
                'service' => $validated['service'],
            ]
        );
    }

    public function engagementShow(
        string $videoId,
        Request $request,
        TikTokVideoStatsService $service
    ): Response|RedirectResponse {
        $videoUrl = trim((string) $request->query('url', ''));
        $serviceKey = trim((string) $request->query('service', 'hearts'));

        if (!in_array($serviceKey, ['hearts', 'comments', 'favorites'], true)) {
            $serviceKey = 'hearts';
        }

        if ($videoUrl === '') {
            return redirect()
                ->route('tiktok-engagement.index')
                ->withErrors([
                    'url' => 'De TikTok URL ontbreekt. Plak de video opnieuw.',
                ]);
        }

        try {
            $video = $service->resolveVideo($videoUrl);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('tiktok-engagement.index')
                ->withInput([
                    'url' => $videoUrl,
                    'service' => $serviceKey,
                ])
                ->withErrors([
                    'url' => $e->getMessage(),
                ]);
        }

        if ((string) $video['video_id'] !== (string) $videoId) {
            return redirect()->route(
                'tiktok-engagement.show',
                [
                    'videoId' => $video['video_id'],
                    'url' => $video['url'],
                    'service' => $serviceKey,
                ]
            );
        }

        return response()
            ->view('tools.tiktok-engagement', [
                'videoId' => $video['video_id'],
                'videoUrl' => $video['url'],
                'selectedService' => $serviceKey,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function engagementStats(
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
                ], 422)
            );
        }

        try {
            $stats = $service->getSupplementalTikTokStats(
                $videoUrl,
                $videoId
            );

            return $this->noStore(
                response()->json([
                    'success' => true,
                    'video_id' => $videoId,
                    'stats' => [
                        'hearts' => $stats['likes'] ?? null,
                        'comments' => $stats['comments'] ?? null,
                        'favorites' => $stats['favorites'] ?? null,
                    ],
                    'author_name' => $stats['author_name'] ?? null,
                    'title' => $stats['title'] ?? null,
                    'thumbnail_url' => $stats['thumbnail_url'] ?? null,
                    'source' => $stats['source'] ?? 'tiktok-public-video-page',
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'updated_at' => now()->toIso8601String(),
                ], 502)
            );
        }
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
    ): Response|RedirectResponse {
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

        return response()
            ->view('tools.tiktok-counter', [
                'videoId' => $video['video_id'],
                'videoUrl' => $video['url'],
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function followerIndex(): View
    {
        return view('tools.tiktok-follower-counter');
    }

    public function followerLookup(
        Request $request,
        TikTokVideoStatsService $service
    ): RedirectResponse {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ]);

        try {
            $account = $service->resolveTikTokUsername($validated['username']);
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'username' => $e->getMessage(),
                ]);
        }

        return redirect()->route(
            'tiktok-follower-counter.show',
            ['username' => $account['username']]
        );
    }

    public function followerSearch(
        Request $request,
        TikTokVideoStatsService $service
    ): JsonResponse {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return $this->noStore(
                response()->json([
                    'success' => true,
                    'query' => $query,
                    'results' => [],
                ])
            );
        }

        if (mb_strlen($query) > 40) {
            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'Zoekterm is te lang.',
                    'results' => [],
                ], 422)
            );
        }

        try {
            $results = $service->searchLiveFollowerUsers($query);

            return $this->noStore(
                response()->json([
                    'success' => true,
                    'query' => $query,
                    'results' => $results,
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'Accounts konden niet worden gezocht.',
                    'results' => [],
                ], 502)
            );
        }
    }

    public function followerShow(
        string $username,
        TikTokVideoStatsService $service
    ): Response|RedirectResponse {
        try {
            $account = $service->resolveTikTokUsername($username);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('tiktok-follower-counter.index')
                ->withErrors([
                    'username' => $e->getMessage(),
                ]);
        }

        if ((string) $account['username'] !== (string) $username) {
            return redirect()->route(
                'tiktok-follower-counter.show',
                ['username' => $account['username']]
            );
        }

        return response()
            ->view('tools.tiktok-follower-counter', [
                'username' => $account['username'],
                'profileUrl' => $account['profile_url'],
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function followerCards(
        string $username,
        TikTokVideoStatsService $service
    ): JsonResponse {
        try {
            $stats = $service->getLiveFollowerCardStats($username);

            return $this->noStore(
                response()->json([
                    'success' => true,
                    'username' => $username,
                    'stats' => [
                        'followers' => $stats['followers'] ?? null,
                        'likes' => $stats['likes'] ?? null,
                        'following' => $stats['following'] ?? null,
                        'videos' => $stats['videos'] ?? null,
                    ],
                    'display_name' => $stats['display_name'] ?? null,
                    'avatar_url' => $stats['avatar_url'] ?? null,
                    'source' => $stats['source'] ?? 'livecounts-follower-public-page-rendered',
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'updated_at' => now()->toIso8601String(),
                ], 502)
            );
        }
    }

    public function livecountsCards(
        string $videoId,
        TikTokVideoStatsService $service
    ): JsonResponse {
        try {
            $stats = $service->getLivecountsCardStats($videoId);

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
                    'source' => $stats['source'] ?? 'livecounts-public-page-rendered',
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'updated_at' => now()->toIso8601String(),
                ], 502)
            );
        }
    }

    public function supplemental(
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
                ], 422)
            );
        }

        try {
            $stats = $service->getSupplementalTikTokStats(
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
                        'favorites' => $stats['favorites'] ?? null,
                    ],
                    'source' => $stats['source'] ?? 'tiktok-public-video-page',
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'updated_at' => now()->toIso8601String(),
                ], 502)
            );
        }
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
                        'favorites' => $stats['favorites'] ?? null,
                    ],

                    'author_name' => $stats['author_name'] ?? null,
                    'title' => $stats['title'] ?? null,
                    'thumbnail_url' => $stats['thumbnail_url'] ?? null,

                    /*
                     * Debug / observability.
                     */
                    'source' => $stats['source'] ?? 'tiktok',
                    'precision' => $stats['precision'] ?? 'unknown',
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
