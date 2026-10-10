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

    public function test_removed_accounts_are_absent_from_the_library_and_not_accessible(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin);
        $page = $this->get('/live')->assertOk();
        $statuses = $this->get('/live/status')->assertOk()->json();

        foreach (['mon1_day', 'lucycums', 'leo_kitty', '_frankie_rivers', 'gimbobar'] as $account) {
            Storage::disk('local')->put('live-recordings/'.$account.'/old.mp4', 'old');
            $page->assertDontSee('@'.$account);
            $this->assertArrayNotHasKey($account, $statuses);
            $this->get('/live/'.$account.'/old.mp4/download')->assertNotFound();
            $this->postJson('/live/uploads', ['account' => $account, 'bytes' => 2048])
                ->assertUnprocessable();
            $this->assertTrue(Storage::disk('local')->exists('live-recordings/'.$account.'/old.mp4'));
        }
    }


    public function test_dellris_obs_recordings_are_private_and_visible_in_live_library(): void
    {
        Storage::fake('local');
        $filename = '2026-10-10T16-00-00-000Z_dellris_obs.mp4';
        Storage::disk('local')->put('live-recordings/dellris/'.$filename, 'test');
        $watch = '/live/dellris/'.$filename.'/watch';
        $download = '/live/dellris/'.$filename.'/download';

        $this->get($watch)->assertRedirect('/login');
        $viewer = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        $this->actingAs($viewer)->get($watch)->assertForbidden();

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->get('/live')->assertOk()->assertSee('@dellris')->assertDontSee('PROFIELLINK');
        $this->actingAs($admin)->get('/live/status')->assertOk()
            ->assertJsonPath('dellris.status', 'needs_setup')
            ->assertJsonPath('julesxdann.status', 'unknown');

        // A real, recent heartbeat is required before reporting OFFLINE.
        Storage::disk('local')->put('live-recordings/dellris/status.json', json_encode([
            'status' => 'offline', 'message' => 'OBS heeft momenteel geen actieve uitzending',
            'checked_at' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR));
        $this->actingAs($admin)->get('/live/status')->assertOk()
            ->assertJsonPath('dellris.status', 'offline');

        Storage::disk('local')->put('live-recordings/dellris/status.json', json_encode([
            'status' => 'offline', 'message' => 'Oude melding',
            'checked_at' => now()->subMinutes(10)->toIso8601String(),
        ], JSON_THROW_ON_ERROR));
        $this->actingAs($admin)->get('/live/status')->assertOk()
            ->assertJsonPath('dellris.status', 'needs_setup');
        $this->actingAs($admin)->get($watch)->assertOk();
        $this->actingAs($admin)->get($download)->assertOk();
        $this->actingAs($admin)->postJson('/live/uploads', ['account' => 'dellris', 'bytes' => 2048])->assertCreated();
    }

    public function test_julesxdann_has_an_admin_only_private_archive_and_status(): void
    {
        Storage::fake('local');
        $filename = '2026-10-10T15-00-00-000Z_julesxdann.mp4';
        Storage::disk('local')->put('live-recordings/julesxdann/'.$filename, 'test');

        $watch = '/live/julesxdann/'.$filename.'/watch';
        $download = '/live/julesxdann/'.$filename.'/download';

        $this->get($watch)->assertRedirect('/login');

        $viewer = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        $this->actingAs($viewer)->get($watch)->assertForbidden();
        $this->actingAs($viewer)->get($download)->assertForbidden();

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->get('/live')->assertOk()->assertSee('@julesxdann');
        $this->actingAs($admin)->get('/live/status')->assertOk()->assertJsonPath('julesxdann.status', 'unknown');
        $this->actingAs($admin)->get($watch)->assertOk();
        $this->actingAs($admin)->get($download)->assertOk();
        $this->actingAs($admin)->postJson('/live/uploads', ['account' => 'julesxdann', 'bytes' => 2048])->assertCreated();
        $this->actingAs($admin)->delete('/live/julesxdann/'.$filename)->assertRedirect('/live');
        $this->assertFalse(Storage::disk('local')->exists('live-recordings/julesxdann/'.$filename));
    }

    public function test_knock1knock_recordings_can_be_played_downloaded_and_deleted_by_admin(): void
    {
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        Storage::fake('local');
        Storage::disk('local')->put('live-recordings/knock1knock/clip.mp4', 'test');

        $this->actingAs($admin)->get('/live')->assertOk()->assertSee('@knock1knock')->assertSee('Neem 30 seconden op en sla privé op')->assertSee('Automatisch de volledige livestream opnemen')->assertSee('Opnameduur: geen limiet van 30 sec.');
        $this->actingAs($admin)->get('/live/knock1knock/clip.mp4/watch')->assertOk();
        $this->actingAs($admin)->get('/live/knock1knock/clip.mp4/download')->assertOk();
        $this->actingAs($admin)->delete('/live/knock1knock/clip.mp4')->assertRedirect('/live');
        $this->assertFalse(Storage::disk('local')->exists('live-recordings/knock1knock/clip.mp4'));
    }

    public function test_cutefacebigass_archive_is_playable_downloadable_and_admin_only(): void
    {
        Storage::fake('local');
        $filename = '2026-10-09T20-22-00-000Z_cutefacebigass.mp4';
        Storage::disk('local')->put('live-recordings/cutefacebigass/'.$filename, 'test');
        $watch = '/live/cutefacebigass/'.$filename.'/watch';
        $download = '/live/cutefacebigass/'.$filename.'/download';

        $this->get($watch)->assertRedirect('/login');
        $viewer = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        $this->actingAs($viewer)->get($watch)->assertForbidden();
        $this->actingAs($viewer)->get($download)->assertForbidden();
        $this->actingAs($viewer)->delete('/live/cutefacebigass/'.$filename)->assertForbidden();

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->get('/live')->assertOk()->assertSee('@cutefacebigass');
        $this->actingAs($admin)->get($watch)->assertOk();
        $this->actingAs($admin)->get($download)->assertOk();
        $this->actingAs($admin)->delete('/live/cutefacebigass/'.$filename)->assertRedirect('/live');
        $this->assertFalse(Storage::disk('local')->exists('live-recordings/cutefacebigass/'.$filename));
    }

    public function test_ricasashaa_records_are_private_and_visible_in_live_library(): void
    {
        Storage::fake('local');
        $filename = '2026-10-09T21-30-00-000Z_ricasashaa.mp4';
        Storage::disk('local')->put('live-recordings/ricasashaa/'.$filename, 'test');
        $watch = '/live/ricasashaa/'.$filename.'/watch';
        $download = '/live/ricasashaa/'.$filename.'/download';

        $this->get($watch)->assertRedirect('/login');
        $viewer = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        $this->actingAs($viewer)->get($watch)->assertForbidden();
        $this->actingAs($viewer)->get($download)->assertForbidden();

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->get('/live')->assertOk()->assertSee('@ricasashaa');
        $this->actingAs($admin)->get('/live/status')->assertOk()->assertJsonPath('ricasashaa.status', 'unknown');
        $this->actingAs($admin)->get($watch)->assertOk();
        $this->actingAs($admin)->get($download)->assertOk();
        $this->actingAs($admin)->postJson('/live/uploads', ['account' => 'ricasashaa', 'bytes' => 2048])->assertCreated();
        $this->actingAs($admin)->delete('/live/ricasashaa/'.$filename)->assertRedirect('/live');
        $this->assertFalse(Storage::disk('local')->exists('live-recordings/ricasashaa/'.$filename));
    }

    public function test_only_admin_can_begin_a_manual_private_upload(): void
    {
        Storage::fake('local');
        $this->post('/live/uploads', ['account' => 'knock1knock', 'bytes' => 2048])
            ->assertRedirect('/login');

        $viewer = new User(['name' => 'Viewer', 'email' => 'viewer@example.test', 'is_admin' => false]);
        $this->actingAs($viewer)->postJson('/live/uploads', ['account' => 'knock1knock', 'bytes' => 2048])
            ->assertForbidden();

        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin)->postJson('/live/uploads', ['account' => 'not-an-account', 'bytes' => 2048])
            ->assertUnprocessable();
        $this->actingAs($admin)->postJson('/live/uploads', ['account' => 'knock1knock', 'bytes' => 300 * 1024 * 1024])
            ->assertUnprocessable();
    }

    public function test_admin_can_upload_an_mp4_in_private_chunks_without_multipart_limit(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin);
        $bytes = "\0\0\0\x18ftypisom".str_repeat('v', 2 * 1024 * 1024 + 1500);
        $begin = $this->postJson('/live/uploads', [
            'account' => 'knock1knock', 'bytes' => strlen($bytes),
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
        $files = Storage::disk('local')->files('live-recordings/knock1knock');
        $this->assertCount(1, $files);
        $this->assertSame($bytes, Storage::disk('local')->get($files[0]));
        $this->assertFalse(Storage::disk('local')->exists('live-recording-uploads/manual/'.$id.'/meta.json'));
        $this->get('/live/knock1knock/'.basename($files[0]).'/download')->assertOk();
    }

    public function test_incomplete_or_non_mp4_upload_is_not_published(): void
    {
        Storage::fake('local');
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        $this->actingAs($admin);
        $bytes = str_repeat('x', 2048);
        $begin = $this->postJson('/live/uploads', [
            'account' => 'knock1knock', 'bytes' => strlen($bytes),
        ])->assertCreated();
        $id = $begin->json('id');

        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertStatus(409);
        $this->call('PUT', '/live/uploads/'.$id.'/parts/0', [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
        ], $bytes)->assertOk();
        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertUnprocessable();
        $this->assertSame([], Storage::disk('local')->files('live-recordings/knock1knock'));
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
            'account' => 'knock1knock', 'bytes' => strlen($bytes), 'format' => 'webm',
        ])->assertCreated()->json('id');

        $this->call('PUT', '/live/uploads/'.$id.'/parts/0', [], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
        ], $bytes)->assertOk();
        $this->postJson('/live/uploads/'.$id.'/complete', [])->assertUnprocessable();
        $this->assertSame([], Storage::disk('local')->files('live-recordings/knock1knock'));
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
                'account' => 'knock1knock', 'bytes' => strlen($bytes), 'format' => 'webm',
            ])->assertCreated()->json('id');
            $this->call('PUT', '/live/uploads/'.$id.'/parts/0', [], [], [], [
                'CONTENT_TYPE' => 'application/octet-stream',
            ], $bytes)->assertOk();
            $this->postJson('/live/uploads/'.$id.'/complete', [])->assertOk();

            $files = Storage::disk('local')->files('live-recordings/knock1knock');
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
            'account' => 'knock1knock', 'bytes' => 2048,
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
