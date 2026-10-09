<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_delete_completed_recordings(): void
    {
        $admin = new User(['name' => 'Admin', 'email' => 'admin@example.test', 'is_admin' => true]);
        Storage::fake('local');
        Storage::disk('local')->put('live-recordings/emyii/sample.mp4', 'test');

        $this->actingAs($admin)->delete('/live/emyii/sample.mp4')->assertRedirect('/live');
        $this->assertFalse(Storage::disk('local')->exists('live-recordings/emyii/sample.mp4'));
    }
}
