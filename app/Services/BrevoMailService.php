<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use RuntimeException;
use Throwable;

class BrevoMailService
{
    /**
     * Verstuur één transactionele e-mail via de Brevo HTTPS API.
     *
     * Deze service:
     *
     * - gebruikt geen SMTP;
     * - gebruikt de Brevo HTTPS API;
     * - rendert eerst een Blade-template;
     * - valideert afzender en ontvanger;
     * - gebruikt bewust geen automatische retry;
     * - geeft fouten door aan de aanroepende service.
     *
     * LoginSecurityService vangt deze fouten vervolgens af,
     * zodat een mislukte beveiligingsmail nooit een login blokkeert.
     *
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
        | Configuratie
        |--------------------------------------------------------------------------
        */

        $apiKey = trim(
            (string) config(
                'services.brevo.api_key',
                ''
            )
        );

        $fromEmail = trim(
            (string) config(
                'services.brevo.from_email',
                ''
            )
        );

        $fromName = trim(
            (string) config(
                'services.brevo.from_name',
                'Mashal Automotive'
            )
        );

        $baseUrl = rtrim(
            trim(
                (string) config(
                    'services.brevo.base_url',
                    'https://api.brevo.com/v3'
                )
            ),
            '/'
        );

        /*
        |--------------------------------------------------------------------------
        | Invoer normaliseren
        |--------------------------------------------------------------------------
        */

        $toEmail = strtolower(
            trim(
                $toEmail
            )
        );

        $toName = $this->cleanHeaderValue(
            $toName
        );

        $subject = $this->cleanHeaderValue(
            $subject
        );

        $view = trim(
            $view
        );

        /*
        |--------------------------------------------------------------------------
        | Brevo-configuratie valideren
        |--------------------------------------------------------------------------
        */

        $this->validateConfiguration(
            $apiKey,
            $fromEmail,
            $baseUrl
        );

        /*
        |--------------------------------------------------------------------------
        | Ontvanger valideren
        |--------------------------------------------------------------------------
        */

        if (
            ! filter_var(
                $toEmail,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'De ontvanger heeft geen geldig e-mailadres.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Onderwerp
        |--------------------------------------------------------------------------
        */

        if ($subject === '') {
            throw new RuntimeException(
                'Het onderwerp van de e-mail ontbreekt.'
            );
        }

        if (
            mb_strlen(
                $subject
            ) > 255
        ) {
            $subject = mb_substr(
                $subject,
                0,
                255
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Blade-template
        |--------------------------------------------------------------------------
        */

        if ($view === '') {
            throw new RuntimeException(
                'De naam van de e-mailtemplate ontbreekt.'
            );
        }

        if (
            ! View::exists(
                $view
            )
        ) {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] bestaat niet."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Template renderen
        |--------------------------------------------------------------------------
        */

        $htmlContent = $this->renderView(
            $view,
            $data
        );

        /*
        |--------------------------------------------------------------------------
        | Afzendernaam fallback
        |--------------------------------------------------------------------------
        */

        $fromName = $this->cleanHeaderValue(
            $fromName
        );

        if ($fromName === '') {
            $fromName = 'Mashal Automotive';
        }

        /*
        |--------------------------------------------------------------------------
        | Ontvanger
        |--------------------------------------------------------------------------
        */

        $recipient = [
            'email' => $toEmail,
        ];

        if ($toName !== '') {
            $recipient['name'] = mb_substr(
                $toName,
                0,
                200
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Brevo payload
        |--------------------------------------------------------------------------
        */

        $payload = [
            'sender' => [
                'name' => mb_substr(
                    $fromName,
                    0,
                    200
                ),

                'email' => strtolower(
                    $fromEmail
                ),
            ],

            'to' => [
                $recipient,
            ],

            'subject' => $subject,

            'htmlContent' => $htmlContent,
        ];

        /*
        |--------------------------------------------------------------------------
        | API-call
        |--------------------------------------------------------------------------
        */

        $response = $this->sendRequest(
            $baseUrl,
            $apiKey,
            $payload
        );

        /*
        |--------------------------------------------------------------------------
        | Brevo-response controleren
        |--------------------------------------------------------------------------
        */

        if (
            ! $response->successful()
        ) {
            throw new RuntimeException(
                $this->buildBrevoErrorMessage(
                    $response
                )
            );
        }
    }

    /**
     * Verstuur dezelfde template afzonderlijk naar meerdere ontvangers.
     *
     * Iedere ontvanger krijgt bewust een aparte API-call.
     *
     * Daardoor:
     *
     * - ziet iedere ontvanger alleen zijn eigen adres;
     * - blijft de bestaande send()-validatie gelden;
     * - kunnen templates later eventueel per ontvanger worden aangepast.
     *
     * @param  array<int, array<string, mixed>>  $recipients
     * @param  array<string, mixed>  $data
     */
    public function sendToMany(
        array $recipients,
        string $subject,
        string $view,
        array $data = []
    ): void {
        foreach (
            $recipients as $recipient
        ) {
            /*
            |--------------------------------------------------------------------------
            | Structuur controleren
            |--------------------------------------------------------------------------
            */

            if (
                ! is_array(
                    $recipient
                )
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Ontvanger uitlezen
            |--------------------------------------------------------------------------
            */

            $email = trim(
                (string) (
                    $recipient['email']
                    ?? ''
                )
            );

            $name = trim(
                (string) (
                    $recipient['name']
                    ?? ''
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Leeg / ongeldig adres overslaan
            |--------------------------------------------------------------------------
            */

            if ($email === '') {
                continue;
            }

            if (
                ! filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Versturen
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

    /**
     * Controleer of de Brevo-configuratie bruikbaar is.
     */
    private function validateConfiguration(
        string $apiKey,
        string $fromEmail,
        string $baseUrl
    ): void {
        /*
        |--------------------------------------------------------------------------
        | API-key
        |--------------------------------------------------------------------------
        */

        if ($apiKey === '') {
            throw new RuntimeException(
                'BREVO_API_KEY ontbreekt.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Afzender
        |--------------------------------------------------------------------------
        */

        if ($fromEmail === '') {
            throw new RuntimeException(
                'BREVO_FROM_EMAIL ontbreekt.'
            );
        }

        if (
            ! filter_var(
                $fromEmail,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'BREVO_FROM_EMAIL is geen geldig e-mailadres.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Base URL
        |--------------------------------------------------------------------------
        */

        if (
            ! filter_var(
                $baseUrl,
                FILTER_VALIDATE_URL
            )
        ) {
            throw new RuntimeException(
                'BREVO_BASE_URL is geen geldige URL.'
            );
        }

        $scheme = strtolower(
            (string) parse_url(
                $baseUrl,
                PHP_URL_SCHEME
            )
        );

        if ($scheme !== 'https') {
            throw new RuntimeException(
                'BREVO_BASE_URL moet HTTPS gebruiken.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Alleen geldige host
        |--------------------------------------------------------------------------
        */

        $host = trim(
            (string) parse_url(
                $baseUrl,
                PHP_URL_HOST
            )
        );

        if ($host === '') {
            throw new RuntimeException(
                'BREVO_BASE_URL bevat geen geldige host.'
            );
        }
    }

    /**
     * Render een Blade-template naar HTML.
     *
     * @param  array<string, mixed>  $data
     */
    private function renderView(
        string $view,
        array $data
    ): string {
        try {
            $htmlContent = View::make(
                $view,
                $data
            )->render();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] kon niet worden gerenderd: "
                . $exception->getMessage(),
                0,
                $exception
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lege HTML voorkomen
        |--------------------------------------------------------------------------
        */

        if (
            trim(
                $htmlContent
            ) === ''
        ) {
            throw new RuntimeException(
                "E-mailtemplate [{$view}] levert lege inhoud op."
            );
        }

        return $htmlContent;
    }

    /**
     * Voer de daadwerkelijke HTTPS-call naar Brevo uit.
     *
     * Er wordt bewust GEEN automatische retry uitgevoerd.
     *
     * Als Brevo een e-mail al heeft ontvangen maar de HTTP-response
     * verloren gaat, kan automatisch opnieuw proberen anders leiden
     * tot dubbele beveiligingsmails.
     *
     * @param  array<string, mixed>  $payload
     */
    private function sendRequest(
        string $baseUrl,
        string $apiKey,
        array $payload
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Timeouts
        |--------------------------------------------------------------------------
        */

        $connectTimeout = max(
            1,
            min(
                15,
                (int) config(
                    'services.brevo.connect_timeout',
                    10
                )
            )
        );

        $timeout = max(
            $connectTimeout,
            min(
                60,
                (int) config(
                    'services.brevo.timeout',
                    20
                )
            )
        );

        try {
            return Http::withHeaders([
                'api-key' => $apiKey,

                'Accept' => 'application/json',

                'User-Agent' =>
                    'MashalAutomotive-Brevo/2.0',
            ])
                ->asJson()
                ->connectTimeout(
                    $connectTimeout
                )
                ->timeout(
                    $timeout
                )
                ->post(
                    $baseUrl . '/smtp/email',
                    $payload
                );
        } catch (ConnectionException $exception) {
            /*
            |--------------------------------------------------------------------------
            | Geen zekerheid over afleverstatus
            |--------------------------------------------------------------------------
            |
            | Bij een timeout kan niet altijd worden vastgesteld of Brevo
            | de request vóór het verbreken van de verbinding al ontving.
            |
            */

            throw new RuntimeException(
                'Brevo kon niet worden bereikt of reageerde niet op tijd. '
                . 'Het is niet zeker of de e-mail door Brevo is geaccepteerd.',
                0,
                $exception
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Er is een fout opgetreden tijdens de verbinding met Brevo: '
                . $exception->getMessage(),
                0,
                $exception
            );
        }
    }

    /**
     * Maak een veilige foutmelding van een mislukte Brevo-response.
     *
     * De API-key wordt nooit in de foutmelding opgenomen.
     */
    private function buildBrevoErrorMessage(
        Response $response
    ): string {
        $status = $response->status();

        $detail = null;

        try {
            $json = $response->json();

            if (
                is_array(
                    $json
                )
            ) {
                $candidate =
                    $json['message']
                    ?? $json['error']
                    ?? $json['code']
                    ?? null;

                if (
                    is_scalar(
                        $candidate
                    )
                ) {
                    $detail = trim(
                        (string) $candidate
                    );
                }
            }
        } catch (Throwable) {
            //
        }

        /*
        |--------------------------------------------------------------------------
        | Geen volledige responsebody loggen
        |--------------------------------------------------------------------------
        |
        | Alleen een korte foutmelding gebruiken.
        |
        */

        if (
            $detail !== null &&
            $detail !== ''
        ) {
            $detail = mb_substr(
                $detail,
                0,
                500
            );

            return
                'Brevo heeft de e-mail niet geaccepteerd. '
                . 'HTTP-status: '
                . $status
                . '. '
                . 'Melding: '
                . $detail;
        }

        return
            'Brevo heeft de e-mail niet geaccepteerd. '
            . 'HTTP-status: '
            . $status
            . '.';
    }

    /**
     * Verwijder CR/LF uit waarden die als mailheaderachtige tekst
     * worden gebruikt.
     *
     * Hiermee voorkomen we ongewenste header-injectieachtige invoer.
     */
    private function cleanHeaderValue(
        mixed $value
    ): string {
        if (
            ! is_scalar(
                $value
            )
        ) {
            return '';
        }

        $value = (string) $value;

        $value = str_replace(
            [
                "\r",
                "\n",
                "\0",
            ],
            ' ',
            $value
        );

        $value = preg_replace(
            '/\s+/',
            ' ',
            $value
        ) ?? $value;

        return trim(
            $value
        );
    }
}