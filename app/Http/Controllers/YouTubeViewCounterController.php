<?php

namespace App\Http\Controllers;

use App\Services\YouTubeLiveCountsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class YouTubeViewCounterController extends Controller
{
    public function index(): Response
    {
        return $this->noStorePage(
            response()->view('tools.youtube-views')
        );
    }

    public function show(string $videoId): Response
    {
        abort_unless(
            preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) === 1,
            404
        );

        return $this->noStorePage(
            response()->view('tools.youtube-views', [
                'initialVideoId' => $videoId,
            ])
        );
    }

    public function lookup(
        Request $request,
        YouTubeLiveCountsService $service
    ): JsonResponse {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        try {
            $videos = $service->searchVideos(
                trim((string) $validated['query'])
            );

            return $this->noStore(
                response()->json([
                    'success' => true,
                    'videos' => $videos,
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'YouTube-video’s konden niet worden opgehaald.',
                    'videos' => [],
                ], 502)
            );
        }
    }

    public function stats(
        string $videoId,
        YouTubeLiveCountsService $service
    ): JsonResponse {
        if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'Ongeldig video-ID.',
                ], 422)
            );
        }

        try {
            $stats = $service->getVideoStats($videoId);

            return $this->noStore(
                response()->json([
                    'success' => true,
                    'id' => $stats['id'],
                    'title' => $stats['title'],
                    'thumbnail' => $stats['thumbnail'],
                    'channel' => $stats['channel'] ?? null,
                    'description' => $stats['description'] ?? null,
                    'url' => $stats['url'],
                    'views' => $stats['views'],
                    'likes' => $stats['likes'],
                    'dislikes' => $stats['dislikes'],
                    'comments' => $stats['comments'],
                    'source' => $stats['source'],
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'YouTube live views konden niet worden opgehaald.',
                    'updated_at' => now()->toIso8601String(),
                ], 502)
            );
        }
    }

    public function embed(string $videoId): View
    {
        abort_unless(
            preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) === 1,
            404
        );

        return view('tools.youtube-views-embed', [
            'videoId' => $videoId,
        ]);
    }

    private function noStorePage(Response $response): Response
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
