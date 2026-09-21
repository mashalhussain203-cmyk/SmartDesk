<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class GroqChatService
{
    private ?int $lastStatus = null;

    private ?int $lastRetryAfter = null;

    public function isConfigured(): bool
    {
        return $this->apiKey() !== ''
            && $this->endpoint() !== ''
            && $this->modelName() !== '';
    }

    public function modelName(): string
    {
        return trim(
            (string) config(
                'groq-chat.model',
                'openai/gpt-oss-20b'
            )
        );
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
     * @param array<int, array{role:string, content:string}> $messages
     *
     * @return array{
     *     message:string,
     *     model:string,
     *     usage:array<string,mixed>|null
     * }
     */
    public function chat(array $messages): array
    {
        $this->resetMetadata();
        $this->assertConfigured();

        $messages = $this->normalizeMessages($messages);

        if ($messages === []) {
            throw ValidationException::withMessages([
                'message' => 'Er is geen geldige chatinhoud om naar Groq te sturen.',
            ]);
        }

        try {
            $response = Http::asJson()
                ->acceptJson()
                ->withToken($this->apiKey())
                ->connectTimeout($this->connectTimeout())
                ->timeout($this->timeout())
                ->withHeaders([
                    'User-Agent' => 'Mashal-Studio/1.0',
                ])
                ->post(
                    $this->endpoint(),
                    [
                        'model' => $this->modelName(),
                        'messages' => $messages,
                        'temperature' => $this->temperature(),
                        'max_completion_tokens' => $this->maxCompletionTokens(),
                        'stream' => false,
                    ]
                );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Groq kon niet worden bereikt.',
                503,
                $exception
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Er ging iets mis tijdens de verbinding met Groq.',
                503,
                $exception
            );
        }

        $this->rememberMetadata($response);

        if (! $response->successful()) {
            $this->throwForFailedResponse($response);
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException(
                'Groq gaf een ongeldig antwoord terug.',
                502
            );
        }

        $message = trim(
            (string) data_get(
                $data,
                'choices.0.message.content',
                ''
            )
        );

        if ($message === '') {
            throw new RuntimeException(
                'Groq gaf geen bruikbaar AI-antwoord terug.',
                502
            );
        }

        return [
            'message' => $message,
            'model' => trim(
                (string) (
                    $data['model']
                    ?? $this->modelName()
                )
            ),
            'usage' => isset($data['usage']) && is_array($data['usage'])
                ? $data['usage']
                : null,
        ];
    }

    /**
     * @param array<int, array<string,mixed>> $messages
     *
     * @return array<int, array{role:string,content:string}>
     */
    private function normalizeMessages(array $messages): array
    {
        $normalized = [];

        foreach ($messages as $message) {
            if (! is_array($message)) {
                continue;
            }

            $role = trim(
                (string) ($message['role'] ?? '')
            );

            $content = trim(
                (string) ($message['content'] ?? '')
            );

            if (
                ! in_array(
                    $role,
                    ['system', 'user', 'assistant'],
                    true
                )
                || $content === ''
            ) {
                continue;
            }

            $normalized[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        return $normalized;
    }

    private function throwForFailedResponse(Response $response): never
    {
        $status = $response->status();

        $providerMessage = trim(
            (string) data_get(
                $response->json(),
                'error.message',
                ''
            )
        );

        if ($status === 429) {
            $retryAfter = $this->lastRetryAfter
                ?? $this->fallbackCooldown();

            throw new RuntimeException(
                'Groq rate limit bereikt. Probeer over '
                . $retryAfter
                . ' seconden opnieuw.',
                429
            );
        }

        if ($status === 401) {
            throw new RuntimeException(
                'De Groq API-key is ongeldig of niet actief.',
                401
            );
        }

        if ($status === 403) {
            throw new RuntimeException(
                'Groq heeft deze request geweigerd.',
                403
            );
        }

        if ($status === 404) {
            throw new RuntimeException(
                'Het ingestelde Groq-model of endpoint bestaat niet.',
                404
            );
        }

        if ($status === 400) {
            throw new RuntimeException(
                'Groq heeft de request afgewezen.'
                . (
                    $providerMessage !== ''
                        ? ' ' . mb_substr($providerMessage, 0, 700)
                        : ''
                ),
                400
            );
        }

        throw new RuntimeException(
            'Groq kon de AI-request niet verwerken.',
            $status >= 400 && $status <= 599
                ? $status
                : 502
        );
    }

    private function rememberMetadata(Response $response): void
    {
        $this->lastStatus = $response->status();

        $this->lastRetryAfter = $this->parseRetryAfter(
            $response->header('Retry-After')
        );
    }

    private function resetMetadata(): void
    {
        $this->lastStatus = null;
        $this->lastRetryAfter = null;
    }

    private function parseRetryAfter(?string $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return max(
                1,
                min(3600, (int) $value)
            );
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        return max(
            1,
            min(
                3600,
                $timestamp - time()
            )
        );
    }

    private function assertConfigured(): void
    {
        if ($this->apiKey() === '') {
            throw new RuntimeException(
                'GROQ_API_KEY ontbreekt.',
                500
            );
        }

        if ($this->endpoint() === '') {
            throw new RuntimeException(
                'GROQ_CHAT_ENDPOINT ontbreekt.',
                500
            );
        }

        if ($this->modelName() === '') {
            throw new RuntimeException(
                'GROQ_CHAT_MODEL ontbreekt.',
                500
            );
        }
    }

    private function apiKey(): string
    {
        return trim(
            (string) config(
                'groq-chat.api_key',
                ''
            )
        );
    }

    private function endpoint(): string
    {
        return rtrim(
            trim(
                (string) config(
                    'groq-chat.endpoint',
                    ''
                )
            ),
            '/'
        );
    }

    private function timeout(): int
    {
        return max(
            5,
            min(
                300,
                (int) config(
                    'groq-chat.timeout',
                    120
                )
            )
        );
    }

    private function connectTimeout(): int
    {
        return max(
            1,
            min(
                60,
                (int) config(
                    'groq-chat.connect_timeout',
                    10
                )
            )
        );
    }

    private function maxCompletionTokens(): int
    {
        return max(
            1,
            min(
                65536,
                (int) config(
                    'groq-chat.max_completion_tokens',
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
                    'groq-chat.temperature',
                    0.7
                )
            )
        );
    }

    private function fallbackCooldown(): int
    {
        return max(
            1,
            min(
                300,
                (int) config(
                    'groq-chat.rate_limit_cooldown',
                    20
                )
            )
        );
    }
}
