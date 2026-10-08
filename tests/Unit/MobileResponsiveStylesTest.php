<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MobileResponsiveStylesTest extends TestCase
{
    private function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    public function test_responsive_stylesheet_exists_with_small_screen_breakpoints(): void
    {
        $path = $this->projectRoot().'/public/css/mobile-responsive.css';

        $this->assertFileExists($path);

        $css = file_get_contents($path);
        $this->assertIsString($css);

        foreach (['767px', '480px', '360px'] as $width) {
            $this->assertStringContainsString('@media (max-width: '.$width.')', $css);
        }

        $this->assertStringContainsString('min-height: 44px', $css);
        $this->assertStringContainsString('html[dir="rtl"]', $css);
        $this->assertSame(substr_count($css, '{'), substr_count($css, '}'));
    }


    public function test_contact_form_has_phone_safe_fields_and_full_width_submit_button(): void
    {
        $css = file_get_contents($this->projectRoot().'/public/css/mobile-responsive.css');

        $this->assertIsString($css);
        $this->assertStringContainsString('.legal-page .contact-input', $css);
        $this->assertStringContainsString('.legal-page .contact-textarea', $css);
        $this->assertStringContainsString('.legal-page .contact-alert', $css);
        $this->assertStringContainsString('.legal-page .contact-submit', $css);
        $this->assertStringContainsString('scroll-margin-block: 80px', $css);
        $this->assertStringContainsString('font-size: 16px', $css);
        $this->assertStringContainsString('width: 100%', $css);
    }

    public function test_all_web_layouts_load_mobile_rules_after_their_page_styles(): void
    {
        $paths = [
            'resources/views/layouts/site-layout.blade.php',
            'resources/views/layouts/admin-layout.blade.php',
            'resources/views/gmail/inbox.blade.php',
            'resources/views/gmail/show.blade.php',
        ];

        foreach ($paths as $path) {
            $content = file_get_contents($this->projectRoot().'/'.$path);
            $this->assertIsString($content, $path);

            $asset = "asset('css/mobile-responsive.css')";
            $this->assertStringContainsString($asset, $content, $path);

            $position = strpos($content, $asset);
            $this->assertNotFalse($position, $path);

            $lastStyleClose = strrpos(substr($content, 0, $position), '</style>');
            $this->assertNotFalse($lastStyleClose, $path);
            $this->assertGreaterThan($lastStyleClose, $position, $path);
        }
    }
}
