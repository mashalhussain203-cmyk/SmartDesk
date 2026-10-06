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
            return back()->withErrors(['url' => $exception->getMessage()])->withInput();
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'url' => 'Deze TikTok-link kon nu niet worden geopend. Probeer een volledige video-URL.',
            ])->withInput();
        }

        return redirect()->route('tiktok-counter.show', [
            'videoId' => $video['video_id'],
            'url' => $video['url'],
        ]);
    }

    public function show(Request $request, string $videoId): View
    {
        abort_unless((bool) preg_match('/^\d{12,24}$/', $videoId), 404);

        $url = (string) $request->query('url', 'https://www.tiktok.com/@_/video/'.$videoId);

        return view('tools.tiktok-counter', [
            'videoId' => $videoId,
            'videoUrl' => $url,
        ]);
    }

    public function stats(Request $request, string $videoId, TikTokVideoStatsService $service): JsonResponse
    {
        abort_unless((bool) preg_match('/^\d{12,24}$/', $videoId), 404);

        $url = (string) $request->query('url', 'https://www.tiktok.com/@_/video/'.$videoId);

        try {
            $data = $service->get($url, $request->boolean('fresh'));

            if ($data['video_id'] !== $videoId) {
                return response()->json([
                    'message' => 'De video-ID komt niet overeen met de opgegeven TikTok-link.',
                ], 422);
            }

            return response()->json($data);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'De TikTok-statistieken konden niet worden opgehaald.',
            ], 503);
        }
    }
}
