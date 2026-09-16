<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use RuntimeException;
use Throwable;

class BrevoMailService
{
    /**
     * Verstuur één e-mail via de Brevo API.
     */
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $view,
        array $data = []
    ): void {
        $apiKey = (string) config(
            'services.brevo.api_key',
            ''
        );

        $fromEmail = (string) config(
            'services.brevo.from_email',
            ''
        );

        $fromName = (string) config(
            'services.brevo.from_name',
            'SmartDesk'
        );

        $baseUrl = rtrim(
            (string) config(
                'services.brevo.base_url',
                'https://api.brevo.com/v3'
            ),
            '/'
        );

        /*
        |--------------------------------------------------------------------------
        | Configuratie controleren
        |--------------------------------------------------------------------------
        */

        if ($apiKey === '') {
            throw new RuntimeException(
                'BREVO_API_KEY ontbreekt.'
            );
        }

        if ($fromEmail === '') {
            throw new RuntimeException(
                'BREVO_FROM_EMAIL ontbreekt.'
            );
        }

        if ($baseUrl === '') {
            throw new RuntimeException(
                'BREVO_BASE_URL ontbreekt.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | E-mailtemplate controleren
        |--------------------------------------------------------------------------
        */

        if (! View::exists($view)) {
            throw new RuntimeException(
                'E-mailtemplate [' .
                $view .
                '] bestaat niet.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Blade-template renderen
        |--------------------------------------------------------------------------
        */

        try {
            $htmlContent = View::make(
                $view,
                $data
            )->render();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'E-mailtemplate [' .
                $view .
                '] kon niet worden gerenderd: ' .
                $exception->getMessage(),
                0,
                $exception
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Brevo API aanroepen
        |--------------------------------------------------------------------------
        */

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])
                ->connectTimeout(10)
                ->timeout(20)
                ->retry(
                    2,
                    500
                )
                ->post(
                    $baseUrl . '/smtp/email',
                    [
                        'sender' => [
                            'name' => $fromName,
                            'email' => $fromEmail,
                        ],

                        'to' => [
                            [
                                'email' => $toEmail,
                                'name' => $toName,
                            ],
                        ],

                        'subject' => $subject,

                        'htmlContent' => $htmlContent,
                    ]
                );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Er kon geen verbinding met Brevo worden gemaakt: ' .
                $exception->getMessage(),
                0,
                $exception
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Brevo antwoord controleren
        |--------------------------------------------------------------------------
        */

        if ($response->failed()) {
            throw new RuntimeException(
                'Brevo kon de e-mail niet versturen. ' .
                'HTTP-status: ' .
                $response->status() .
                '. Antwoord: ' .
                $response->body()
            );
        }
    }

    /**
     * Verstuur dezelfde e-mail naar meerdere ontvangers.
     */
    public function sendToMany(
        array $recipients,
        string $subject,
        string $view,
        array $data = []
    ): void {
        foreach ($recipients as $recipient) {
            if (
                ! isset($recipient['email']) ||
                trim((string) $recipient['email']) === ''
            ) {
                continue;
            }

            $this->send(
                (string) $recipient['email'],
                (string) ($recipient['name'] ?? ''),
                $subject,
                $view,
                $data
            );
        }
    }
}