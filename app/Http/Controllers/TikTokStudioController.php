<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\Process\Process;
use Throwable;

class TikTokStudioController extends Controller
{
    private const API = 'https://open.tiktokapis.com';
    private const MAX_VIDEO_BYTES = 52428800;

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('tools.tiktok-upload-studio', [
            'connected' => $user !== null && filled($user->tiktok_post_refresh_token)
                && in_array('video.upload', explode(',', (string) $user->tiktok_post_scopes), true),
            'configured' => filled(config('services.tiktok.client_id'))
                && filled(config('services.tiktok.client_secret')),
            'lastUpload' => $request->session()->get('tiktok_studio.last'),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $clientKey = (string) config('services.tiktok.client_id');
        if ($clientKey === '' || ! config('services.tiktok.client_secret')) {
            return back()->withErrors(['tiktok' => 'TikTok is nog niet geconfigureerd op de server.']);
        }

        $state = Str::random(48);
        $request->session()->put('tiktok_studio.oauth', [
            'state' => $state,
            'user_id' => $request->user()->getAuthIdentifier(),
            'started_at' => time(),
        ]);

        return redirect()->away('https://www.tiktok.com/v2/auth/authorize/?'.http_build_query([
            'client_key' => $clientKey,
            'response_type' => 'code',
            'scope' => 'video.upload',
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
        ]));
    }

    public function callback(Request $request): RedirectResponse
    {
        $oauth = $request->session()->pull('tiktok_studio.oauth');
        $state = $request->query('state');

        if (! is_array($oauth) || ! is_string($state)
            || ! hash_equals((string) ($oauth['state'] ?? ''), $state)
            || (string) ($oauth['user_id'] ?? '') !== (string) $request->user()->getAuthIdentifier()
            || time() - (int) ($oauth['started_at'] ?? 0) > 900) {
            return redirect()->route('tiktok-studio.index')
                ->withErrors(['tiktok' => 'TikTok-aanmelding verlopen of ongeldig. Probeer opnieuw.']);
        }

        if ($request->filled('error')) {
            return redirect()->route('tiktok-studio.index')
                ->withErrors(['tiktok' => 'TikTok-toestemming is niet gegeven.']);
        }

        $code = $request->query('code');
        if (! is_string($code) || $code === '') {
            return redirect()->route('tiktok-studio.index')
                ->withErrors(['tiktok' => 'TikTok heeft geen autorisatiecode teruggestuurd.']);
        }

        try {
            $response = Http::asForm()->acceptJson()->timeout(20)->post(self::API.'/v2/oauth/token/', [
                'client_key' => config('services.tiktok.client_id'),
                'client_secret' => config('services.tiktok.client_secret'),
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri(),
            ]);

            $payload = $response->json();
            if (! $response->successful() || ! is_array($payload)
                || ! is_string($payload['access_token'] ?? null)
                || ! is_string($payload['refresh_token'] ?? null)) {
                return redirect()->route('tiktok-studio.index')
                    ->withErrors(['tiktok' => 'TikTok kon de toestemming niet bevestigen. Controleer de app-scopes en callback-URL.']);
            }

            $scopes = explode(',', (string) ($payload['scope'] ?? ''));
            if (! in_array('video.upload', $scopes, true)) {
                return redirect()->route('tiktok-studio.index')
                    ->withErrors(['tiktok' => 'Geef TikTok toestemming voor video.upload om concepten te versturen.']);
            }

            $request->user()->forceFill([
                'tiktok_post_access_token' => $payload['access_token'],
                'tiktok_post_refresh_token' => $payload['refresh_token'],
                'tiktok_post_open_id' => (string) ($payload['open_id'] ?? ''),
                'tiktok_post_scopes' => (string) ($payload['scope'] ?? ''),
                'tiktok_post_expires_at' => now()->addSeconds(max(0, (int) ($payload['expires_in'] ?? 86400) - 60)),
                'tiktok_post_connected_at' => now(),
            ])->save();

            return redirect()->route('tiktok-studio.index')
                ->with('success', 'TikTok is gekoppeld voor het versturen van videoconcepten.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('tiktok-studio.index')
                ->withErrors(['tiktok' => 'TikTok is tijdelijk niet bereikbaar. Probeer opnieuw.']);
        }
    }

    /**
     * Use the registered TikTok Login Kit redirect URL shared with regular
     * TikTok sign-in. The auth callback distinguishes Studio OAuth by state.
     */
    private function redirectUri(): string
    {
        return (string) (config('services.tiktok.redirect') ?: route('tiktok.callback'));
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();
        $accessToken = (string) $user->tiktok_post_access_token;

        if ($accessToken !== '') {
            try {
                Http::asForm()->timeout(10)->post(self::API.'/v2/oauth/revoke/', [
                    'client_key' => config('services.tiktok.client_id'),
                    'client_secret' => config('services.tiktok.client_secret'),
                    'token' => $accessToken,
                ]);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        $user->forceFill([
            'tiktok_post_access_token' => null,
            'tiktok_post_refresh_token' => null,
            'tiktok_post_open_id' => null,
            'tiktok_post_scopes' => null,
            'tiktok_post_expires_at' => null,
            'tiktok_post_connected_at' => null,
        ])->save();
        $request->session()->forget(['tiktok_studio.last', 'tiktok_studio.oauth']);

        return redirect()->route('tiktok-studio.index')
            ->with('success', 'TikTok is losgekoppeld van de Upload Studio.');
    }

    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,mov,webm', 'max:51200'],
            'audio' => ['nullable', 'file', 'mimes:mp3,wav,m4a,aac,ogg,mp4', 'max:15360'],
            'caption' => ['nullable', 'string', 'max:2200'],
            'hashtags' => ['nullable', 'string', 'max:600'],
            'music_start' => ['nullable', 'numeric', 'min:0', 'max:3600'],
            'music_volume' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'original_volume' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'rights_confirmed' => ['required', 'accepted'],
            'consent' => ['required', 'accepted'],
        ]);

        $user = $request->user();
        if (! in_array('video.upload', explode(',', (string) $user->tiktok_post_scopes), true)
            || ! filled($user->tiktok_post_refresh_token)) {
            return response()->json(['message' => 'Koppel eerst TikTok met toestemming voor video.upload.'], 403);
        }

        $paths = [];
        try {
            $token = $this->accessToken($request);
            if ($token === null) {
                return response()->json(['message' => 'TikTok-toegang verlopen. Koppel TikTok opnieuw.'], 401);
            }

            Storage::disk('local')->makeDirectory('tiktok-studio');
            $filename = (string) Str::uuid();
            $videoPath = $request->file('video')->storeAs('tiktok-studio', $filename.'.'.$request->file('video')->extension(), 'local');
            if (! is_string($videoPath)) {
                return response()->json(['message' => 'Videobestand kon niet worden opgeslagen.'], 503);
            }
            $paths[] = $videoPath;
            $uploadPath = Storage::disk('local')->path($videoPath);
            $mime = match (strtolower(pathinfo($uploadPath, PATHINFO_EXTENSION))) {
                'mov' => 'video/quicktime',
                'webm' => 'video/webm',
                default => 'video/mp4',
            };

            if ($request->hasFile('audio')) {
                $audioPath = $request->file('audio')->storeAs('tiktok-studio', $filename.'-music.'.$request->file('audio')->extension(), 'local');
                if (! is_string($audioPath)) {
                    return response()->json(['message' => 'Muziekbestand kon niet worden opgeslagen.'], 503);
                }
                $paths[] = $audioPath;
                $mixedPath = 'tiktok-studio/'.$filename.'-mixed.mp4';
                $paths[] = $mixedPath;
                $mixError = $this->mixAudio(
                    $uploadPath,
                    Storage::disk('local')->path($audioPath),
                    Storage::disk('local')->path($mixedPath),
                    (float) ($data['music_start'] ?? 0),
                    (float) ($data['music_volume'] ?? 0.8),
                    (float) ($data['original_volume'] ?? 0.4)
                );

                if ($mixError !== null) {
                    return response()->json(['message' => $mixError], 422);
                }

                $uploadPath = Storage::disk('local')->path($mixedPath);
                $mime = 'video/mp4';
            }

            $bytes = filesize($uploadPath);
            if (! is_int($bytes) || $bytes < 1 || $bytes > self::MAX_VIDEO_BYTES) {
                return response()->json(['message' => 'Het uiteindelijke videobestand moet kleiner zijn dan 50 MB.'], 422);
            }

            $initial = Http::acceptJson()->withToken($token)->timeout(30)
                ->post(self::API.'/v2/post/publish/inbox/video/init/', [
                    'source_info' => [
                        'source' => 'FILE_UPLOAD',
                        'video_size' => $bytes,
                        'chunk_size' => $bytes,
                        'total_chunk_count' => 1,
                    ],
                ]);

            $publishId = (string) $initial->json('data.publish_id', '');
            $uploadUrl = (string) $initial->json('data.upload_url', '');
            if (! $initial->successful() || $initial->json('error.code') !== 'ok'
                || $publishId === '' || $uploadUrl === '') {
                $code = (string) $initial->json('error.code', '');
                return response()->json([
                    'message' => 'TikTok heeft de upload niet geaccepteerd. '.($code !== '' ? 'Code: '.$code : 'Controleer de videorechten en API-goedkeuring.'),
                ], 422);
            }

            $host = strtolower((string) parse_url($uploadUrl, PHP_URL_HOST));
            if (parse_url($uploadUrl, PHP_URL_SCHEME) !== 'https'
                || ! str_ends_with($host, '.tiktokapis.com')
                || parse_url($uploadUrl, PHP_URL_USER) !== null
                || parse_url($uploadUrl, PHP_URL_PASS) !== null) {
                return response()->json(['message' => 'TikTok gaf een ongeldige beveiligde upload-URL.'], 502);
            }

            $stream = fopen($uploadPath, 'rb');
            if ($stream === false) {
                return response()->json(['message' => 'Video kan niet gelezen worden.'], 503);
            }

            try {
                $sent = Http::withOptions(['allow_redirects' => false])
                    ->withHeaders([
                        'Content-Range' => 'bytes 0-'.($bytes - 1).'/'.$bytes,
                        'Content-Length' => (string) $bytes,
                    ])
                    ->withBody($stream, $mime)
                    ->timeout(180)
                    ->put($uploadUrl);
            } finally {
                fclose($stream);
            }

            if ($sent->status() !== 201) {
                return response()->json(['message' => 'TikTok heeft het videobestand niet voltooid. Probeer het opnieuw.'], 502);
            }

            $hashtags = trim((string) ($data['hashtags'] ?? ''));
            $caption = trim((string) ($data['caption'] ?? ''));
            $request->session()->put('tiktok_studio.last', [
                'publish_id' => $publishId,
                'caption' => $caption,
                'hashtags' => $hashtags,
                'sent_at' => now()->toIso8601String(),
            ]);

            return response()->json([
                'message' => 'Video naar TikTok verstuurd. Open de melding in je TikTok-inbox om muziek toe te voegen, bijschrift in te vullen en te publiceren.',
                'publish_id' => $publishId,
                'caption' => trim($caption.' '.$hashtags),
                'status' => 'PROCESSING_UPLOAD',
            ])->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Video kon niet naar TikTok worden verzonden. Probeer opnieuw.'], 503);
        } finally {
            Storage::disk('local')->delete($paths);
        }
    }

    public function status(Request $request): JsonResponse
    {
        $last = $request->session()->get('tiktok_studio.last');
        if (! is_array($last) || empty($last['publish_id'])) {
            return response()->json(['message' => 'Geen recente TikTok-upload gevonden.'], 404);
        }

        try {
            $token = $this->accessToken($request);
            if ($token === null) {
                return response()->json(['message' => 'TikTok-sessie verlopen. Koppel opnieuw.'], 401);
            }

            $response = Http::acceptJson()->withToken($token)->timeout(20)
                ->post(self::API.'/v2/post/publish/status/fetch/', [
                    'publish_id' => $last['publish_id'],
                ]);

            if (! $response->successful() || $response->json('error.code') !== 'ok') {
                return response()->json(['message' => 'TikTok kon de status niet ophalen.'], 422);
            }

            return response()->json([
                'status' => (string) $response->json('data.status', 'UNKNOWN'),
                'fail_reason' => (string) $response->json('data.fail_reason', ''),
                'published_ids' => $response->json('data.publicaly_available_post_id', []),
            ])->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Status tijdelijk niet beschikbaar.'], 503);
        }
    }

    private function accessToken(Request $request): ?string
    {
        $user = $request->user();
        if (! filled($user->tiktok_post_refresh_token)) {
            return null;
        }

        if (filled($user->tiktok_post_access_token)
            && $user->tiktok_post_expires_at?->isFuture()) {
            return (string) $user->tiktok_post_access_token;
        }

        $result = Http::asForm()->acceptJson()->timeout(20)->post(self::API.'/v2/oauth/token/', [
            'client_key' => config('services.tiktok.client_id'),
            'client_secret' => config('services.tiktok.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->tiktok_post_refresh_token,
        ]);

        if (! $result->successful()
            || ! is_string($result->json('access_token'))
            || ! is_string($result->json('refresh_token'))
            || ! in_array('video.upload', explode(',', (string) $result->json('scope', '')), true)) {
            return null;
        }

        $user->forceFill([
            'tiktok_post_access_token' => $result->json('access_token'),
            'tiktok_post_refresh_token' => $result->json('refresh_token'),
            'tiktok_post_scopes' => $result->json('scope'),
            'tiktok_post_expires_at' => now()->addSeconds(max(0, (int) $result->json('expires_in', 86400) - 60)),
        ])->save();

        return (string) $result->json('access_token');
    }

    private function mixAudio(
        string $video,
        string $audio,
        string $output,
        float $musicStart,
        float $musicVolume,
        float $originalVolume
    ): ?string {
        try {
            $probe = new Process([
                'ffprobe', '-v', 'error', '-select_streams', 'a:0',
                '-show_entries', 'stream=index', '-of', 'csv=p=0', $video,
            ]);
            $probe->setTimeout(20);
            $probe->run();

            if (! $probe->isSuccessful()) {
                return 'De video kon niet worden gecontroleerd. Controleer het bestandsformaat.';
            }

            $originalHasAudio = trim($probe->getOutput()) !== '';
            $musicFilter = '[1:a:0]atrim=start='.$musicStart.',asetpts=PTS-STARTPTS,volume='.$musicVolume.',apad[music]';
            $filters = $originalHasAudio
                ? '[0:a:0]volume='.$originalVolume.'[original];'.$musicFilter.';[original][music]amix=inputs=2:duration=first:dropout_transition=0[a]'
                : $musicFilter.';[music]anull[a]';

            $process = new Process([
                'ffmpeg', '-y', '-nostdin', '-hide_banner', '-loglevel', 'error',
                '-i', $video, '-i', $audio,
                '-filter_complex', $filters,
                '-map', '0:v:0', '-map', '[a]',
                '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '21', '-pix_fmt', 'yuv420p',
                '-c:a', 'aac', '-b:a', '192k', '-movflags', '+faststart',
                '-shortest', '-t', '600', $output,
            ]);
            $process->setTimeout(150);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($output) || filesize($output) < 1) {
                return 'Muziek samenvoegen is niet gelukt. Probeer MP3 of WAV, of upload zonder extra audio.';
            }

            return null;
        } catch (Throwable $exception) {
            report($exception);

            return 'Audio bewerken is niet beschikbaar op deze server. Controleer FFmpeg.';
        }
    }
}
