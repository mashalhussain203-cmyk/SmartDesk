<?php

namespace Tests\Feature;

use App\Services\OpenAiVisionService;
use App\Services\OpenAiVoiceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MashalAiMediaProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mashal-ai.openai.api_key', 'media-test-key');
        config()->set('mashal-ai.openai.transcription_model', 'gpt-4o-mini-transcribe');
        config()->set('mashal-ai.openai.vision_model', 'gpt-4.1-mini');
    }

    public function test_voice_transcription_uses_openai_instead_of_groq(): void
    {
        Http::fake([
            'api.openai.com/v1/audio/transcriptions' => Http::response([
                'text' => 'Hallo vanuit Mashal AI.',
            ], 200),
        ]);

        $audio = UploadedFile::fake()->create('speech.webm', 4, 'audio/webm');
        $text = app(OpenAiVoiceService::class)->transcribe($audio, 'nl');

        $this->assertSame('Hallo vanuit Mashal AI.', $text);
        Http::assertSent(fn ($request): bool =>
            $request->url() === 'https://api.openai.com/v1/audio/transcriptions');
    }

    public function test_images_are_sent_to_official_openai_responses_endpoint(): void
    {
        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'model' => 'gpt-4.1-mini',
                'output' => [[
                    'type' => 'message',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => 'Ik zie een foto.',
                    ]],
                ]],
            ], 200),
        ]);

        $file = tempnam(sys_get_temp_dir(), 'mashal-ai-test-');
        $this->assertNotFalse($file);
        file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9rFjfQAAAABJRU5ErkJggg=='));

        try {
            $result = app(OpenAiVisionService::class)->analyzeMany([[
                'path' => $file,
                'mime' => 'image/png',
                'label' => 'foto.png',
            ]], 'Wat staat hier?');
        } finally {
            @unlink($file);
        }

        $this->assertSame('Ik zie een foto.', $result);
        Http::assertSent(function ($request): bool {
            $input = $request['input'][0]['content'] ?? [];
            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request['model'] === 'gpt-4.1-mini'
                && $request['store'] === false
                && collect($input)->contains(fn ($part) =>
                    ($part['type'] ?? null) === 'input_image');
        });
    }

    public function test_public_chat_page_has_the_chat_interface_and_draft_recovery(): void
    {
        $view = file_get_contents(base_path('resources/views/ai/chat.blade.php'));
        $this->assertIsString($view);
        $this->assertStringContainsString('css/mashal-ai-chat-interface.css', $view);
        $this->assertStringContainsString('id="ai-attach-menu-panel"', $view);
        $this->assertStringContainsString('Waarmee kan ik je helpen?', $view);
        $this->assertStringContainsString('optimisticMessage?.remove()', $view);
        $this->assertStringContainsString('response.status === 419', $view);
        $this->assertStringContainsString('id="chat-list"', $view);
        $this->assertStringContainsString("route('ai.chat.message')", $view);
    }
}
