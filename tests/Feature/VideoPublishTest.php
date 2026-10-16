<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VideoPublishTest extends TestCase
{
    public function test_publishing_requires_explicit_consent(): void
    {
        $this->postJson('/tools/video-optimizer/zernio/publish', [
            'api_key' => 'sk_'.str_repeat('a', 64),
            'token' => str_repeat('b', 40),
            'account_id' => str_repeat('c', 24),
            'consent' => false,
        ])->assertUnprocessable()->assertJsonValidationErrors(['consent']);
    }

    public function test_zernio_partial_failure_is_not_reported_as_success_and_has_idempotency_key(): void
    {
        $token = str_repeat('b', 40);
        $path = storage_path('app/private/video-optimizer/'.$token.'.mp4');
        File::ensureDirectoryExists(dirname($path));
        file_put_contents($path, 'fake-private-video-for-mocked-api');

        Http::preventStrayRequests();
        Http::fake([
            'https://zernio.com/api/v1/accounts/*/tiktok/creator-info*' => Http::response([
                'privacyLevels' => [['value' => 'PUBLIC_TO_EVERYONE']],
            ], 200),
            'https://zernio.com/api/v1/media/presign' => Http::response([
                'uploadUrl' => 'https://bucket.r2.cloudflarestorage.com/temp/test.mp4?X-Amz-Signature=dummy',
                'publicUrl' => 'https://media.zernio.com/temp/test.mp4',
            ], 200),
            'https://bucket.r2.cloudflarestorage.com/*' => Http::response('', 200),
            'https://zernio.com/api/v1/posts' => Http::response([
                'post' => [
                    '_id' => str_repeat('d', 24),
                    'status' => 'failed',
                    'platforms' => [[
                        'platform' => 'tiktok',
                        'status' => 'failed',
                        'errorMessage' => 'TikTok rejected the video.',
                    ]],
                ],
            ], 207),
        ]);

        try {
            $this->withSession([
                'video_optimizer' => [
                    $token => [
                        'path' => $path,
                        'expires_at' => time() + 3600,
                        'duration' => 30,
                    ],
                ],
            ])->postJson('/tools/video-optimizer/zernio/publish', [
                'api_key' => 'sk_'.str_repeat('a', 64),
                'token' => $token,
                'account_id' => str_repeat('c', 24),
                'caption' => 'My clip',
                'consent' => true,
            ])->assertUnprocessable()
                ->assertJsonPath('status', 'failed')
                ->assertJsonPath('message', 'TikTok rejected the video.');

            Http::assertSent(function ($request) {
                return str_ends_with($request->url(), '/api/v1/posts')
                    && $request->hasHeader('Idempotency-Key');
            });
        } finally {
            @unlink($path);
        }
    }
}
