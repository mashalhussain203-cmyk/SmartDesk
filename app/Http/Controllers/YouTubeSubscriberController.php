<?php

namespace App\Http\Controllers;

use App\Services\YouTubeLiveCountsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class YouTubeSubscriberController extends Controller
{
    public function index(): View
    {
        return view('tools.youtube-subscribers');
    }

    public function show(string $channelId): View
    {
        abort_unless(
            preg_match('/^UC[A-Za-z0-9_-]{22}$/', $channelId) === 1,
            404
        );

        return view('tools.youtube-subscribers', [
            'initialChannelId' => $channelId,
        ]);
    }

    public function lookup(
        Request $request,
        YouTubeLiveCountsService $service
    ): JsonResponse {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        try {
            $channels = $service->searchChannels(
                trim((string) $validated['query'])
            );

            return $this->noStore(
                response()->json([
                    'success' => true,
                    'channels' => $channels,
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'YouTube-kanalen konden niet worden opgehaald.',
                    'channels' => [],
                ], 502)
            );
        }
    }

    public function embed(string $channelId): View
    {
        abort_unless(
            preg_match('/^UC[A-Za-z0-9_-]{22}$/', $channelId) === 1,
            404
        );

        return view('tools.youtube-subscribers-embed', [
            'channelId' => $channelId,
        ]);
    }

    public function stats(
        string $channelId,
        YouTubeLiveCountsService $service
    ): JsonResponse {
        if (!preg_match('/^UC[A-Za-z0-9_-]{22}$/', $channelId)) {
            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'Ongeldig kanaal-ID.',
                ], 422)
            );
        }

        try {
            $stats = $service->getChannelStats($channelId);

            return $this->noStore(
                response()->json([
                    'success' => true,
                    'id' => $stats['id'],
                    'title' => $stats['title'],
                    'avatar' => $stats['avatar'],
                    'banner' => $stats['banner'] ?? null,
                    'description' => $stats['description'] ?? null,
                    'url' => $stats['url'],
                    'subscribers' => $stats['subscribers'],
                    'views' => $stats['views'],
                    'videos' => $stats['videos'],
                    'goal' => $stats['goal'],
                    'hidden' => (bool) ($stats['hidden'] ?? false),
                    'source' => $stats['source'],
                    'updated_at' => now()->toIso8601String(),
                ])
            );
        } catch (Throwable $e) {
            report($e);

            return $this->noStore(
                response()->json([
                    'success' => false,
                    'message' => 'YouTube live-statistieken konden niet worden opgehaald.',
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
