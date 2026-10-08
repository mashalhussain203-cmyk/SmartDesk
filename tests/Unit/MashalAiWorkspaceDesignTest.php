<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MashalAiWorkspaceDesignTest extends TestCase
{
    private function source(string $path): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/'.$path);
        $this->assertIsString($contents, $path);

        return $contents;
    }

    public function test_mashal_ai_has_its_own_chat_workspace_and_tools(): void
    {
        $view = $this->source('resources/views/ai/chat.blade.php');
        $css = $this->source('public/css/mashal-ai-workspace.css');

        $this->assertStringContainsString("css/mashal-ai-workspace.css", $view);
        $this->assertStringContainsString('id="mashal-chat-app"', $view);
        $this->assertStringContainsString('id="chat-list"', $view);
        $this->assertStringContainsString('id="ai-messages"', $view);
        $this->assertStringContainsString('id="ai-form"', $view);
        $this->assertStringContainsString('id="ai-files"', $view);
        $this->assertStringContainsString('id="ai-live-start"', $view);
        $this->assertStringContainsString("route('ai.chat.message')", $view);

        foreach (['auto', 'web', 'research', 'code', 'plain'] as $mode) {
            $this->assertStringContainsString('data-ai-quick-mode="'.$mode.'"', $view);
        }

        $this->assertStringContainsString("new Event('change', { bubbles: true })", $view);
        $this->assertStringContainsString('syncQuickModeButtons()', $view);
        $this->assertStringContainsString('.mashal-chat-app .ai-workspace-tools', $css);
        $this->assertStringContainsString('html[dir="rtl"]', $css);
        $this->assertStringContainsString('@media(max-width:560px)', $css);
    }

    public function test_new_styles_are_scoped_to_mashal_ai_and_do_not_copy_a_brand(): void
    {
        $css = $this->source('public/css/mashal-ai-workspace.css');

        $this->assertStringContainsString('mashal-chat-app', $css);
        $this->assertStringNotContainsString('chatgpt.com', $css);
        $this->assertStringNotContainsString('openai.com', $css);
        $this->assertStringNotContainsString('guest-chat', $css);
        $this->assertStringNotContainsString('admin-live-chat', $css);
    }
}
