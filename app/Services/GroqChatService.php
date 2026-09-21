<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class GroqVoiceService
{
    public const MAX_AUDIO_BYTES = 25 * 1024 * 1024;

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
                'groq-chat.voice.model',
                'whisper-large-v3-turbo'
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

    public function transcribe(UploadedFile $audio): string
    {
        $this->resetMetadata();
        $this->assertConfigured();
        $this->assertAudio($audio);

        $path = $audio->getRealPath();

        if (
            ! is_string($path)
            || $path === ''
            || ! is_file($path)
        ) {
            throw ValidationException::withMessages([
                'audio' => 'De opgenomen audio kon niet worden gelezen.',
            ]);
        }

        $stream = @fopen($path, 'rb');

        if ($stream === false) {
            throw ValidationException::withMessages([
                'audio' => 'De opgenomen audio kon niet worden geopend.',
            ]);
        }

        $fileName = $this->safeAudioName(
            $audio->getClientOriginalName(),
            $audio->getMimeType()
        );

        $payload = [
            'model' => $this->modelName(),
            'response_format' => 'json',
            'temperature' => '0',
        ];

        $language = trim(
            (string) config(
                'groq-chat.voice.language',
                ''
            )
        );

        if ($language !== '') {
            $payload['language'] = $language;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($this->apiKey())
                ->connectTimeout($this->connectTimeout())
                ->timeout($this->timeout())
                ->withHeaders([
                    'User-Agent' => 'Mashal-Studio/1.0',
                ])
                ->attach(
                    'file',
                    $stream,
                    $fileName
                )
                ->post(
                    $this->endpoint(),
                    $payload
                );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Groq Speech-to-Text kon niet worden bereikt.',
                503,
                $exception
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'De voice-opname kon niet naar Groq worden gestuurd.',
                503,
                $exception
            );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $this->rememberMetadata($response);

        if (! $response->successful()) {
            $this->throwForFailedResponse($response);
        }

        $text = trim(
            (string) data_get(
                $response->json(),
                'text',
                ''
            )
        );

        if ($text === '') {
            throw ValidationException::withMessages([
                'audio' => 'Ik hoorde geen duidelijke spraak. Probeer opnieuw.',
            ]);
        }

        return $text;
    }

    private function assertAudio(UploadedFile $audio): void
    {
        if (! $audio->isValid()) {
            throw ValidationException::withMessages([
                'audio' => 'De audio-upload is ongeldig.',
            ]);
        }

        $size = (int) $audio->getSize();

        if ($size < 1) {
            throw ValidationException::withMessages([
                'audio' => 'De audio-opname is leeg.',
            ]);
        }

        if ($size > self::MAX_AUDIO_BYTES) {
            throw ValidationException::withMessages([
                'audio' => 'De audio-opname is te groot.',
            ]);
        }

        $mime = strtolower(
            trim(
                (string) $audio->getMimeType()
            )
        );

        $allowed = [
            'audio/webm',
            'video/webm',
            'audio/ogg',
            'application/ogg',
            'audio/mp4',
            'video/mp4',
            'audio/m4a',
            'audio/x-m4a',
            'audio/mpeg',
            'audio/mp3',
            'audio/wav',
            'audio/x-wav',
            'audio/flac',
            'application/octet-stream',
        ];

        if (
            $mime !== ''
            && ! in_array(
                $mime,
                $allowed,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'audio' => 'Dit audioformaat wordt niet ondersteund.',
            ]);
        }
    }

    private function safeAudioName(
        string $originalName,
        ?string $mime
    ): string {
        $name = trim($originalName);

        if (
            $name !== ''
            && str_contains($name, '.')
        ) {
            return preg_replace(
                '/[^A-Za-z0-9._-]+/',
                '-',
                basename($name)
            ) ?: 'voice.webm';
        }

        $extension = match (
            strtolower(
                trim(
                    (string) $mime
                )
            )
        ) {
            'audio/ogg',
            'application/ogg' => 'ogg',

            'audio/mp4',
            'video/mp4',
            'audio/m4a',
            'audio/x-m4a' => 'm4a',

            'audio/mpeg',
            'audio/mp3' => 'mp3',

            'audio/wav',
            'audio/x-wav' => 'wav',

            'audio/flac' => 'flac',

            default => 'webm',
        };

        return 'mashal-voice.' . $extension;
    }

    private function throwForFailedResponse(Response $response): never
    {
        $status = $response->status();

        if ($status === 429) {
            $retryAfter = $this->lastRetryAfter
                ?? $this->fallbackCooldown();

            throw new RuntimeException(
                'Groq Speech-to-Text rate limit bereikt. Probeer over '
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
                'Groq heeft de voice-request geweigerd.',
                403
            );
        }

        if ($status === 404) {
            throw new RuntimeException(
                'Het ingestelde Groq Whisper-model bestaat niet.',
                404
            );
        }

        if ($status === 400) {
            $providerMessage = trim(
                (string) data_get(
                    $response->json(),
                    'error.message',
                    ''
                )
            );

            throw new RuntimeException(
                'Groq kon de audio niet verwerken.'
                . (
                    $providerMessage !== ''
                        ? ' ' . mb_substr($providerMessage, 0, 700)
                        : ''
                ),
                400
            );
        }

        throw new RuntimeException(
            'Groq kon de voice-request niet verwerken.',
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
                'GROQ_VOICE_ENDPOINT ontbreekt.',
                500
            );
        }

        if ($this->modelName() === '') {
            throw new RuntimeException(
                'GROQ_VOICE_MODEL ontbreekt.',
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
                    'groq-chat.voice.endpoint',
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
                180,
                (int) config(
                    'groq-chat.voice.timeout',
                    60
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
