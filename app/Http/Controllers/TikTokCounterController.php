<?php

namespace App\Http\Controllers;

use App\Services\TikTokVideoStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class TikTokCounterController extends Controller
{
    public function index(): View
    {
        return view('tools.tiktok-counter');
    }

    public function lookup(Request $request, TikTokVideoStatsService $service): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        try {
            $video = $service->inspect($validated['url']);
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withErrors(['url' => $exception->getMessage()])
                ->withInput();
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors([
                    'url' => 'Deze TikTok-link kon nu niet worden geopend. Probeer een volledige video-URL.',
                ])
                ->withInput();
        }

        return redirect()->route('tiktok-counter.show', [
            'videoId' => $video['video_id'],
            'url' => $video['url'],
        ]);
    }

    public function show(Request $request, string $videoId): View
    {
        abort_unless((bool) preg_match('/^\d{12,24}$/', $videoId), 404);

        $url = (string) $request->query(
            'url',
            'https://www.tiktok.com/@_/video/'.$videoId
        );

        return view('tools.tiktok-counter', [
            'videoId' => $videoId,
            'videoUrl' => $url,
        ]);
    }

    /**
     * Dit endpoint wordt continu door de browser aangeroepen.
     * Iedere request gaat opnieuw naar TikTok; er is geen applicatiecache.
     */
    public function stats(
        Request $request,
        string $videoId,
        TikTokVideoStatsService $service
    ): JsonResponse {
        abort_unless((bool) preg_match('/^\d{12,24}$/', $videoId), 404);

        $url = (string) $request->query(
            'url',
            'https://www.tiktok.com/@_/video/'.$videoId
        );

        try {
            $data = $service->getLive($url);

            if (($data['video_id'] ?? null) !== $videoId) {
                return $this->noStoreJson([
                    'success' => false,
                    'message' => 'De video-ID komt niet overeen met de opgegeven TikTok-link.',
                ], 422);
            }

            return $this->noStoreJson([
                'success' => true,
                ...$data,
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->noStoreJson([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return $this->noStoreJson([
                'success' => false,
                'message' => 'De TikTok-statistieken konden niet live worden opgehaald.',
            ], 503);
        }
    }

    private function noStoreJson(array $payload, int $status = 200): JsonResponse
    {
        return response()
            ->json($payload, $status)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
