<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/tools/youtube-downloader', function () {
    return view('tools.youtube-downloader');
})->name('youtube-downloader.index');

Route::post('/tools/youtube-downloader/inspect', function (Request $request) {
    $validated = $request->validate([
        'url' => ['required', 'url', 'max:2048'],
    ]);

    $url = $validated['url'];
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $allowedHosts = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtube-nocookie.com'];

    if (!in_array($host, $allowedHosts, true)) {
        return response()->json(['message' => 'Gebruik een geldige YouTube-link.'], 422);
    }

    $videoId = null;
    if ($host === 'youtu.be') {
        $videoId = trim((string) parse_url($url, PHP_URL_PATH), '/');
    } else {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $videoId = $query['v'] ?? null;
        if (!$videoId && preg_match('~/(?:shorts|embed|live)/([A-Za-z0-9_-]{11})~', (string) parse_url($url, PHP_URL_PATH), $matches)) {
            $videoId = $matches[1];
        }
    }

    if (!is_string($videoId) || !preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
        return response()->json(['message' => 'Deze YouTube-link bevat geen herkenbare video.'], 422);
    }

    try {
        $response = Http::timeout(8)->get('https://www.youtube.com/oembed', [
            'url' => 'https://www.youtube.com/watch?v=' . $videoId,
            'format' => 'json',
        ]);
        if (!$response->successful()) {
            return response()->json(['message' => 'YouTube kon geen openbare videogegevens teruggeven. Controleer de link.'], 422);
        }
        $data = $response->json();
        return response()->json([
            'id' => $videoId,
            'title' => $data['title'] ?? 'YouTube-video',
            'author' => $data['author_name'] ?? 'YouTube',
            'thumbnail' => 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg',
            'watch_url' => 'https://www.youtube.com/watch?v=' . $videoId,
            'embed_url' => 'https://www.youtube-nocookie.com/embed/' . $videoId,
        ]);
    } catch (\Throwable $exception) {
        report($exception);
        return response()->json(['message' => 'YouTube is tijdelijk niet bereikbaar. Probeer het later opnieuw.'], 503);
    }
})->middleware('throttle:20,1')->name('youtube-downloader.inspect');
