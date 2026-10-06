<?php

namespace Tests\Unit;

use App\Services\TikTokVideoStatsService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TikTokVideoStatsServiceTest extends TestCase
{
    public function test_it_extracts_a_video_id_from_a_standard_url(): void
    {
        $service = new TikTokVideoStatsService;

        $this->assertSame(
            '7562873521378954528',
            $service->extractVideoId('https://www.tiktok.com/@creator/video/7562873521378954528?lang=en')
        );
    }

    public function test_it_rejects_non_tiktok_hosts(): void
    {
        $service = new TikTokVideoStatsService;

        $this->expectException(InvalidArgumentException::class);
        $service->normaliseUrl('https://example.com/@creator/video/7562873521378954528');
    }

    public function test_it_accepts_tiktok_subdomains(): void
    {
        $service = new TikTokVideoStatsService;

        $this->assertSame(
            'https://vm.tiktok.com/ZMexample/',
            $service->normaliseUrl('vm.tiktok.com/ZMexample/')
        );
    }
}
