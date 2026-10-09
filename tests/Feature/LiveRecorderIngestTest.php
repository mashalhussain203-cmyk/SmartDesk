<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LiveRecorderIngestTest extends TestCase
{
    private const TOKEN = 'this-is-a-test-recorder-secret-with-48-chars';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config()->set('live_recorder.secret', self::TOKEN);
    }

    public function test_ingest_requires_a_long_matching_bearer_secret(): void
    {
        $this->postJson('/api/internal/live-recordings/knock1knock/status', [
            'status' => 'recording',
        ])->assertForbidden();

        $this->withToken('wrong-token')->postJson('/api/internal/live-recordings/knock1knock/status', [
            'status' => 'recording',
        ])->assertForbidden();

        config()->set('live_recorder.secret', '');
        $this->withToken(self::TOKEN)->postJson('/api/internal/live-recordings/knock1knock/status', [
            'status' => 'recording',
        ])->assertForbidden();
    }

    public function test_status_is_private_and_not_automatically_marked_offline(): void
    {
        $this->withToken(self::TOKEN)->postJson('/api/internal/live-recordings/emyii/status', [
            'status' => 'recording',
            'message' => 'Livestream wordt opgenomen',
        ])->assertOk();

        $this->assertTrue(Storage::disk('local')->exists('live-recordings/emyii/status.json'));
        $this->get('/live/status')->assertRedirect('/login');

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->get('/live/status')
            ->assertOk()
            ->assertJsonPath('emyii.status', 'recording');
    }

    public function test_recorder_can_upload_a_video_in_ordered_private_chunks(): void
    {
        $base = '/api/internal/live-recordings/knock1knock';
        $begin = $this->withToken(self::TOKEN)->postJson($base.'/uploads', [
            'filename' => '2026-10-09_knock1knock.mp4',
        ])->assertCreated();
        $upload = $begin->json('id');
        $this->assertNotEmpty($upload);

        $response = $this->withHeader('Authorization', 'Bearer '.self::TOKEN)->call(
            'PUT', $base.'/uploads/'.$upload.'/parts/0', [], [], [],
            ['CONTENT_TYPE' => 'application/octet-stream', 'HTTP_AUTHORIZATION' => 'Bearer '.self::TOKEN],
            'first-video-data'
        );
        $response->assertOk();

        $this->postJson($base.'/uploads/'.$upload.'/complete', ['parts' => 2, 'bytes' => 31])
            ->assertStatus(409);

        $this->withHeader('Authorization', 'Bearer '.self::TOKEN)->call(
            'PUT', $base.'/uploads/'.$upload.'/parts/1', [], [], [],
            ['CONTENT_TYPE' => 'application/octet-stream', 'HTTP_AUTHORIZATION' => 'Bearer '.self::TOKEN],
            'second-video-data'
        )->assertOk();

        $this->withToken(self::TOKEN)->postJson($base.'/uploads/'.$upload.'/complete', [
            'parts' => 2, 'bytes' => strlen('first-video-datasecond-video-data'),
        ])->assertOk();

        $path = 'live-recordings/knock1knock/2026-10-09_knock1knock.mp4';
        $this->assertSame('first-video-datasecond-video-data', Storage::disk('local')->get($path));
        $this->assertFalse(Storage::disk('local')->exists('live-recording-uploads/knock1knock/'.$upload.'/meta.json'));

        $viewer = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        $this->actingAs($viewer)->get('/live/knock1knock/2026-10-09_knock1knock.mp4/download')
            ->assertForbidden();

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->get('/live/knock1knock/2026-10-09_knock1knock.mp4/download')
            ->assertOk();
    }
}
