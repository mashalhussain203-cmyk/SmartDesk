<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
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

    public function test_4k120_mode_is_accepted_by_validation(): void
    {
        $this->postJson('/tools/video-optimizer', [
            'mode' => '4k120',
            'video' => UploadedFile::fake()->create('invalid.txt', 2, 'text/plain'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['video']);
    }

    public function test_active_render_without_session_returns_inactive(): void
    {
        $this->getJson('/tools/video-optimizer/active')
            ->assertOk()
            ->assertJsonPath('active', false);
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
