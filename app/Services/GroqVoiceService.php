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
    /**
     * Maximale audiogrootte die vanuit de browser naar Laravel mag komen.
     *
     * 25 MB.
     */
    public const MAX_AUDIO_BYTES = 25 * 1024 * 1024;

    /**
     * Laatste HTTP-status die Groq binnen het huidige request teruggaf.
     */
    private ?int $lastStatus = null;

    /**
     * Laatste Retry-After waarde in seconden.
     */
    private ?int $lastRetryAfter = null;

    /**
     * Controleert of Groq Voice minimaal geconfigureerd is.
     */
    public function isConfigured(): bool
    {
        return $this->apiKey() !== ''
            && $this->endpoint() !== ''
            && $this->modelName() !== '';
    }

    /**
     * Geeft het ingestelde Whisper-model terug.
     */
    public function modelName(): string
    {
        return trim(
            (string) config(
                'groq-chat.voice.model',
                'whisper-large-v3-turbo'
            )
        );
    }

    /**
     * Laatste HTTP-status van Groq.
     */
    public function lastStatus(): ?int
    {
        return $this->lastStatus;
    }

    /**
     * Retry-After in seconden wanneer Groq rate-limiting toepast.
     */
    public function lastRetryAfterSeconds(): ?int
    {
        return $this->lastRetryAfter;
    }

    /**
     * Zet één browseropname om naar tekst via Groq Whisper.
     */
    public function transcribe(
        UploadedFile $audio,
        ?string $languageOverride = null
    ): string {
        $this->resetMetadata();

        $this->assertConfigured();

        $this->assertAudio(
            $audio
        );

        $absolutePath =
            $audio->getRealPath();

        if (
            ! is_string($absolutePath)
            || $absolutePath === ''
            || ! is_file($absolutePath)
        ) {
            throw ValidationException::withMessages([
                'audio' =>
                    'De opgenomen audio kon niet worden gelezen.',
            ]);
        }

        $stream =
            @fopen(
                $absolutePath,
                'rb'
            );

        if ($stream === false) {
            throw ValidationException::withMessages([
                'audio' =>
                    'De opgenomen audio kon niet worden geopend.',
            ]);
        }

        $fileName =
            $this->safeAudioName(
                $audio->getClientOriginalName(),
                $audio->getMimeType()
            );

        $payload = [
            'model' =>
                $this->modelName(),

            'response_format' =>
                'json',

            'temperature' =>
                '0',
        ];

        /*
         * Wanneer language leeg is, mag Whisper zelf de taal detecteren.
         */
        $language =
            $this->resolveLanguage(
                $languageOverride
            );

        if ($language !== '') {
            $payload['language'] =
                $language;
        }

        try {
            $response =
                Http::acceptJson()
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
                    ->attach(
                        'file',
                        $stream,
                        $fileName
                    )
                    ->post(
                        $this->endpoint(),
                        $payload
                    );
        } catch (
            ConnectionException $exception
        ) {
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
                fclose(
                    $stream
                );
            }
        }

        $this->rememberMetadata(
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
                'Groq gaf een ongeldig voice-antwoord terug.',
                502
            );
        }

        $text =
            trim(
                (string) (
                    $data['text']
                    ?? ''
                )
            );

        if ($text === '') {
            throw ValidationException::withMessages([
                'audio' =>
                    'Ik hoorde geen duidelijke spraak. Probeer opnieuw.',
            ]);
        }

        return $text;
    }

    /**
     * Valideert de tijdelijke audio-upload extra server-side.
     */
    private function assertAudio(
        UploadedFile $audio
    ): void {
        if (! $audio->isValid()) {
            throw ValidationException::withMessages([
                'audio' =>
                    'De audio-upload is ongeldig.',
            ]);
        }

        $size =
            (int) $audio->getSize();

        if ($size < 1) {
            throw ValidationException::withMessages([
                'audio' =>
                    'De audio-opname is leeg.',
            ]);
        }

        if (
            $size >
            self::MAX_AUDIO_BYTES
        ) {
            throw ValidationException::withMessages([
                'audio' =>
                    'De audio-opname is te groot. Neem een kortere opname op.',
            ]);
        }

        $mimeType =
            strtolower(
                trim(
                    (string) $audio->getMimeType()
                )
            );

        /*
         * MediaRecorder verschilt per browser:
         *
         * Chrome / Edge:
         * - audio/webm
         *
         * Firefox:
         * - audio/ogg
         *
         * Safari / iPhone:
         * - audio/mp4 / video/mp4
         *
         * Daarnaast accepteren we gangbare uploadformaten.
         */
        $allowedMimeTypes = [
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
            'audio/x-flac',

            'audio/aac',
            'audio/x-aac',

            /*
             * Sommige browsers/PHP-installaties herkennen een tijdelijke
             * MediaRecorder-upload alleen als octet-stream.
             */
            'application/octet-stream',
        ];

        if (
            $mimeType !== ''
            && ! in_array(
                $mimeType,
                $allowedMimeTypes,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'audio' =>
                    'Dit audioformaat wordt niet ondersteund.',
            ]);
        }
    }

    /**
     * Maakt een veilige bestandsnaam voor de multipart upload naar Groq.
     */
    private function safeAudioName(
        string $originalName,
        ?string $mimeType
    ): string {
        $originalName =
            trim(
                $originalName
            );

        if (
            $originalName !== ''
            && str_contains(
                $originalName,
                '.'
            )
        ) {
            $safeName =
                preg_replace(
                    '/[^A-Za-z0-9._-]+/',
                    '-',
                    basename(
                        $originalName
                    )
                );

            if (
                is_string($safeName)
                && $safeName !== ''
            ) {
                return $safeName;
            }
        }

        return 'mashal-voice.'
            . $this->extensionFromMime(
                $mimeType
            );
    }

    /**
     * Geeft een passende extensie terug voor browser-audio.
     */
    private function extensionFromMime(
        ?string $mimeType
    ): string {
        $mimeType =
            strtolower(
                trim(
                    (string) $mimeType
                )
            );

        return match ($mimeType) {
            'audio/ogg',
            'application/ogg' =>
                'ogg',

            'audio/mp4',
            'video/mp4',
            'audio/m4a',
            'audio/x-m4a' =>
                'm4a',

            'audio/mpeg',
            'audio/mp3' =>
                'mp3',

            'audio/wav',
            'audio/x-wav' =>
                'wav',

            'audio/flac',
            'audio/x-flac' =>
                'flac',

            'audio/aac',
            'audio/x-aac' =>
                'aac',

            default =>
                'webm',
        };
    }

    /**
     * Zet Groq HTTP-fouten om naar duidelijke applicatiefouten.
     */
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
                $this->lastRetryAfter
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
                'De Groq API-key is ongeldig of niet meer actief.',
                401
            );
        }

        if ($status === 403) {
            throw new RuntimeException(
                'Groq heeft de voice-request geweigerd. Controleer de rechten van de API-key.',
                403
            );
        }

        if ($status === 404) {
            throw new RuntimeException(
                'Het ingestelde Groq Whisper-model of voice-endpoint bestaat niet.',
                404
            );
        }

        if ($status === 413) {
            throw new RuntimeException(
                'De voice-opname is te groot voor de Groq Speech-to-Text request.',
                413
            );
        }

        if ($status === 400) {
            throw new RuntimeException(
                'Groq kon de audio niet verwerken.'
                . (
                    $providerMessage !== ''
                        ? ' ' . $providerMessage
                        : ''
                ),
                400
            );
        }

        if ($status >= 500) {
            throw new RuntimeException(
                'Groq Speech-to-Text is tijdelijk niet beschikbaar.',
                $status
            );
        }

        throw new RuntimeException(
            'Groq kon de voice-request niet verwerken.'
            . (
                $providerMessage !== ''
                    ? ' ' . $providerMessage
                    : ''
            ),
            $status >= 400
            && $status <= 599
                ? $status
                : 502
        );
    }

    /**
     * Leest een korte foutmelding uit een Groq JSON-error response.
     */
    private function providerErrorMessage(
        Response $response
    ): string {
        $data =
            $response->json();

        if (! is_array($data)) {
            return '';
        }

        $message =
            data_get(
                $data,
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
         * Houd serverlogs en exceptions beheersbaar.
         */
        return mb_substr(
            $message,
            0,
            700
        );
    }

    /**
     * Bewaart status/rate-limit informatie voor de controller.
     */
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

    /**
     * Retry-After kan seconden of een HTTP-datum bevatten.
     */
    private function parseRetryAfter(
        ?string $value
    ): ?int {
        $value =
            trim(
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

    /**
     * Controleert de config voordat er een externe request wordt verstuurd.
     */
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

    private function resolveLanguage(
        ?string $languageOverride
    ): string {
        if ($languageOverride !== null) {
            $languageOverride = strtolower(
                trim(
                    $languageOverride
                )
            );

            if (
                $languageOverride === ''
                || $languageOverride === 'auto'
            ) {
                return '';
            }

            if (
                in_array(
                    $languageOverride,
                    ['nl', 'en', 'ur'],
                    true
                )
            ) {
                return $languageOverride;
            }
        }

        return strtolower(
            $this->language()
        );
    }

    private function language(): string
    {
        return trim(
            (string) config(
                'groq-chat.voice.language',
                ''
            )
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
