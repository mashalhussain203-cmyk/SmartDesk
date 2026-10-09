<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class LiveRecordingsTest extends TestCase
{
    public function test_guests_cannot_open_private_live_library(): void
    {
        $this->get('/live')->assertRedirect('/login');
        $this->get('/live/status')->assertRedirect('/login');
    }

    public function test_regular_users_cannot_access_videos_or_status(): void
    {
        $user = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        Storage::fake('local');
        Storage::disk('local')->put('live-recordings/knock1knock/test.mp4', 'test');

        $this->actingAs($user)->get('/live')->assertForbidden();
        $this->actingAs($user)->get('/live/status')->assertForbidden();
        $this->actingAs($user)->get('/live/knock1knock/test.mp4/watch')->assertForbidden();
        $this->actingAs($user)->get('/live/knock1knock/test.mp4/download')->assertForbidden();
        $this->actingAs($user)->delete('/live/knock1knock/test.mp4')->assertForbidden();
        $this->assertTrue(Storage::disk('local')->exists('live-recordings/knock1knock/test.mp4'));
    }

    public function test_admin_can_open_library_and_only_existing_files(): void
    {
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        Storage::fake('local');
        Storage::disk('local')->put('live-recordings/knock1knock/test.mp4', 'test');

        $this->actingAs($admin)->get('/live')->assertOk()->assertSee('Privé livestreamopnames');
        $this->actingAs($admin)->get('/live/status')->assertOk()->assertJsonPath('knock1knock.status', 'unknown');
        $this->actingAs($admin)->get('/live/knock1knock/test.mp4/download')->assertOk();
        $this->actingAs($admin)->get('/live/knock1knock/missing.mp4/download')->assertNotFound();
        $this->actingAs($admin)->get('/live/not-an-account/test.mp4/download')->assertNotFound();
    }

    public function test_lucycums_recordings_can_be_played_downloaded_and_deleted_by_admin(): void
    {
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        Storage::fake('local');
        Storage::disk('local')->put('live-recordings/lucycums/clip.mp4', 'test');

        $this->actingAs($admin)->get('/live')->assertOk()->assertSee('@lucycums')->assertSee('Neem 30 seconden op en sla privé op');
        $this->actingAs($admin)->get('/live/lucycums/clip.mp4/watch')->assertOk();
        $this->actingAs($admin)->get('/live/lucycums/clip.mp4/download')->assertOk();
        $this->actingAs($admin)->delete('/live/lucycums/clip.mp4')->assertRedirect('/live');
        $this->assertFalse(Storage::disk('local')->exists('live-recordings/lucycums/clip.mp4'));
    }

    public function test_only_admin_can_begin_a_manual_private_upload(): void
    {
        Storage::fake('local');
        $this->post('/live/uploads', ['account' => 'lucycums', 'bytes' => 2048])
            ->assertRedirect('/login');

        $viewer = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        $this->actingAs($viewer)->postJson('/live/uploads', ['account' => 'lucycums', 'bytes' => 2048])
            ->assertForbidden();

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->postJson('/live/uploads', ['account' => 'not-an-account', 'bytes' => 2048])
            ->assertUnprocessable();
        $this->actingAs($admin)->postJson('/live/uploads', ['account' => 'lucycums', 'bytes' => 300 * 1024 * 1024])
            ->assertUnprocessable();
    }

    public function test_admin_can_upload_an_mp4_in_private_chunks_without_multipart_limit(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin);
        $bytes = "\0\0\0\x18ftypisom".str_repeat('v', 2 * 1024 * 1024 + 1500);
        $begin = $this->postJson('/live/uploads', [
            'account' => 'lucycums', 'bytes' => strlen($bytes),
        ])->assertCreated();
        $id = $begin->json('id');
        $this->assertSame(2, $begin->json('parts'));

        $this->call('PUT', '/live/uploads/'.$id.'/parts/0', [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
        ], substr($bytes, 0, 2 * 1024 * 1024))->assertOk();
        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertStatus(409);
        $this->call('PUT', '/live/uploads/'.$id.'/parts/1', [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
        ], substr($bytes, 2 * 1024 * 1024))->assertOk();

        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertOk();
        $files = Storage::disk('local')->files('live-recordings/lucycums');
        $this->assertCount(1, $files);
        $this->assertSame($bytes, Storage::disk('local')->get($files[0]));
        $this->assertFalse(Storage::disk('local')->exists('live-recording-uploads/manual/'.$id.'/meta.json'));
        $this->get('/live/lucycums/'.basename($files[0]).'/download')->assertOk();
    }

    public function test_incomplete_or_non_mp4_upload_is_not_published(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin);
        $bytes = str_repeat('x', 2048);
        $begin = $this->postJson('/live/uploads', [
            'account' => 'lucycums', 'bytes' => strlen($bytes),
        ])->assertCreated();
        $id = $begin->json('id');

        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertStatus(409);
        $this->call('PUT', '/live/uploads/'.$id.'/parts/0', [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
        ], $bytes)->assertOk();
        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertUnprocessable();
        $this->assertSame([], Storage::disk('local')->files('live-recordings/lucycums'));
        $this->delete('/live/uploads/'.$id)->assertOk();
        $this->assertFalse(Storage::disk('local')->exists('live-recording-uploads/manual/'.$id.'/meta.json'));
    }

    public function test_invalid_webm_capture_is_rejected_before_ffmpeg_and_never_published(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin);
        $bytes = str_repeat('x', 2048);
        $id = $this->postJson('/live/uploads', [
            'account' => 'lucycums', 'bytes' => strlen($bytes), 'format' => 'webm',
        ])->assertCreated()->json('id');

        $this->call('PUT', '/live/uploads/'.$id.'/parts/0', [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
        ], $bytes)->assertOk();
        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertUnprocessable();
        $this->assertSame([], Storage::disk('local')->files('live-recordings/lucycums'));
    }

    public function test_browser_webm_with_audio_is_converted_to_private_mp4(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin);

        $source = tempnam(sys_get_temp_dir(), 'live-test-');
        try {
            $generated = Process::timeout(30)->run([
                'ffmpeg', '-hide_banner', '-loglevel', 'error', '-nostdin', '-y',
                '-f', 'lavfi', '-i', 'color=c=black:s=160x90:r=10',
                '-f', 'lavfi', '-i', 'sine=frequency=440:sample_rate=48000',
                '-t', '1', '-c:v', 'libvpx', '-b:v', '150k',
                '-c:a', 'libopus', '-shortest', '-f', 'webm', $source,
            ]);
            $this->assertTrue($generated->successful(), $generated->errorOutput());
            $bytes = file_get_contents($source);
            $this->assertStringStartsWith("\x1A\x45\xDF\xA3", $bytes);

            $id = $this->postJson('/live/uploads', [
                'account' => 'lucycums', 'bytes' => strlen($bytes), 'format' => 'webm',
            ])->assertCreated()->json('id');
            $this->call('PUT', '/live/uploads/'.$id.'/parts/0', [], [], [], [
                'CONTENT_TYPE' => 'application/octet-stream',
            ], $bytes)->assertOk();
            $this->postJson('/live/uploads/'.$id.'/complete', [])->assertOk();

            $files = Storage::disk('local')->files('live-recordings/lucycums');
            $this->assertCount(1, $files);
            $this->assertSame('ftyp', substr(Storage::disk('local')->get($files[0]), 4, 4));
            $probe = Process::timeout(15)->run([
                'ffprobe', '-v', 'error', '-select_streams', 'a:0',
                '-show_entries', 'stream=codec_name', '-of', 'default=noprint_wrappers=1',
                Storage::disk('local')->path($files[0]),
            ]);
            $this->assertTrue($probe->successful(), $probe->errorOutput());
            $this->assertStringContainsString('codec_name=aac', $probe->output());
            $this->assertFalse(Storage::disk('local')->exists('live-recording-uploads/manual/'.$id.'/meta.json'));
        } finally {
            @unlink($source);
        }
    }

    public function test_one_admin_cannot_modify_another_admin_upload(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'First Admin', 'email' => 'first@example.test', 'is_admin' => true]);
        $other = new User(['name' => 'Second Admin', 'email' => 'second@example.test', 'is_admin' => true]);
        $id = $this->actingAs($admin)->postJson('/live/uploads', [
            'account' => 'lucycums', 'bytes' => 2048,
        ])->assertCreated()->json('id');

        $this->actingAs($other)->delete('/live/uploads/'.$id)->assertNotFound();
        $this->actingAs($other)->postJson('/live/uploads/'.$id.'/complete', [])->assertNotFound();
        $this->assertTrue(Storage::disk('local')->exists('live-recording-uploads/manual/'.$id.'/meta.json'));
    }

    public function test_admin_can_delete_completed_recordings(): void
    {
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        Storage::fake('local');
        Storage::disk('local')->put('live-recordings/emyii/sample.mp4', 'test');

        $this->actingAs($admin)->delete('/live/emyii/sample.mp4')->assertRedirect('/live');
        $this->assertFalse(Storage::disk('local')->exists('live-recordings/emyii/sample.mp4'));
    }
}
