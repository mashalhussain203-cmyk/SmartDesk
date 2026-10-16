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

    public function test_invalid_video_is_rejected_before_processing(): void
    {
        $this->postJson('/tools/video-optimizer', [
            'mode' => 'original',
            'video' => UploadedFile::fake()->create('notes.txt', 2, 'text/plain'),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['video']);
    }

    public function test_download_cannot_be_accessed_without_session(): void
    {
        $this->get('/tools/video-optimizer/download/'.str_repeat('a', 40))
            ->assertNotFound();
    }
}
