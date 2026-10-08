<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiChatService
{
    private ?int $lastStatus = null;

    private ?int $lastRetryAfter = null;

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '' && $this->modelName() !== '';
    }

    public function modelName(): string
    {
        return trim((string) config('mashal-ai.openai.model', 'gpt-5-mini'));
    }

    public function lastStatus(): ?int
    {
        return $this->lastStatus;
    }

    public function lastRetryAfterSeconds(): ?int
    {
        return $this->lastRetryAfter;
    }

    /**
     * Same response contract as GroqChatService so the existing Mashal AI
     * chat history, browser UI and account workspace continue to work.
     *
     * @param array<int,array{role:string,content:string}> $messages
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    public function chat(array $messages, array $options = []): array
    {
        $this->lastStatus = null;
        $this->lastRetryAfter = null;

        if (! $this->isConfigured()) {
            throw new RuntimeException('OpenAI is nog niet geconfigureerd.', 503);
        }

        $input = [];
        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? '');
            $text = trim((string) ($message['content'] ?? ''));

            if (! in_array($role, ['system', 'developer', 'user', 'assistant'], true) || $text === '') {
                continue;
            }

            $input[] = ['role' => $role, 'content' => $text];
        }

        if ($input === []) {
            throw new RuntimeException('Er is geen geldig bericht om te versturen.', 422);
        }

        $mode = strtolower(trim((string) ($options['mode'] ?? 'auto')));
        if (! in_array($mode, ['auto', 'plain', 'web', 'research', 'code'], true)) {
            $mode = 'auto';
        }

        $payload = [
            'model' => $this->modelName(),
            'input' => $input,
            'store' => false,
            'max_output_tokens' => max(512, min(8192, (int) config('mashal-ai.openai.max_output_tokens', 3000))),
        ];

        // OpenAI tools run on the provider's servers, not in the visitor's
        // browser. Never claim to have searched or executed code otherwise.
        $tools = [];
        if (in_array($mode, ['web', 'research'], true)
            || ($mode === 'auto' && (bool) config('mashal-ai.openai.auto_web_search', false))) {
            $tools[] = ['type' => 'web_search'];
        }
        if ($mode === 'code') {
            $tools[] = ['type' => 'code_interpreter', 'container' => ['type' => 'auto']];
        }
        if ($tools !== []) {
            $payload['tools'] = $tools;
        }

        if ($mode === 'web') {
            $payload['instructions'] = 'Use web search for current information. Cite sources; do not claim to have searched unless the tool ran.';
        } elseif ($mode === 'research') {
            $payload['instructions'] = 'Research carefully using web search, compare credible sources, give citations and dates. Do not invent searches.';
        } elseif ($mode === 'code') {
            $payload['instructions'] = 'Use code interpreter where needed to verify calculations. Do not claim code ran without a tool result.';
        }

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->withToken($this->apiKey())
                ->connectTimeout(10)
                ->timeout((int) config('mashal-ai.openai.timeout', 120))
                ->post('https://api.openai.com/v1/responses', $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('OpenAI is tijdelijk niet bereikbaar.', 502, $exception);
        }

        $this->lastStatus = $response->status();
        $retry = $response->header('Retry-After');
        $this->lastRetryAfter = is_numeric($retry) ? max(1, (int) $retry) : null;

        if (! $response->successful()) {
            throw new RuntimeException(match ($response->status()) {
                401, 403 => 'De OpenAI API-sleutel is ongeldig of niet toegestaan.',
                404 => 'Het ingestelde OpenAI-model bestaat niet of is niet toegankelijk.',
                429 => 'De OpenAI-limiet is bereikt. Probeer later opnieuw.',
                default => 'De OpenAI API kon het bericht niet verwerken.',
            }, $response->status());
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new RuntimeException('OpenAI gaf geen geldig JSON-antwoord.', 502);
        }

        $text = '';
        $sources = [];
        $usedWeb = false;
        $usedCode = false;

        foreach ((array) ($data['output'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (($item['type'] ?? '') === 'web_search_call') {
                $usedWeb = true;
            } elseif (($item['type'] ?? '') === 'code_interpreter_call') {
                $usedCode = true;
            }

            if (($item['type'] ?? '') !== 'message') {
                continue;
            }

            foreach ((array) ($item['content'] ?? []) as $content) {
                if (! is_array($content) || ($content['type'] ?? '') !== 'output_text') {
                    continue;
                }

                $text .= (string) ($content['text'] ?? '');

                foreach ((array) ($content['annotations'] ?? []) as $annotation) {
                    if (! is_array($annotation) || ($annotation['type'] ?? '') !== 'url_citation') {
                        continue;
                    }

                    $url = (string) ($annotation['url'] ?? '');
                    if (! filter_var($url, FILTER_VALIDATE_URL)
                        || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
                        continue;
                    }

                    $sources[$url] = [
                        'title' => (string) ($annotation['title'] ?? $url),
                        'url' => $url,
                        'content' => '',
                        'score' => null,
                    ];
                }
            }
        }

        $text = trim($text !== '' ? $text : (string) ($data['output_text'] ?? ''));
        if ($text === '') {
            throw new RuntimeException('OpenAI gaf geen bruikbaar tekstantwoord.', 502);
        }

        return [
            'message' => $text,
            'model' => (string) ($data['model'] ?? $this->modelName()),
            'usage' => is_array($data['usage'] ?? null) ? $data['usage'] : null,
            'mode' => $mode,
            'used_web' => $usedWeb,
            'used_code' => $usedCode,
            'sources' => array_slice(array_values($sources), 0, 10),
            'tools' => array_values(array_filter([
                $usedWeb ? ['name' => 'OpenAI Web Search', 'type' => 'web_search'] : null,
                $usedCode ? ['name' => 'OpenAI Code Interpreter', 'type' => 'code_interpreter'] : null,
            ])),
        ];
    }

    private function apiKey(): string
    {
        return trim((string) config('mashal-ai.openai.api_key', ''));
    }
}
