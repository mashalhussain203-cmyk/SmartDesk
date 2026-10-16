<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VideoPublishController extends Controller
{
    private const API = 'https://zernio.com/api/v1';

    public function accounts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'api_key' => ['required', 'string', 'regex:/^sk_[a-fA-F0-9]{64}$/'],
        ]);

        try {
            $response = Http::acceptJson()->withToken($data['api_key'])
                ->timeout(20)->get(self::API.'/accounts');

            if (! $response->successful()) {
                return response()->json(['message' => __('Zernio did not accept your API key or could not list accounts.')], 422);
            }

            $accounts = collect($response->json('accounts', []))
                ->filter(fn ($account) => is_array($account)
                    && ($account['platform'] ?? '') === 'tiktok'
                    && ($account['isActive'] ?? true) !== false
                    && is_string($account['_id'] ?? null))
                ->map(fn ($account) => [
                    'id' => $account['_id'],
                    'name' => (string) ($account['username'] ?? $account['displayName'] ?? 'TikTok'),
                ])
                ->values()
                ->all();

            return response()->json(['accounts' => $accounts])->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => __('Zernio is temporarily unavailable. Try again later.')], 503);
        }
    }

    public function publish(Request $request): JsonResponse
    {
        $data = $request->validate([
            'api_key' => ['required', 'string', 'regex:/^sk_[a-fA-F0-9]{64}$/'],
            'token' => ['required', 'regex:/^[a-f0-9]{40}$/'],
            'account_id' => ['required', 'string', 'regex:/^[a-f0-9]{24}$/i'],
            'caption' => ['nullable', 'string', 'max:2200'],
            'ai_generated' => ['sometimes', 'boolean'],
            'consent' => ['required', 'accepted'],
        ]);

        $token = $data['token'];
        $entry = $request->session()->get('video_optimizer.'.$token);
        $path = storage_path('app/private/video-optimizer/'.$token.'.mp4');
        if (! is_array($entry) || ($entry['expires_at'] ?? 0) < time()
            || ($entry['path'] ?? '') !== $path || ! is_file($path)) {
            return response()->json(['message' => __('Please optimize your video again before posting.')], 422);
        }

        $duration = (float) ($entry['duration'] ?? 0);
        if ($duration < 3 || $duration > 600) {
            return response()->json(['message' => __('TikTok API posts must be between 3 seconds and 10 minutes.')], 422);
        }

        try {
            $client = Http::acceptJson()->withToken($data['api_key'])->timeout(30);
            $accountId = $data['account_id'];

            $info = $client->get(self::API.'/accounts/'.$accountId.'/tiktok/creator-info', ['mediaType' => 'video']);
            if (! $info->successful()) {
                return response()->json(['message' => __('TikTok creator permissions could not be verified in Zernio.')], 422);
            }

            $privacyLevels = collect($info->json('privacyLevels', []))->pluck('value');
            if (! $privacyLevels->contains('PUBLIC_TO_EVERYONE')) {
                return response()->json(['message' => __('Public publishing is not available for this TikTok account.')], 422);
            }

            $presign = $client->post(self::API.'/media/presign', [
                'filename' => 'mashal-optimized-'.$token.'.mp4',
                'contentType' => 'video/mp4',
                'size' => filesize($path),
            ]);
            if (! $presign->successful()) {
                return response()->json(['message' => __('Zernio could not prepare a video upload.')], 422);
            }

            $uploadUrl = (string) $presign->json('uploadUrl', '');
            $publicUrl = (string) $presign->json('publicUrl', '');
            $host = strtolower((string) parse_url($uploadUrl, PHP_URL_HOST));
            if (parse_url($uploadUrl, PHP_URL_SCHEME) !== 'https'
                || ! str_ends_with($host, '.r2.cloudflarestorage.com')
                || ! str_starts_with($publicUrl, 'https://media.zernio.com/')) {
                return response()->json(['message' => __('Zernio returned an unsupported secure upload URL.')], 502);
            }

            $stream = fopen($path, 'rb');
            if ($stream === false) {
                return response()->json(['message' => __('Unable to read the optimized video.')], 503);
            }

            try {
                $upload = Http::withOptions(['allow_redirects' => false])
                    ->timeout(180)
                    ->withHeaders(['Content-Length' => (string) filesize($path)])
                    ->withBody($stream, 'video/mp4')
                    ->put($uploadUrl);
            } finally {
                fclose($stream);
            }

            if (! $upload->successful()) {
                return response()->json(['message' => __('Zernio video storage rejected the upload. Try again.')], 422);
            }

            $post = $client->post(self::API.'/posts', [
                'content' => (string) ($data['caption'] ?? ''),
                'mediaItems' => [['type' => 'video', 'url' => $publicUrl]],
                'platforms' => [['platform' => 'tiktok', 'accountId' => $accountId]],
                'tiktokSettings' => [
                    'privacy_level' => 'PUBLIC_TO_EVERYONE',
                    'allow_comment' => false,
                    'allow_duet' => false,
                    'allow_stitch' => false,
                    'video_made_with_ai' => $request->boolean('ai_generated'),
                    'content_preview_confirmed' => true,
                    'express_consent_given' => true,
                ],
                'publishNow' => true,
            ]);

            if (! $post->successful()) {
                $error = $post->json('error');
                return response()->json([
                    'message' => is_string($error) ? mb_substr($error, 0, 350) : __('TikTok publishing was rejected by Zernio.'),
                ], 422);
            }

            return response()->json([
                'message' => __('Zernio accepted the publishing request. TikTok may still be processing the video.'),
                'post_id' => (string) $post->json('post._id', ''),
                'status' => (string) $post->json('post.status', 'processing'),
            ])->header('Cache-Control', 'no-store');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => __('The publishing service is temporarily unavailable.')], 503);
        }
    }
}
