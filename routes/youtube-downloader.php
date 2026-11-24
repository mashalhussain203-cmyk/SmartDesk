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


// Real quality detection and conversion for video/audio files the user is authorized to use.
Route::post('/tools/youtube-downloader/upload', function (Request $request) {
    $validated = $request->validate([
        'media' => ['required', 'file', 'max:512000', 'mimes:mp4,mov,m4v,webm,mkv,avi,mp3,wav,m4a,aac,flac,ogg'],
    ]);

    $file = $validated['media'];
    $token = bin2hex(random_bytes(24));
    $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
    $path = $file->storeAs('youtube-downloader', $token . '.' . $extension, 'local');
    $absolute = \Illuminate\Support\Facades\Storage::disk('local')->path($path);

    $probe = new \Symfony\Component\Process\Process([
        'ffprobe', '-v', 'error', '-show_entries', 'format=duration:stream=codec_type,width,height',
        '-of', 'json', $absolute,
    ]);
    $probe->setTimeout(30);
    $probe->run();

    if (!$probe->isSuccessful()) {
        \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
        return response()->json(['message' => 'Dit bestand kon niet worden gelezen. Controleer het bestand en probeer opnieuw.'], 422);
    }

    $info = json_decode($probe->getOutput(), true) ?: [];
    $video = collect($info['streams'] ?? [])->firstWhere('codec_type', 'video');
    $audio = collect($info['streams'] ?? [])->contains(fn ($stream) => ($stream['codec_type'] ?? null) === 'audio');
    $height = (int) ($video['height'] ?? 0);
    $width = (int) ($video['width'] ?? 0);
    // Video quality is conventionally named after the shorter edge (1080p also
    // applies to portrait 1080x1920 footage).
    $sourceResolution = min($width, $height);
    $qualities = $video
        ? collect([144, 240, 360, 480, 720, 1080, 1440, 2160])
            ->filter(fn ($quality) => $quality <= $sourceResolution)
            ->values()->all()
        : [];

    if (!$video && !$audio) {
        \Illuminate\Support\Facades\Storage::disk('local')->delete($path);
        return response()->json(['message' => 'Geen bruikbare video- of audiostream gevonden.'], 422);
    }

    $request->session()->put('ytd_upload_' . $token, $path);

    return response()->json([
        'token' => $token,
        'name' => $file->getClientOriginalName(),
        'width' => $width,
        'height' => $height,
        'duration' => (float) ($info['format']['duration'] ?? 0),
        'qualities' => $qualities,
        'has_video' => (bool) $video,
        'has_audio' => $audio,
        'message' => $video
            ? 'Alleen resoluties die deze bron aankan worden getoond.'
            : 'Audiobestand herkend. MP3-conversie is beschikbaar.',
    ]);
})->middleware('throttle:10,1')->name('youtube-downloader.upload');

Route::post('/tools/youtube-downloader/convert', function (Request $request) {
    $validated = $request->validate([
        'token' => ['required', 'regex:/^[a-f0-9]{48}$/'],
        'format' => ['required', 'in:mp4,mp3'],
        'quality' => ['nullable', 'integer', 'in:144,240,360,480,720,1080,1440,2160'],
    ]);

    $key = 'ytd_upload_' . $validated['token'];
    $path = $request->session()->get($key);
    if (!$path || !\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
        return response()->json(['message' => 'Upload verlopen. Upload het bestand opnieuw.'], 410);
    }

    $input = \Illuminate\Support\Facades\Storage::disk('local')->path($path);
    $probe = new \Symfony\Component\Process\Process([
        'ffprobe', '-v', 'error', '-select_streams', 'v:0', '-show_entries', 'stream=width,height',
        '-of', 'csv=p=0:s=x', $input,
    ]);
    $probe->setTimeout(20);
    $probe->run();
    $dimensions = array_map('intval', explode('x', trim($probe->getOutput())));
    $sourceWidth = $dimensions[0] ?? 0;
    $sourceHeight = $dimensions[1] ?? 0;
    $sourceResolution = min($sourceWidth, $sourceHeight);
    $format = $validated['format'];
    $quality = (int) ($validated['quality'] ?? 0);

    if ($format === 'mp4' && ($sourceResolution < 1 || $quality < 1 || $quality > $sourceResolution)) {
        return response()->json(['message' => 'Kies een resolutie die daadwerkelijk in het bronbestand beschikbaar is.'], 422);
    }

    $outputName = 'mashal-studio-' . $validated['token'] . '.' . $format;
    $output = storage_path('app/private/' . $outputName);
    if (!is_dir(dirname($output))) {
        mkdir(dirname($output), 0775, true);
    }

    $command = ['ffmpeg', '-y', '-i', $input, '-map', '0:v:0?', '-map', '0:a:0?'];
    if ($format === 'mp4') {
        $command = array_merge($command, [
            '-vf', ($sourceWidth >= $sourceHeight
                ? 'scale=-2:' . $quality . ':force_original_aspect_ratio=decrease'
                : 'scale=' . $quality . ':-2:force_original_aspect_ratio=decrease'),
            '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '22',
            '-c:a', 'aac', '-b:a', '192k', '-movflags', '+faststart', $output,
        ]);
    } else {
        $command = array_merge($command, ['-vn', '-c:a', 'libmp3lame', '-b:a', '192k', $output]);
    }

    $process = new \Symfony\Component\Process\Process($command);
    $process->setTimeout(900);
    $process->run();
    if (!$process->isSuccessful() || !is_file($output) || filesize($output) === 0) {
        @unlink($output);
        report(new \RuntimeException('FFmpeg conversion failed: ' . $process->getErrorOutput()));
        return response()->json(['message' => 'Converteren is mislukt. Probeer een kleiner bestand of een lagere kwaliteit.'], 500);
    }

    $downloadName = pathinfo($path, PATHINFO_FILENAME) . '-' . ($format === 'mp4' ? $quality . 'p' : 'audio') . '.' . $format;
    return response()->download($output, $downloadName)->deleteFileAfterSend(true);
})->middleware('throttle:5,1')->name('youtube-downloader.convert');
