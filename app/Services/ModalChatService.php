<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ModalChatService
{
    public function isConfigured(): bool
    {
        if ($this->endpoint() === '') {
            return false;
        }

        if (
            (bool) config(
                'modal-chat.allow_unauthenticated',
                false
            )
        ) {
            return true;
        }

        return $this->apiKey() !== '' ||
            (
                $this->proxyKey() !== '' &&
                $this->proxySecret() !== ''
            );
    }

    public function modelName(): string
    {
        return trim(
            (string) config(
                'modal-chat.model',
                ''
            )
        );
    }

    /**
     * @param array<int, array{role:string,content:string}> $messages
     *
     * @return array{
     *     message:string,
     *     model:string|null,
     *     usage:array<string,mixed>|null
     * }
     */
    public function chat(array $messages): array
    {
        $this->assertConfigured();

        $messages = $this->normalizeMessages(
            $messages
        );

        if ($messages === []) {
            throw ValidationException::withMessages([
                'message' => 'Er is geen geldig chatbericht om te versturen.',
            ]);
        }

        $payload = [
            'messages' => $messages,
            'stream' => false,
            'temperature' => $this->temperature(),
            'max_tokens' => $this->maxTokens(),
        ];

        $model = $this->modelName();

        if ($model !== '') {
            $payload['model'] = $model;
        }

        try {
            $request = Http::acceptJson()
                ->asJson()
                ->connectTimeout(
                    $this->connectTimeout()
                )
                ->timeout(
                    $this->timeout()
                );

            $headers = $this->authenticationHeaders();

            if ($headers !== []) {
                $request = $request->withHeaders(
                    $headers
                );
            }

            $response = $request->post(
                $this->chatCompletionsEndpoint(),
                $payload
            );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'De verbinding met de Modal AI-endpoint kon niet worden gemaakt.',
                previous: $exception
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'De Modal AI-aanvraag kon niet worden verstuurd.',
                previous: $exception
            );
        }

        $this->throwForFailedResponse(
            $response
        );

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException(
                'Modal gaf geen geldige JSON-response terug.'
            );
        }

        $message = $this->extractAssistantMessage(
            $data
        );

        if ($message === '') {
            throw new RuntimeException(
                'Modal gaf geen antwoordtekst terug.'
            );
        }

        $usage = $data['usage'] ?? null;

        return [
            'message' => $message,
            'model' => isset($data['model'])
                ? (string) $data['model']
                : ($model !== '' ? $model : null),
            'usage' => is_array($usage)
                ? $usage
                : null,
        ];
    }

    private function assertConfigured(): void
    {
        if ($this->endpoint() === '') {
            throw new RuntimeException(
                'MODAL_CHAT_ENDPOINT is niet ingesteld.'
            );
        }

        if (
            ! (bool) config(
                'modal-chat.allow_unauthenticated',
                false
            ) &&
            $this->apiKey() === '' &&
            (
                $this->proxyKey() === '' ||
                $this->proxySecret() === ''
            )
        ) {
            throw new RuntimeException(
                'Er zijn geen Modal chat credentials ingesteld.'
            );
        }
    }

    /**
     * @param array<int, array{role:string,content:string}> $messages
     *
     * @return array<int, array{role:string,content:string}>
     */
    private function normalizeMessages(
        array $messages
    ): array {
        $allowedRoles = [
            'system',
            'user',
            'assistant',
        ];

        $normalized = [];

        foreach ($messages as $message) {
            if (! is_array($message)) {
                continue;
            }

            $role = strtolower(
                trim(
                    (string) (
                        $message['role'] ??
                        ''
                    )
                )
            );

            $content = trim(
                (string) (
                    $message['content'] ??
                    ''
                )
            );

            if (
                ! in_array(
                    $role,
                    $allowedRoles,
                    true
                ) ||
                $content === ''
            ) {
                continue;
            }

            $normalized[] = [
                'role' => $role,
                'content' => mb_substr(
                    $content,
                    0,
                    12000
                ),
            ];
        }

        /*
         * Houd de prompt compact. Het systeembericht blijft behouden,
         * daarna worden alleen de recentste conversatieberichten gestuurd.
         */
        $systemMessages = array_values(
            array_filter(
                $normalized,
                fn (array $item): bool =>
                    $item['role'] === 'system'
            )
        );

        $conversationMessages = array_values(
            array_filter(
                $normalized,
                fn (array $item): bool =>
                    $item['role'] !== 'system'
            )
        );

        $conversationMessages = array_slice(
            $conversationMessages,
            -21
        );

        return [
            ...array_slice(
                $systemMessages,
                0,
                1
            ),
            ...$conversationMessages,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function authenticationHeaders(): array
    {
        /*
         * Shared/OpenAI-compatible endpoint:
         * Authorization: Bearer <api-key>
         */
        if ($this->apiKey() !== '') {
            return [
                'Authorization' =>
                    'Bearer ' . $this->apiKey(),
            ];
        }

        /*
         * Modal Proxy Auth:
         * Modal-Key: wk-...
         * Modal-Secret: ws-...
         */
        if (
            $this->proxyKey() !== '' &&
            $this->proxySecret() !== ''
        ) {
            return [
                'Modal-Key' => $this->proxyKey(),
                'Modal-Secret' => $this->proxySecret(),
            ];
        }

        return [];
    }

    private function extractAssistantMessage(
        array $data
    ): string {
        $message =
            data_get(
                $data,
                'choices.0.message.content'
            );

        if (is_string($message)) {
            return trim($message);
        }

        /*
         * Sommige OpenAI-compatible backends retourneren content-parts.
         */
        if (is_array($message)) {
            $parts = [];

            foreach ($message as $part) {
                if (
                    is_array($part) &&
                    isset($part['text']) &&
                    is_string($part['text'])
                ) {
                    $parts[] = $part['text'];
                }
            }

            $combined = trim(
                implode(
                    "\n",
                    $parts
                )
            );

            if ($combined !== '') {
                return $combined;
            }
        }

        /*
         * Fallback voor eenvoudige completion-compatible responses.
         */
        $text =
            data_get(
                $data,
                'choices.0.text'
            );

        return is_string($text)
            ? trim($text)
            : '';
    }

    private function throwForFailedResponse(
        Response $response
    ): void {
        if ($response->successful()) {
            return;
        }

        $status = $response->status();

        $message =
            match (true) {
                $status === 401,
                $status === 403 =>
                    'Modal heeft de AI-credentials geweigerd.',

                $status === 404 =>
                    'De ingestelde Modal AI-endpoint bestaat niet.',

                $status === 408 =>
                    'De Modal AI-aanvraag duurde te lang.',

                $status === 429 =>
                    'De Modal AI-endpoint wordt momenteel te vaak aangeroepen.',

                $status >= 500 =>
                    'De Modal AI-endpoint gaf een serverfout terug.',

                default =>
                    'De Modal AI-endpoint gaf een onverwachte fout terug.',
            };

        throw new RuntimeException(
            $message . ' HTTP ' . $status . '.'
        );
    }

    private function chatCompletionsEndpoint(): string
    {
        $endpoint = rtrim(
            $this->endpoint(),
            '/'
        );

        if (
            str_ends_with(
                $endpoint,
                '/chat/completions'
            )
        ) {
            return $endpoint;
        }

        if (
            str_ends_with(
                $endpoint,
                '/v1'
            )
        ) {
            return $endpoint .
                '/chat/completions';
        }

        return $endpoint;
    }

    private function endpoint(): string
    {
        return trim(
            (string) config(
                'modal-chat.endpoint',
                ''
            )
        );
    }

    private function apiKey(): string
    {
        return trim(
            (string) config(
                'modal-chat.api_key',
                ''
            )
        );
    }

    private function proxyKey(): string
    {
        return trim(
            (string) config(
                'modal-chat.proxy_key',
                ''
            )
        );
    }

    private function proxySecret(): string
    {
        return trim(
            (string) config(
                'modal-chat.proxy_secret',
                ''
            )
        );
    }

    private function timeout(): int
    {
        return max(
            5,
            (int) config(
                'modal-chat.timeout',
                120
            )
        );
    }

    private function connectTimeout(): int
    {
        return max(
            1,
            (int) config(
                'modal-chat.connect_timeout',
                10
            )
        );
    }

    private function maxTokens(): int
    {
        return max(
            16,
            min(
                8192,
                (int) config(
                    'modal-chat.max_tokens',
                    1200
                )
            )
        );
    }

    private function temperature(): float
    {
        return max(
            0.0,
            min(
                2.0,
                (float) config(
                    'modal-chat.temperature',
                    0.7
                )
            )
        );
    }
}
