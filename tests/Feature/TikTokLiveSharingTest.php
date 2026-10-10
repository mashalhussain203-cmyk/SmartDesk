<?php

namespace Tests\Feature;

use Tests\TestCase;

class TikTokLiveSharingTest extends TestCase
{
    public function test_tiktok_live_sharing_page_is_public(): void
    {
        $this->get(route('tiktok-live.index'))
            ->assertOk()
            ->assertSee('TikTok LIVE delen')
            ->assertSee('live-share-form');
    }

    public function test_live_counts_has_a_separate_tiktok_live_tool(): void
    {
        $this->get(route('live-counts.index'))
            ->assertOk()
            ->assertSee(route('tiktok-live.index'), false)
            ->assertSee('TikTok LIVE delen');
    }
}
