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

    /**
     * Controleert of de minimale Groq-configuratie aanwezig is.
     */
    public function isConfigured(): bool
    {
        return $this->apiKey() !== ''
            && $this->endpoint() !== ''
            && $this->modelName() !== '';
    }

    /**
     * Geeft het ingestelde model terug.
     */
    public function modelName(): string
    {
        return trim(
            (string) config(
                'groq-chat.model',
                ''
            )
        );
    }

    /**
     * Laatste HTTP-status van Groq binnen dit request.
     */
    public function lastStatus(): ?int
    {
        return $this->lastStatus;
    }

    /**
     * Retry-After in seconden wanneer Groq HTTP 429 teruggeeft.
     */
    public function lastRetryAfterSeconds(): ?int
    {
        return $this->lastRetryAfter;
    }

    /**
     * Stuurt een OpenAI-compatible chat-completion request naar Groq.
     *
     * @param array<int, array{role:string, content:string}> $messages
     *
     * @return array{
     *     message:string,
     *     model:string,
     *     usage:array<string, mixed>|null
     * }
     */
    public function chat(
        array $messages
    ): array {
        $this->resetResponseMetadata();

        $this->assertConfigured();

        $normalizedMessages =
            $this->normalizeMessages(
                $messages
            );

        if ($normalizedMessages === []) {
            throw ValidationException::withMessages([
                'message' =>
                    'Er is geen geldige chatinhoud om naar Groq te sturen.',
            ]);
        }

        $payload = [
            'model' =>
                $this->modelName(),

            'messages' =>
                $normalizedMessages,

            'temperature' =>
                $this->temperature(),

            'max_completion_tokens' =>
                $this->maxCompletionTokens(),

            'stream' =>
                false,
        ];

        try {
            $response =
                Http::asJson()
                    ->acceptJson()
                    ->withToken(
                        $this->apiKey()
                    )
                    ->connectTimeout(
                        $this->connectTimeout()
                    )
                    ->timeout(
                        $this->timeout()
                    )
                    ->withHeaders([
                        'User-Agent' =>
                            'Mashal-Studio/1.0',
                    ])
                    ->post(
                        $this->endpoint(),
                        $payload
                    );
        } catch (
            ConnectionException $exception
        ) {
            throw new RuntimeException(
                'Groq kon niet worden bereikt. Controleer de internetverbinding en probeer het opnieuw.',
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

        $this->rememberResponseMetadata(
            $response
        );

        if (! $response->successful()) {
            $this->throwForFailedResponse(
                $response
            );
        }

        $data =
            $response->json();

        if (! is_array($data)) {
            throw new RuntimeException(
                'Groq gaf een ongeldig antwoord terug.',
                502
            );
        }

        $message =
            $this->extractAssistantMessage(
                $data
            );

        if ($message === '') {
            throw new RuntimeException(
                'Groq gaf geen bruikbaar AI-antwoord terug.',
                502
            );
        }

        $model =
            trim(
                (string) (
                    $data['model'] ??
                    $this->modelName()
                )
            );

        $usage =
            isset($data['usage']) &&
            is_array($data['usage'])
                ? $data['usage']
                : null;

        return [
            'message' => $message,
            'model' => $model,
            'usage' => $usage,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     *
     * @return array<int, array{role:string, content:string}>
     */
    private function normalizeMessages(
        array $messages
    ): array {
        $normalized = [];

        foreach ($messages as $message) {
            if (! is_array($message)) {
                continue;
            }

            $role =
                trim(
                    (string) (
                        $message['role'] ??
                        ''
                    )
                );

            $content =
                trim(
                    (string) (
                        $message['content'] ??
                        ''
                    )
                );

            if (
                ! in_array(
                    $role,
                    [
                        'system',
                        'user',
                        'assistant',
                    ],
                    true
                )
            ) {
                continue;
            }

            if ($content === '') {
                continue;
            }

            $normalized[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractAssistantMessage(
        array $data
    ): string {
        $content =
            data_get(
                $data,
                'choices.0.message.content'
            );

        if (is_string($content)) {
            return trim(
                $content
            );
        }

        /*
         * Defensieve fallback wanneer een provider ooit content
         * als array met tekstblokken teruggeeft.
         */
        if (is_array($content)) {
            $parts = [];

            foreach ($content as $part) {
                if (is_string($part)) {
                    $parts[] = $part;

                    continue;
                }

                if (
                    is_array($part) &&
                    isset($part['text']) &&
                    is_string($part['text'])
                ) {
                    $parts[] =
                        $part['text'];
                }
            }

            return trim(
                implode(
                    "\n",
                    $parts
                )
            );
        }

        return '';
    }

    private function throwForFailedResponse(
        Response $response
    ): never {
        $status =
            $response->status();

        $providerMessage =
            $this->providerErrorMessage(
                $response
            );

        if ($status === 429) {
            $retryAfter =
                $this->lastRetryAfter ??
                $this->fallbackRateLimitCooldown();

            throw new RuntimeException(
                'Groq rate limit bereikt. Probeer over ' .
                $retryAfter .
                ' seconden opnieuw.' .
                (
                    $providerMessage !== ''
                        ? ' ' . $providerMessage
                        : ''
                ),
                429
            );
        }

        if ($status === 401) {
            throw new RuntimeException(
                'De Groq API-key is ongeldig of niet meer actief.',
                401
            );
        }

        if ($status === 403) {
            throw new RuntimeException(
                'Groq heeft deze request geweigerd. Controleer de rechten van de API-key en het project.',
                403
            );
        }

        if ($status === 400) {
            throw new RuntimeException(
                'Groq heeft de request als ongeldig afgewezen.' .
                (
                    $providerMessage !== ''
                        ? ' ' . $providerMessage
                        : ''
                ),
                400
            );
        }

        if ($status === 404) {
            throw new RuntimeException(
                'Het ingestelde Groq-model of endpoint kon niet worden gevonden.',
                404
            );
        }

        if ($status >= 500) {
            throw new RuntimeException(
                'Groq is tijdelijk niet beschikbaar. Probeer het later opnieuw.',
                $status
            );
        }

        throw new RuntimeException(
            'Groq kon de AI-request niet verwerken.' .
            (
                $providerMessage !== ''
                    ? ' ' . $providerMessage
                    : ''
            ),
            $status > 0
                ? $status
                : 502
        );
    }

    private function providerErrorMessage(
        Response $response
    ): string {
        $json =
            $response->json();

        if (! is_array($json)) {
            return '';
        }

        $message =
            data_get(
                $json,
                'error.message'
            );

        if (! is_string($message)) {
            return '';
        }

        $message =
            trim(
                $message
            );

        if ($message === '') {
            return '';
        }

        /*
         * Providertekst komt alleen in server-side exceptions/logs terecht.
         * We limiteren de lengte zodat logs niet onnodig groot worden.
         */
        return mb_substr(
            $message,
            0,
            1000
        );
    }

    private function rememberResponseMetadata(
        Response $response
    ): void {
        $this->lastStatus =
            $response->status();

        $this->lastRetryAfter =
            $this->parseRetryAfter(
                $response->header(
                    'Retry-After'
                )
            );
    }

    private function resetResponseMetadata(): void
    {
        $this->lastStatus = null;
        $this->lastRetryAfter = null;
    }

    private function parseRetryAfter(
        ?string $value
    ): ?int {
        if ($value === null) {
            return null;
        }

        $value =
            trim(
                $value
            );

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return max(
                1,
                min(
                    3600,
                    (int) $value
                )
            );
        }

        /*
         * HTTP Retry-After mag ook een datum zijn.
         */
        $timestamp =
            strtotime(
                $value
            );

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

    private function fallbackRateLimitCooldown(): int
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
