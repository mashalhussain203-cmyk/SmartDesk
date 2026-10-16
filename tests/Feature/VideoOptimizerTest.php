<?php

namespace Tests\Feature;

use App\Support\VideoRenderGuard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VideoOptimizerTest extends TestCase
{
    public function test_video_optimizer_page_is_public(): void
    {
        $this->get('/tools/video-optimizer')
            ->assertOk()
            ->assertSee('TikTok Video Optimizer');
    }

    public function test_default_video_mode_is_real_4k120_conversion(): void
    {
        $this->get('/tools/video-optimizer')
            ->assertOk()
            ->assertSee('name="mode" value="4k120"', false)
            ->assertSee('data-vo-mode="4k120" aria-pressed="true"', false);
    }

    public function test_invalid_video_is_rejected_before_processing(): void
    {
        $this->postJson('/tools/video-optimizer', [
            'mode' => 'original',
            'video' => UploadedFile::fake()->create('notes.txt', 2, 'text/plain'),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['video']);
    }

    public function test_multiple_validation_attempts_do_not_trigger_the_old_three_per_hour_limit(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/tools/video-optimizer', [
                'mode' => 'original',
                'video' => UploadedFile::fake()->create('invalid.txt', 1, 'text/plain'),
            ])->assertUnprocessable()->assertJsonValidationErrors(['video']);
        }
    }

    public function test_4k120_page_supports_3_minute_clips(): void
    {
        $this->get('/tools/video-optimizer')->assertOk()->assertSee('max 3 minutes');
    }

    public function test_4k120_mode_is_accepted_by_validation(): void
    {
        $this->postJson('/tools/video-optimizer', [
            'mode' => '4k120',
            'video' => UploadedFile::fake()->create('invalid.txt', 2, 'text/plain'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['video']);
    }


    public function test_stale_video_processing_is_recovered_without_blocking_other_uploads(): void
    {
        $directory = sys_get_temp_dir().'/video-render-guard-'.bin2hex(random_bytes(6));
        File::ensureDirectoryExists($directory);
        $token = str_repeat('e', 40);
        $statusFile = $directory.'/'.$token.'.status.json';

        try {
            file_put_contents($statusFile, json_encode([
                'status' => 'processing',
                'created_at' => time() - 500,
                'progress' => 25,
                'source' => $token.'.source.mp4',
            ]));

            $state = VideoRenderGuard::inspect($directory, $token);
            $this->assertSame('failed', $state['status']);
            $this->assertStringContainsString('new export', $state['message']);
            $this->assertSame('failed', json_decode((string) file_get_contents($statusFile), true)['status']);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_recently_queued_video_is_not_interrupted(): void
    {
        $directory = sys_get_temp_dir().'/video-render-guard-'.bin2hex(random_bytes(6));
        File::ensureDirectoryExists($directory);
        $token = str_repeat('d', 40);

        try {
            file_put_contents($directory.'/'.$token.'.status.json', json_encode([
                'status' => 'queued', 'created_at' => time(), 'progress' => 0,
            ]));

            $state = VideoRenderGuard::inspect($directory, $token);
            $this->assertSame('queued', $state['status']);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_active_render_without_session_returns_inactive(): void
    {
        $this->getJson('/tools/video-optimizer/active')
            ->assertOk()
            ->assertJsonPath('active', false);
    }

    public function test_iphone_variant_requires_a_private_export_session(): void
    {
        $token = str_repeat('a', 40);
        $this->get('/tools/video-optimizer/download/'.$token.'?version=iphone')
            ->assertNotFound();
    }

    public function test_video_optimizer_has_a_separate_iphone_download_link(): void
    {
        $this->get('/tools/video-optimizer')
            ->assertOk()
            ->assertSee('id="vo-phone-ready"', false)
            ->assertSee('id="vo-phone-download"', false);
    }

    public function test_render_status_is_private_to_the_upload_session(): void
    {
        $this->get('/tools/video-optimizer/status/'.str_repeat('a', 40))
            ->assertNotFound();
    }

    public function test_download_cannot_be_accessed_without_session(): void
    {
        $this->get('/tools/video-optimizer/download/'.str_repeat('a', 40))
            ->assertNotFound();
    }
}
