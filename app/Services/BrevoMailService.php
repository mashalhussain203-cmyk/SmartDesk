<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use RuntimeException;
use Throwable;

class BrevoMailService
{
    /**
     * Verstuur één e-mail via de Brevo HTTPS API.
     */
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $view,
        array $data = []
    ): void {
        $apiKey = trim((string) config(
            'services.brevo.api_key',
            ''
        ));

        $fromEmail = trim((string) config(
            'services.brevo.from_email',
            ''
        ));

        $fromName = trim((string) config(
            'services.brevo.from_name',
            'SmartDesk'
        ));

        $baseUrl = rtrim(trim((string) config(
            'services.brevo.base_url',
            'https://api.brevo.com/v3'
        )), '/');

        $toEmail = trim($toEmail);
        $toName = trim($toName);

        // Configuratie controleren.
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

        if (! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'BREVO_FROM_EMAIL is geen geldig e-mailadres.'
            );
        }

        if (
            ! filter_var($baseUrl, FILTER_VALIDATE_URL) ||
            strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)) !== 'https'
        ) {
            throw new RuntimeException(
                'BREVO_BASE_URL moet een geldige HTTPS-URL zijn.'
            );
        }

        if ($fromName === '') {
            $fromName = 'SmartDesk';
        }

        // Ontvanger en onderwerp controleren.
        if (! filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'De ontvanger heeft geen geldig e-mailadres.'
            );
        }

        if (trim($subject) === '') {
            throw new RuntimeException(
                'Het onderwerp van de e-mail ontbreekt.'
            );
        }

        // Blade-template controleren en renderen.
        if (! View::exists($view)) {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] bestaat niet."
            );
        }

        try {
            $htmlContent = View::make($view, $data)->render();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] kon niet worden gerenderd: " .
                $exception->getMessage(),
                0,
                $exception
            );
        }

        if (trim($htmlContent) === '') {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] levert lege inhoud op."
            );
        }

        $recipient = [
            'email' => $toEmail,
        ];

        if ($toName !== '') {
            $recipient['name'] = $toName;
        }

        // Geen automatische retry: voorkom dubbele verzending
        // wanneer Brevo de mail accepteert maar het antwoord uitblijft.
        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])
                ->acceptJson()
                ->asJson()
                ->connectTimeout(10)
                ->timeout(20)
                ->post($baseUrl . '/smtp/email', [
                    'sender' => [
                        'name' => $fromName,
                        'email' => $fromEmail,
                    ],
                    'to' => [$recipient],
                    'subject' => $subject,
                    'htmlContent' => $htmlContent,
                ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Brevo kon niet worden bereikt of reageerde niet op tijd. ' .
                'Het is niet zeker of de e-mail is geaccepteerd.',
                0,
                $exception
            );
        }

        // Deze details zijn bedoeld voor de serverlogs.
        // Toon de bezoeker alleen een algemene foutmelding.
        if (! $response->successful()) {
            throw new RuntimeException(
                'Brevo heeft de e-mail niet geaccepteerd. ' .
                'HTTP-status: ' . $response->status() . '. ' .
                'Antwoord: ' . $response->body()
            );
        }
    }

    /**
     * Verstuur afzonderlijk naar iedere ontvanger.
     *
     * Ontvangers zonder e-mailadres worden overgeslagen.
     * Bij een verzendfout stopt de verwerking met een exception.
     */
    public function sendToMany(
        array $recipients,
        string $subject,
        string $view,
        array $data = []
    ): void {
        foreach ($recipients as $recipient) {
            if (! is_array($recipient)) {
                continue;
            }

            $email = trim((string) ($recipient['email'] ?? ''));

            if ($email === '') {
                continue;
            }

            $this->send(
                $email,
                (string) ($recipient['name'] ?? ''),
                $subject,
                $view,
                $data
            );
        }
    }
}