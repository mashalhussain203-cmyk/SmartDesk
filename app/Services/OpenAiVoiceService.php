<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class OpenAiVoiceService
{
    public const MAX_AUDIO_BYTES = 25 * 1024 * 1024;

    private ?int $lastStatus = null;
    private ?int $lastRetryAfter = null;

    public function isConfigured(): bool
    {
        return trim((string) config('mashal-ai.openai.api_key', '')) !== '';
    }

    public function modelName(): string
    {
        return (string) config('mashal-ai.openai.transcription_model', 'gpt-4o-mini-transcribe');
    }

    public function lastStatus(): ?int
    {
        return $this->lastStatus;
    }

    public function lastRetryAfterSeconds(): ?int
    {
        return $this->lastRetryAfter;
    }

    public function transcribe(UploadedFile $audio, ?string $languageOverride = null): string
    {
        $this->lastStatus = null;
        $this->lastRetryAfter = null;

        if (! $this->isConfigured()) {
            throw new RuntimeException('OpenAI-spraakherkenning is niet geconfigureerd.', 503);
        }

        if (! $audio->isValid() || $audio->getSize() < 1 || $audio->getSize() > self::MAX_AUDIO_BYTES) {
            throw ValidationException::withMessages(['audio' => 'Deze audio-upload is ongeldig of te groot.']);
        }

        $path = $audio->getRealPath();
        $stream = $path ? @fopen($path, 'rb') : false;
        if ($stream === false) {
            throw ValidationException::withMessages(['audio' => 'De audio-opname kon niet worden gelezen.']);
        }

        $payload = ['model' => $this->modelName()];
        if (in_array($languageOverride, ['nl', 'en', 'ur'], true)) {
            $payload['language'] = $languageOverride;
        }

        try {
            $response = Http::acceptJson()
                ->withToken((string) config('mashal-ai.openai.api_key'))
                ->connectTimeout(10)
                ->timeout((int) config('mashal-ai.openai.timeout', 120))
                ->attach('file', $stream, basename($audio->getClientOriginalName() ?: 'speech.webm'))
                ->post('https://api.openai.com/v1/audio/transcriptions', $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('OpenAI-spraakherkenning is niet bereikbaar.', 502, $exception);
        } finally {
            fclose($stream);
        }

        $this->lastStatus = $response->status();
        $retry = $response->header('Retry-After');
        $this->lastRetryAfter = is_numeric($retry) ? (int) $retry : null;

        if (! $response->successful()) {
            throw new RuntimeException('De spraakopname kon niet worden verwerkt door OpenAI.', $response->status());
        }

        $text = trim((string) $response->json('text', ''));
        if ($text === '') {
            throw ValidationException::withMessages(['audio' => 'Er werd geen verstaanbare spraak herkend.']);
        }

        return $text;
    }
}
