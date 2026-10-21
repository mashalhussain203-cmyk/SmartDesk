<?php

namespace Tests\Feature;

use Tests\TestCase;

class TikTokDownloaderTest extends TestCase
{
    public function test_public_download_page_exists(): void
    {
        $this->get('/tools/tiktok-downloader')
            ->assertOk()
            ->assertSee('TikTok Downloader');
    }

    public function test_only_tiktok_links_are_accepted(): void
    {
        $this->postJson('/tools/tiktok-downloader/analyze', [
            'url' => 'https://example.com/@someone/video/7234567890123456789',
        ])->assertStatus(422);

        $this->postJson('/tools/tiktok-downloader/analyze', [
            'url' => 'https://www.tiktok.com.evil.example/@user/video/7234567890123456789',
        ])->assertStatus(422);
    }

    public function test_unowned_or_expired_download_cannot_be_started_or_read(): void
    {
        $token = str_repeat('a', 40);

        $this->postJson('/tools/tiktok-downloader/prepare', [
            'token' => $token, 'format' => 'video',
        ])->assertStatus(422);

        $this->getJson('/tools/tiktok-downloader/status/'.$token)->assertNotFound();
        $this->get('/tools/tiktok-downloader/download/'.$token)->assertNotFound();
    }
}
