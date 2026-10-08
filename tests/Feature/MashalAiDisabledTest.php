<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MashalAiDisabledTest extends TestCase
{
    public function test_mashal_ai_routes_are_not_registered(): void
    {
        foreach ([
            'ai.chat',
            'ai.chat.message',
            'ai.chat.voice.turn',
            'ai.chat.voice.tts',
            'ai.workspace.bootstrap',
            'ai.workspace.shared',
            'ai.studio.dashboard',
        ] as $name) {
            $this->assertFalse(Route::has($name), $name.' should not be registered.');
        }
    }

    public function test_old_mashal_ai_urls_are_not_available(): void
    {
        $this->get('/ai-chat')->assertNotFound();
        $this->post('/ai-chat/message', ['message' => 'Hallo'])->assertNotFound();
        $this->get('/ai-chat/shared/example')->assertNotFound();
        $this->get('/ai-studio')->assertNotFound();
    }

    public function test_mashal_ai_is_not_in_site_navigation(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/site-layout.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringNotContainsString('Mashal AI', $layout);
        $this->assertStringNotContainsString("route('ai.chat')", $layout);
        $this->assertStringNotContainsString('$hasAiChat', $layout);
    }
}
