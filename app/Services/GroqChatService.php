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
     * @param array<int,array{role:string,content:string}> $messages
     * @param array<string,mixed> $options
     *
     * @return array{
     *     message:string,
     *     model:string,
     *     usage:array<string,mixed>|null,
     *     mode:string,
     *     used_web:bool,
     *     used_code:bool,
     *     sources:array<int,array{title:string,url:string,content:string,score:float|null}>,
     *     tools:array<int,array{name:string,type:string}>
     * }
     */
    public function chat(
        array $messages,
        array $options = []
    ): array {
        $this->resetMetadata();
        $this->assertConfigured();

        $messages = $this->normalizeMessages(
            $messages
        );

        if ($messages === []) {
            throw ValidationException::withMessages([
                'message' =>
                    'Er is geen geldige chatinhoud om naar Groq te sturen.',
            ]);
        }

        $mode = $this->normalizeMode(
            (string) (
                $options['mode']
                ?? config(
                    'groq-chat.tools.default_mode',
                    'auto'
                )
            )
        );

        $messages = $this->applyModePrompt(
            $messages,
            $mode
        );

        $payload = [
            'model' => $this->modelName(),
            'messages' => $messages,
            'temperature' => $this->temperature(),
            'max_completion_tokens' =>
                $this->maxCompletionTokens(),
            'stream' => false,
        ];

        /*
         * Do not send citation_options for GPT-OSS.
         *
         * Groq's Browser Search works without this flag. Explicitly enabling
         * citations causes openai/gpt-oss-20b to return HTTP 400:
         * "model openai/gpt-oss-20b does not support citations".
         *
         * Browser-search source data is still collected from executed_tools
         * below and can still be shown by the Mashal AI UI.
         */

        $this->applyBuiltInTools(
            $payload,
            $mode
        );

        $response = $this->sendPayload(
            $payload,
            $mode
        );

        $this->rememberMetadata($response);

        /*
         * Auto mode is allowed to fall back to a normal chat request when
         * Groq rejects an experimental built-in-tool combination. This keeps
         * ordinary chat working while explicit Web/Research/Code modes still
         * fail loudly instead of pretending a tool was used.
         */
        if (
            ! $response->successful()
            && $response->status() === 400
            && $mode === 'auto'
            && isset($payload['tools'])
        ) {
            $fallbackPayload = $payload;

            unset(
                $fallbackPayload['tools'],
                $fallbackPayload['tool_choice'],
                $fallbackPayload['parallel_tool_calls'],
                $fallbackPayload['reasoning_format']
            );

            $response = $this->sendPayload(
                $fallbackPayload,
                'plain'
            );

            $this->rememberMetadata($response);
        }

        if (! $response->successful()) {
            $this->throwForFailedResponse(
                $response
            );
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

        $executedTools = data_get(
            $data,
            'choices.0.message.executed_tools',
            []
        );

        if (! is_array($executedTools)) {
            $executedTools = [];
        }

        $sources = $this->extractSources(
            $executedTools
        );

        $toolSummary = $this->extractToolSummary(
            $executedTools
        );

        return [
            'message' => $message,

            'model' => trim(
                (string) (
                    $data['model']
                    ?? $this->modelName()
                )
            ),

            'usage' =>
                isset($data['usage'])
                && is_array($data['usage'])
                    ? $data['usage']
                    : null,

            'mode' => $mode,

            'used_web' =>
                $this->executedWebSearch(
                    $executedTools,
                    $sources
                ),

            'used_code' =>
                $this->executedCode(
                    $executedTools
                ),

            'sources' => $sources,
            'tools' => $toolSummary,
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function applyBuiltInTools(
        array &$payload,
        string $mode
    ): void {
        if (
            $mode === 'plain'
            || ! $this->toolsEnabled()
        ) {
            return;
        }

        $tools = [];

        if (
            in_array(
                $mode,
                ['auto', 'web', 'research'],
                true
            )
            && $this->browserSearchEnabled()
        ) {
            if (
                $mode !== 'auto'
                && ! $this->supportsBrowserSearch()
            ) {
                throw new RuntimeException(
                    'Het ingestelde Groq-model ondersteunt Browser Search niet. Gebruik openai/gpt-oss-20b of openai/gpt-oss-120b.',
                    400
                );
            }

            if ($this->supportsBrowserSearch()) {
                $tools[] = [
                    'type' => 'browser_search',
                ];
            }
        }

        if (
            in_array(
                $mode,
                ['auto', 'code'],
                true
            )
            && $this->codeInterpreterEnabled()
        ) {
            if (
                $mode === 'code'
                && ! $this->supportsCodeInterpreter()
            ) {
                throw new RuntimeException(
                    'Het ingestelde Groq-model ondersteunt Code Interpreter niet. Gebruik openai/gpt-oss-20b of openai/gpt-oss-120b.',
                    400
                );
            }

            if ($this->supportsCodeInterpreter()) {
                $tools[] = [
                    'type' => 'code_interpreter',
                ];
            }
        }

        if ($tools === []) {
            return;
        }

        $payload['tools'] = $tools;

        $payload['tool_choice'] =
            in_array(
                $mode,
                ['web', 'research', 'code'],
                true
            )
                ? 'required'
                : 'auto';

        /*
         * GPT-OSS built-in tools do not support parallel tool calls. Groq's
         * reasoning guidance also requires parsed/hidden reasoning when tool
         * calling is active. Setting both explicitly avoids 400 responses
         * caused by incompatible defaults.
         */
        $payload['parallel_tool_calls'] = false;
        $payload['reasoning_format'] =
            $this->reasoningFormat();
    }

    /**
     * @param array<int,array{role:string,content:string}> $messages
     * @return array<int,array{role:string,content:string}>
     */
    private function applyModePrompt(
        array $messages,
        string $mode
    ): array {
        $prompt = match ($mode) {
            'web' =>
                'Internet mode is active. Use Browser Search before answering. Prefer current primary or authoritative sources, mention relevant dates, and do not claim something is current unless the search supports it.',

            'research' =>
                'Deep Research mode is active. Use Browser Search before answering. Investigate the question carefully, compare multiple relevant sources when available, distinguish confirmed facts from uncertainty, include concrete dates, and provide a clear source-grounded synthesis. Do not fabricate sources.',

            'code' =>
                'Code/Calculation mode is active. Use the code interpreter to calculate, test, transform, or verify the answer when useful. Clearly separate computed results from assumptions.',

            'auto' =>
                'Automatic tools are available. Use Browser Search when the answer requires fresh/current web information and use the code interpreter when calculation or executable verification materially improves accuracy. Never claim to have searched or executed code unless the corresponding tool was actually used. Do not send private uploaded document contents to web search unless the user explicitly asks to compare them with public web information.',

            default => '',
        };

        if ($prompt === '') {
            return $messages;
        }

        $insertAt = 0;

        while (
            isset($messages[$insertAt])
            && ($messages[$insertAt]['role'] ?? '') === 'system'
        ) {
            $insertAt++;
        }

        array_splice(
            $messages,
            $insertAt,
            0,
            [[
                'role' => 'system',
                'content' => $prompt,
            ]]
        );

        return $messages;
    }

    /**
     * @param array<int,mixed> $executedTools
     * @return array<int,array{title:string,url:string,content:string,score:float|null}>
     */
    private function extractSources(
        array $executedTools
    ): array {
        $sources = [];
        $seen = [];

        foreach ($executedTools as $tool) {
            if (! is_array($tool)) {
                continue;
            }

            $results = data_get(
                $tool,
                'search_results.results',
                []
            );

            if (! is_array($results)) {
                continue;
            }

            foreach ($results as $result) {
                if (! is_array($result)) {
                    continue;
                }

                $url = trim(
                    (string) ($result['url'] ?? '')
                );

                if (
                    $url === ''
                    || ! filter_var(
                        $url,
                        FILTER_VALIDATE_URL
                    )
                    || isset($seen[$url])
                ) {
                    continue;
                }

                $scheme = strtolower(
                    (string) parse_url(
                        $url,
                        PHP_URL_SCHEME
                    )
                );

                if (! in_array(
                    $scheme,
                    ['http', 'https'],
                    true
                )) {
                    continue;
                }

                $seen[$url] = true;

                $sources[] = [
                    'title' => trim(
                        (string) (
                            $result['title']
                            ?? $url
                        )
                    ),

                    'url' => $url,

                    'content' => trim(
                        (string) (
                            $result['content']
                            ?? ''
                        )
                    ),

                    'score' =>
                        isset($result['score'])
                        && is_numeric($result['score'])
                            ? (float) $result['score']
                            : null,
                ];

                if (
                    count($sources)
                    >= $this->maxSources()
                ) {
                    return $sources;
                }
            }
        }

        return $sources;
    }

    /**
     * @param array<int,mixed> $executedTools
     * @return array<int,array{name:string,type:string}>
     */
    private function extractToolSummary(
        array $executedTools
    ): array {
        $summary = [];

        foreach ($executedTools as $tool) {
            if (! is_array($tool)) {
                continue;
            }

            $name = trim(
                (string) ($tool['name'] ?? '')
            );

            $type = trim(
                (string) ($tool['type'] ?? '')
            );

            if ($name === '' && $type === '') {
                continue;
            }

            $summary[] = [
                'name' => $name !== ''
                    ? $name
                    : $type,
                'type' => $type,
            ];
        }

        return $summary;
    }

    /**
     * @param array<int,mixed> $executedTools
     * @param array<int,mixed> $sources
     */
    private function executedWebSearch(
        array $executedTools,
        array $sources
    ): bool {
        if ($sources !== []) {
            return true;
        }

        foreach ($executedTools as $tool) {
            if (! is_array($tool)) {
                continue;
            }

            $haystack = strtolower(
                trim(
                    (string) ($tool['name'] ?? '')
                    . ' '
                    . (string) ($tool['type'] ?? '')
                )
            );

            if (
                str_contains($haystack, 'search')
                || str_contains($haystack, 'browser')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,mixed> $executedTools
     */
    private function executedCode(
        array $executedTools
    ): bool {
        foreach ($executedTools as $tool) {
            if (! is_array($tool)) {
                continue;
            }

            $codeResults = $tool['code_results'] ?? null;

            if (
                is_array($codeResults)
                && $codeResults !== []
            ) {
                return true;
            }

            $haystack = strtolower(
                trim(
                    (string) ($tool['name'] ?? '')
                    . ' '
                    . (string) ($tool['type'] ?? '')
                )
            );

            if (
                str_contains($haystack, 'python')
                || str_contains($haystack, 'code')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,array<string,mixed>> $messages
     * @return array<int,array{role:string,content:string}>
     */
    private function normalizeMessages(
        array $messages
    ): array {
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

    private function normalizeMode(
        string $mode
    ): string {
        $value = strtolower(
            trim($mode)
        );

        return in_array(
            $value,
            [
                'auto',
                'web',
                'research',
                'code',
                'plain',
            ],
            true
        )
            ? $value
            : 'auto';
    }

    private function supportsBrowserSearch(): bool
    {
        return in_array(
            strtolower($this->modelName()),
            [
                'openai/gpt-oss-20b',
                'openai/gpt-oss-120b',
                'openai/gpt-oss-safeguard-20b',
            ],
            true
        );
    }

    private function supportsCodeInterpreter(): bool
    {
        return in_array(
            strtolower($this->modelName()),
            [
                'openai/gpt-oss-20b',
                'openai/gpt-oss-120b',
            ],
            true
        );
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function sendPayload(
        array $payload,
        string $mode
    ): Response {
        try {
            return Http::asJson()
                ->acceptJson()
                ->withToken($this->apiKey())
                ->connectTimeout(
                    $this->connectTimeout()
                )
                ->timeout(
                    $mode === 'research'
                        ? $this->researchTimeout()
                        : $this->timeout()
                )
                ->withHeaders([
                    'User-Agent' =>
                        'Mashal-Studio/1.0',
                ])
                ->post(
                    $this->endpoint(),
                    $payload
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
    }

    private function throwForFailedResponse(
        Response $response
    ): never {
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
                        ? ' '
                            . mb_substr(
                                $providerMessage,
                                0,
                                900
                            )
                        : ''
                ),
                400
            );
        }

        throw new RuntimeException(
            'Groq kon de AI-request niet verwerken.',
            $status >= 400
            && $status <= 599
                ? $status
                : 502
        );
    }

    private function rememberMetadata(
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

    private function resetMetadata(): void
    {
        $this->lastStatus = null;
        $this->lastRetryAfter = null;
    }

    private function parseRetryAfter(
        ?string $value
    ): ?int {
        $value = trim(
            (string) $value
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

    private function researchTimeout(): int
    {
        return max(
            $this->timeout(),
            min(
                300,
                (int) config(
                    'groq-chat.tools.research_timeout',
                    180
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

    private function toolsEnabled(): bool
    {
        return (bool) config(
            'groq-chat.tools.enabled',
            true
        );
    }

    private function browserSearchEnabled(): bool
    {
        return (bool) config(
            'groq-chat.tools.browser_search',
            true
        );
    }

    private function codeInterpreterEnabled(): bool
    {
        return (bool) config(
            'groq-chat.tools.code_interpreter',
            true
        );
    }

    private function reasoningFormat(): string
    {
        $value = strtolower(
            trim(
                (string) config(
                    'groq-chat.tools.reasoning_format',
                    'hidden'
                )
            )
        );

        return in_array(
            $value,
            ['hidden', 'parsed'],
            true
        )
            ? $value
            : 'hidden';
    }

    private function maxSources(): int
    {
        return max(
            1,
            min(
                12,
                (int) config(
                    'groq-chat.tools.max_sources',
                    8
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
