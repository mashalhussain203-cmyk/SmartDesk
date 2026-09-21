<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AzureSpeechService
{
    private ?int $lastStatus = null;

    public function isConfigured(): bool
    {
        return $this->key() !== ''
            && $this->region() !== '';
    }

    public function lastStatus(): ?int
    {
        return $this->lastStatus;
    }

    public function synthesize(
        string $text,
        string $locale
    ): string {
        $this->lastStatus = null;

        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Azure Speech is nog niet geconfigureerd.',
                503
            );
        }

        $cleanText = trim($text);

        if ($cleanText === '') {
            throw new RuntimeException(
                'Er is geen tekst om uit te spreken.',
                422
            );
        }

        if (mb_strlen($cleanText) > 5000) {
            throw new RuntimeException(
                'Het voice-antwoord is te lang om in één keer uit te spreken.',
                422
            );
        }

        $resolvedLocale = $this->normalizeLocale(
            $locale
        );

        $voiceName = $this->voiceForLocale(
            $resolvedLocale
        );

        $escapedText = htmlspecialchars(
            $cleanText,
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );

        $ssml =
            '<speak version="1.0" '
            . 'xmlns="http://www.w3.org/2001/10/synthesis" '
            . 'xml:lang="' . $resolvedLocale . '">'
            . '<voice name="' . $voiceName . '">'
            . $escapedText
            . '</voice>'
            . '</speak>';

        try {
            $response =
                Http::connectTimeout(
                    $this->connectTimeout()
                )
                    ->timeout(
                        $this->timeout()
                    )
                    ->withHeaders([
                        'Ocp-Apim-Subscription-Key' =>
                            $this->key(),

                        'X-Microsoft-OutputFormat' =>
                            $this->outputFormat(),

                        'User-Agent' =>
                            'Mashal-Studio/1.0',

                        'Accept' =>
                            'audio/mpeg',
                    ])
                    ->withBody(
                        $ssml,
                        'application/ssml+xml'
                    )
                    ->post(
                        $this->endpoint()
                    );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Azure Speech kon niet worden bereikt.',
                503,
                $exception
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Het voice-antwoord kon niet worden gegenereerd.',
                503,
                $exception
            );
        }

        $this->lastStatus =
            $response->status();

        if (! $response->successful()) {
            $this->throwForFailedResponse(
                $response
            );
        }

        $audio =
            $response->body();

        if ($audio === '') {
            throw new RuntimeException(
                'Azure Speech gaf geen audio terug.',
                502
            );
        }

        return $audio;
    }

    private function throwForFailedResponse(
        Response $response
    ): never {
        $status =
            $response->status();

        $message =
            trim(
                $response->body()
            );

        if ($status === 401 || $status === 403) {
            throw new RuntimeException(
                'Azure Speech authenticatie is mislukt. Controleer AZURE_SPEECH_KEY en AZURE_SPEECH_REGION.',
                503
            );
        }

        if ($status === 429) {
            throw new RuntimeException(
                'Azure Speech heeft tijdelijk de limiet bereikt.',
                429
            );
        }

        if ($status >= 500) {
            throw new RuntimeException(
                'Azure Speech is tijdelijk niet beschikbaar.',
                503
            );
        }

        throw new RuntimeException(
            'Azure Speech kon geen audio genereren'
            . ($message !== ''
                ? ': ' . mb_substr($message, 0, 500)
                : '.'),
            $status >= 400 && $status < 600
                ? $status
                : 502
        );
    }

    private function normalizeLocale(
        string $locale
    ): string {
        $value =
            strtolower(
                trim($locale)
            );

        if (
            $value === 'ur'
            || str_starts_with(
                $value,
                'ur-'
            )
        ) {
            return 'ur-PK';
        }

        if (
            $value === 'en'
            || str_starts_with(
                $value,
                'en-'
            )
        ) {
            return 'en-US';
        }

        return 'nl-NL';
    }

    private function voiceForLocale(
        string $locale
    ): string {
        return match ($locale) {
            'ur-PK' =>
                trim(
                    (string) config(
                        'speech.azure.voices.ur',
                        'ur-PK-UzmaNeural'
                    )
                ),

            'en-US' =>
                trim(
                    (string) config(
                        'speech.azure.voices.en',
                        'en-US-JennyNeural'
                    )
                ),

            default =>
                trim(
                    (string) config(
                        'speech.azure.voices.nl',
                        'nl-NL-FennaNeural'
                    )
                ),
        };
    }

    private function endpoint(): string
    {
        return sprintf(
            'https://%s.tts.speech.microsoft.com/cognitiveservices/v1',
            $this->region()
        );
    }

    private function key(): string
    {
        return trim(
            (string) config(
                'speech.azure.key',
                ''
            )
        );
    }

    private function region(): string
    {
        return strtolower(
            trim(
                (string) config(
                    'speech.azure.region',
                    ''
                )
            )
        );
    }

    private function outputFormat(): string
    {
        return trim(
            (string) config(
                'speech.azure.output_format',
                'audio-24khz-48kbitrate-mono-mp3'
            )
        );
    }

    private function timeout(): int
    {
        return max(
            5,
            min(
                120,
                (int) config(
                    'speech.azure.timeout',
                    30
                )
            )
        );
    }

    private function connectTimeout(): int
    {
        return max(
            1,
            min(
                30,
                (int) config(
                    'speech.azure.connect_timeout',
                    10
                )
            )
        );
    }
}
