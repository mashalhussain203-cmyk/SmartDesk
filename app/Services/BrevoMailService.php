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
     * Verstuur één transactionele e-mail via de Brevo HTTPS API.
     *
     * @param  string  $toEmail
     * @param  string  $toName
     * @param  string  $subject
     * @param  string  $view
     * @param  array<string, mixed>  $data
     */
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $view,
        array $data = []
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Brevo-configuratie ophalen
        |--------------------------------------------------------------------------
        */

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
            'Mashal Automotive'
        ));

        $baseUrl = rtrim(
            trim((string) config(
                'services.brevo.base_url',
                'https://api.brevo.com/v3'
            )),
            '/'
        );


        /*
        |--------------------------------------------------------------------------
        | Invoer normaliseren
        |--------------------------------------------------------------------------
        */

        $toEmail = trim($toEmail);
        $toName = trim($toName);
        $subject = trim($subject);
        $view = trim($view);


        /*
        |--------------------------------------------------------------------------
        | Brevo API key controleren
        |--------------------------------------------------------------------------
        */

        if ($apiKey === '') {
            throw new RuntimeException(
                'BREVO_API_KEY ontbreekt.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Afzender controleren
        |--------------------------------------------------------------------------
        */

        if ($fromEmail === '') {
            throw new RuntimeException(
                'BREVO_FROM_EMAIL ontbreekt.'
            );
        }

        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'BREVO_FROM_EMAIL is geen geldig e-mailadres.'
            );
        }

        if ($fromName === '') {
            $fromName = 'Mashal Automotive';
        }


        /*
        |--------------------------------------------------------------------------
        | Brevo API URL controleren
        |--------------------------------------------------------------------------
        */

        if (
            !filter_var($baseUrl, FILTER_VALIDATE_URL) ||
            strtolower((string) parse_url(
                $baseUrl,
                PHP_URL_SCHEME
            )) !== 'https'
        ) {
            throw new RuntimeException(
                'BREVO_BASE_URL moet een geldige HTTPS-URL zijn.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Ontvanger controleren
        |--------------------------------------------------------------------------
        */

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'De ontvanger heeft geen geldig e-mailadres.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Onderwerp controleren
        |--------------------------------------------------------------------------
        */

        if ($subject === '') {
            throw new RuntimeException(
                'Het onderwerp van de e-mail ontbreekt.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Blade-view controleren
        |--------------------------------------------------------------------------
        */

        if ($view === '') {
            throw new RuntimeException(
                'De naam van de e-mailtemplate ontbreekt.'
            );
        }

        if (!View::exists($view)) {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] bestaat niet."
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
                "E-mailtemplate [{$view}] kon niet worden gerenderd: " .
                $exception->getMessage(),
                0,
                $exception
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Gerenderde inhoud controleren
        |--------------------------------------------------------------------------
        */

        if (trim($htmlContent) === '') {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] levert lege inhoud op."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Ontvanger samenstellen
        |--------------------------------------------------------------------------
        */

        $recipient = [
            'email' => $toEmail,
        ];

        if ($toName !== '') {
            $recipient['name'] = $toName;
        }


        /*
        |--------------------------------------------------------------------------
        | Brevo payload samenstellen
        |--------------------------------------------------------------------------
        */

        $payload = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],

            'to' => [
                $recipient,
            ],

            'subject' => $subject,

            'htmlContent' => $htmlContent,
        ];


        /*
        |--------------------------------------------------------------------------
        | E-mail versturen via Brevo HTTPS API
        |--------------------------------------------------------------------------
        |
        | Er wordt bewust geen automatische retry gebruikt.
        |
        | Wanneer Brevo de e-mail al heeft geaccepteerd maar de HTTP-response
        | onderweg verloren gaat, zou een automatische retry namelijk een
        | dubbele e-mail kunnen veroorzaken.
        |
        */

        try {
            $response = Http::withHeaders([
                'api-key' => $apiKey,
            ])
                ->acceptJson()
                ->asJson()
                ->connectTimeout(10)
                ->timeout(20)
                ->post(
                    $baseUrl . '/smtp/email',
                    $payload
                );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Brevo kon niet worden bereikt of reageerde niet op tijd. ' .
                'Het is niet zeker of de e-mail door Brevo is geaccepteerd.',
                0,
                $exception
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Brevo response controleren
        |--------------------------------------------------------------------------
        */

        if (!$response->successful()) {
            throw new RuntimeException(
                'Brevo heeft de e-mail niet geaccepteerd. ' .
                'HTTP-status: ' . $response->status() . '. ' .
                'Antwoord: ' . $response->body()
            );
        }
    }


    /**
     * Verstuur dezelfde e-mail afzonderlijk naar meerdere ontvangers.
     *
     * Iedere ontvanger krijgt een afzonderlijke API-call.
     *
     * Ontvangers zonder geldig e-mailadres worden overgeslagen.
     *
     * Bij een verzendfout wordt de verwerking gestopt en wordt de
     * exception doorgestuurd naar de aanroepende code.
     *
     * @param  array<int, array<string, mixed>>  $recipients
     * @param  string  $subject
     * @param  string  $view
     * @param  array<string, mixed>  $data
     */
    public function sendToMany(
        array $recipients,
        string $subject,
        string $view,
        array $data = []
    ): void {
        foreach ($recipients as $recipient) {

            /*
            |--------------------------------------------------------------------------
            | Ongeldige recipient-structuur overslaan
            |--------------------------------------------------------------------------
            */

            if (!is_array($recipient)) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Ontvangergegevens ophalen
            |--------------------------------------------------------------------------
            */

            $email = trim(
                (string) ($recipient['email'] ?? '')
            );

            $name = trim(
                (string) ($recipient['name'] ?? '')
            );


            /*
            |--------------------------------------------------------------------------
            | Ontvangers zonder e-mailadres overslaan
            |--------------------------------------------------------------------------
            */

            if ($email === '') {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Individuele e-mail versturen
            |--------------------------------------------------------------------------
            */

            $this->send(
                $email,
                $name,
                $subject,
                $view,
                $data
            );
        }
    }
}