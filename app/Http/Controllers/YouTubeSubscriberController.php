<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class YouTubeSubscriberController extends Controller
{
    public function index(): View
    {
        return view('tools.youtube-subscribers');
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate(['query' => ['required', 'string', 'max:255']]);
        $input = trim($validated['query']);
        $channelId = null;
        $handle = null;

        if (preg_match('~(?:youtube\.com/)?channel/(UC[A-Za-z0-9_-]{22})~i', $input, $match) ||
            preg_match('/^(UC[A-Za-z0-9_-]{22})$/', $input, $match)) {
            $channelId = $match[1];
        } elseif (preg_match('~(?:youtube\.com/)?@([A-Za-z0-9._-]+)~i', $input, $match)) {
            $handle = $match[1];
        } elseif (preg_match('/^@[A-Za-z0-9._-]+$/', $input)) {
            $handle = substr($input, 1);
        }

        $key = config('services.youtube.api_key');
        if (!$key) {
            return response()->json(['message' => 'YouTube API is nog niet ingesteld. Voeg YOUTUBE_API_KEY toe aan Railway Variables.'], 503);
        }

        try {
            if ($channelId || $handle) {
                $params = $channelId ? ['id' => $channelId] : ['forHandle' => $handle];
                $payload = $this->youtube('channels', $params + ['part' => 'snippet,statistics']);
                $items = $payload['items'] ?? [];
            } else {
                $result = $this->youtube('search', ['part' => 'snippet', 'q' => $input, 'type' => 'channel', 'maxResults' => 5]);
                $ids = array_values(array_filter(array_map(fn ($item) => $item['snippet']['channelId'] ?? null, $result['items'] ?? [])));
                $items = $ids ? ($this->youtube('channels', ['part' => 'snippet,statistics', 'id' => implode(',', $ids)])['items'] ?? []) : [];
            }

            return response()->json([
                'channels' => array_map(fn ($item) => $this->formatChannel($item), $items),
            ])->header('Cache-Control', 'no-store');
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'YouTube-gegevens konden niet worden opgehaald. Controleer je API-sleutel en probeer opnieuw.'], 502);
        }
    }

    public function stats(string $channelId): JsonResponse
    {
        if (!preg_match('/^UC[A-Za-z0-9_-]{22}$/', $channelId)) {
            return response()->json(['message' => 'Ongeldig kanaal-ID.'], 422);
        }
        if (!config('services.youtube.api_key')) {
            return response()->json(['message' => 'YOUTUBE_API_KEY ontbreekt in de serverinstellingen.'], 503);
        }
        try {
            $item = Cache::remember('youtube:channel:'. $channelId, now()->addSeconds(60), function () use ($channelId) {
                return $this->youtube('channels', ['part' => 'snippet,statistics', 'id' => $channelId])['items'][0] ?? null;
            });
            if (!$item) {
                return response()->json(['message' => 'Kanaal niet gevonden.'], 404);
            }
            return response()->json($this->formatChannel($item))
                ->header('Cache-Control', 'no-store');
        } catch (Throwable $e) {
            report($e);
            return response()->json(['message' => 'Vernieuwen van YouTube-statistieken mislukt.'], 502);
        }
    }

    private function youtube(string $endpoint, array $params): array
    {
        $response = Http::timeout(12)->acceptJson()->get('https://www.googleapis.com/youtube/v3/'.$endpoint, $params + [
            'key' => config('services.youtube.api_key'),
        ]);
        $response->throw();
        return $response->json() ?? [];
    }

    private function formatChannel(array $item): array
    {
        $stats = $item['statistics'] ?? [];
        $snippet = $item['snippet'] ?? [];
        $id = (string) ($item['id'] ?? '');
        return [
            'id' => $id,
            'title' => (string) ($snippet['title'] ?? 'YouTube-kanaal'),
            'avatar' => $snippet['thumbnails']['medium']['url'] ?? $snippet['thumbnails']['default']['url'] ?? null,
            'url' => 'https://www.youtube.com/channel/'.$id,
            'subscribers' => isset($stats['subscriberCount']) ? (int) $stats['subscriberCount'] : null,
            'views' => isset($stats['viewCount']) ? (int) $stats['viewCount'] : null,
            'videos' => isset($stats['videoCount']) ? (int) $stats['videoCount'] : null,
            'hidden' => (bool) ($stats['hiddenSubscriberCount'] ?? false),
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
