<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminLiveChatDesignTest extends TestCase
{
    private function projectFile(string $path): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/'.$path);

        $this->assertIsString($contents, $path);

        return $contents;
    }

    public function test_operator_design_keeps_existing_live_chat_hooks(): void
    {
        $view = $this->projectFile('resources/views/admin/live-chat.blade.php');

        foreach ([
            'id="admin-live-chat"',
            'class="lca-list"',
            'class="lca-log"',
            'class="lca-form"',
            'class="lca-submit"',
            'data-inbox=',
            'data-presence=',
            'data-search',
            'data-filter',
            'data-close',
            'data-typing-indicator',
        ] as $hook) {
            $this->assertStringContainsString($hook, $view);
        }

        $this->assertStringContainsString('MASHAL SUPPORT / STUDIO EDITION', $view);
        $this->assertStringContainsString('.lca-message[data-sender="admin"]', $view);
        $this->assertStringContainsString('@media(max-width:760px)', $view);
    }

    public function test_admin_launcher_remains_accessible_and_points_to_operator_inbox(): void
    {
        $view = $this->projectFile('resources/views/site/partials/admin-live-chat-launcher.blade.php');

        $this->assertStringContainsString('id="admin-live-chat-launcher"', $view);
        $this->assertStringContainsString("route('admin.live-chat.index')", $view);
        $this->assertStringContainsString('aria-label=', $view);
        $this->assertStringContainsString('@media (max-width: 699px)', $view);
    }

    public function test_regular_visitors_still_receive_separate_chat_in_site_layout(): void
    {
        $layout = $this->projectFile('resources/views/layouts/site-layout.blade.php');

        $this->assertStringContainsString('$layoutIsAdmin', $layout);
        $this->assertStringContainsString("@include('site.partials.admin-live-chat-launcher')", $layout);
        $this->assertStringContainsString("@include('site.partials.guest-chat')", $layout);
    }
}
