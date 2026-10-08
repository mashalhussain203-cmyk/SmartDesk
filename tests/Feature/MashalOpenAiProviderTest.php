<?php

namespace Tests\Feature;

use App\Services\OpenAiChatService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class MashalOpenAiProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('mashal-ai.openai.api_key', 'test-only-key');
        config()->set('mashal-ai.openai.model', 'gpt-5-mini');
        config()->set('mashal-ai.openai.max_output_tokens', 3000);
    }

    public function test_openai_response_returns_compatible_chat_payload(): void
    {
        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'id' => 'resp_123',
                'model' => 'gpt-5-mini',
                'output' => [[
                    'type' => 'message',
                    'role' => 'assistant',
                    'content' => [[
                        'type' => 'output_text',
                        'text' => 'Hallo! Hoe kan ik je helpen?',
                        'annotations' => [],
                    ]],
                ]],
                'usage' => ['input_tokens' => 15, 'output_tokens' => 7],
            ], 200),
        ]);

        $result = app(OpenAiChatService::class)->chat([
            ['role' => 'system', 'content' => 'You are Mashal AI.'],
            ['role' => 'user', 'content' => 'Hallo'],
        ], ['mode' => 'plain']);

        $this->assertSame('Hallo! Hoe kan ik je helpen?', $result['message']);
        $this->assertSame('gpt-5-mini', $result['model']);
        $this->assertSame('plain', $result['mode']);
        $this->assertFalse($result['used_web']);
        $this->assertFalse($result['used_code']);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request['model'] === 'gpt-5-mini'
                && $request['store'] === false
                && $request['input'][1]['content'] === 'Hallo'
                && $request->hasHeader('Authorization', 'Bearer test-only-key')
                && ! isset($request['tools']);
        });
    }

    public function test_web_search_sources_are_forwarded_to_existing_ui(): void
    {
        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'model' => 'gpt-5-mini',
                'output' => [
                    ['type' => 'web_search_call', 'status' => 'completed'],
                    [
                        'type' => 'message',
                        'role' => 'assistant',
                        'content' => [[
                            'type' => 'output_text',
                            'text' => 'Dit is het antwoord.',
                            'annotations' => [[
                                'type' => 'url_citation',
                                'url' => 'https://example.com/source',
                                'title' => 'Bron',
                            ]],
                        ]],
                    ],
                ],
            ], 200),
        ]);

        $result = app(OpenAiChatService::class)->chat([
            ['role' => 'user', 'content' => 'Zoek recente informatie'],
        ], ['mode' => 'web']);

        $this->assertTrue($result['used_web']);
        $this->assertSame('https://example.com/source', $result['sources'][0]['url']);

        Http::assertSent(fn (Request $request): bool =>
            $request['tools'][0]['type'] === 'web_search');
    }

    public function test_failed_authentication_does_not_leak_the_api_key(): void
    {
        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'error' => ['message' => 'Invalid API key.'],
            ], 401),
        ]);

        try {
            app(OpenAiChatService::class)->chat([
                ['role' => 'user', 'content' => 'Hallo'],
            ]);
            $this->fail('Expected a provider authentication exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame(401, $exception->getCode());
            $this->assertStringNotContainsString('test-only-key', $exception->getMessage());
        }
    }

    public function test_provider_selection_is_explicit_and_groq_remains_default(): void
    {
        $this->assertSame('groq', config('mashal-ai.provider'));

        $controller = file_get_contents(base_path('app/Http/Controllers/AiChatController.php'));
        $this->assertIsString($controller);
        $this->assertStringContainsString("config('mashal-ai.provider', 'groq')", $controller);
        $this->assertStringContainsString('$this->chatProvider()->chat(', $controller);
    }
}
