<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;
use Throwable;

class GmailLiveChatService
{
    private const TOKEN_CACHE_KEY = 'live_chat_gmail_oauth_token';

    protected GoogleClient $client;

    protected Gmail $gmail;

    protected string $fromEmail;

    protected string $fromName;

    public function __construct()
    {
        $clientId = trim((string) env(
            'LIVE_CHAT_GMAIL_CLIENT_ID',
            ''
        ));

        $clientSecret = trim((string) env(
            'LIVE_CHAT_GMAIL_CLIENT_SECRET',
            ''
        ));

        $this->fromEmail = trim((string) env(
            'LIVE_CHAT_GMAIL_USERNAME',
            ''
        ));

        $this->fromName = trim((string) env(
            'LIVE_CHAT_GMAIL_FROM_NAME',
            'SmartDesk'
        ));

        if ($clientId === '') {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_CLIENT_ID ontbreekt.'
            );
        }

        if ($clientSecret === '') {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_CLIENT_SECRET ontbreekt.'
            );
        }

        if ($this->fromEmail === '') {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_USERNAME ontbreekt.'
            );
        }

        if (! filter_var(
            $this->fromEmail,
            FILTER_VALIDATE_EMAIL
        )) {
            throw new RuntimeException(
                'LIVE_CHAT_GMAIL_USERNAME bevat geen geldig e-mailadres.'
            );
        }

        $this->client = new GoogleClient();

        $this->client->setClientId(
            $clientId
        );

        $this->client->setClientSecret(
            $clientSecret
        );

        $this->client->setScopes([
            Gmail::GMAIL_SEND,
            Gmail::GMAIL_READONLY,
        ]);

        $this->client->setAccessType(
            'offline'
        );

        $token = $this->getStoredToken();

        if ($token === []) {
            throw new RuntimeException(
                'Gmail is nog niet gekoppeld. Koppel eerst het Live Chat Gmail-account.'
            );
        }

        $this->authenticateWithStoredToken(
            $token
        );

        $this->gmail = new Gmail(
            $this->client
        );
    }

    /**
     * Gebruik de opgeslagen OAuth-token.
     */
    protected function authenticateWithStoredToken(
        array $token
    ): void {
        $this->client->setAccessToken(
            $token
        );

        if (! $this->client->isAccessTokenExpired()) {
            return;
        }

        $refreshToken = $token['refresh_token']
            ?? null;

        if (
            ! is_string($refreshToken)
            || $refreshToken === ''
        ) {
            throw new RuntimeException(
                'De Gmail access token is verlopen en er is geen refresh token beschikbaar. Koppel Gmail opnieuw.'
            );
        }

        try {
            $newToken = $this->client
                ->fetchAccessTokenWithRefreshToken(
                    $refreshToken
                );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Gmail authenticatie kon niet worden vernieuwd: '
                . $exception->getMessage(),
                previous: $exception
            );
        }

        if (
            isset($newToken['error'])
            || empty($newToken['access_token'])
        ) {
            $message = $newToken['error_description']
                ?? $newToken['error']
                ?? 'Onbekende Gmail OAuth fout.';

            throw new RuntimeException(
                'Gmail authenticatie mislukt: '
                . $message
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Bestaande refresh token behouden
        |--------------------------------------------------------------------------
        |
        | Google stuurt bij refresh meestal geen nieuwe refresh_token terug.
        |
        */

        $newToken['refresh_token'] = $newToken['refresh_token']
            ?? $refreshToken;

        $this->client->setAccessToken(
            $newToken
        );

        $this->storeToken(
            $newToken
        );
    }

    /**
     * Verstuur een HTML e-mail via Gmail API.
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $html,
        ?string $replyTo = null,
        ?string $replyToName = null
    ): array {
        $to = trim($to);
        $subject = trim($subject);

        if (
            $to === ''
            || ! filter_var(
                $to,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'Ongeldig ontvanger e-mailadres.'
            );
        }

        if ($subject === '') {
            throw new RuntimeException(
                'E-mailonderwerp ontbreekt.'
            );
        }

        $rawMessage = $this->buildMimeMessage(
            to: $to,
            subject: $subject,
            html: $html,
            replyTo: $replyTo,
            replyToName: $replyToName
        );

        $message = new Message();

        $message->setRaw(
            $this->base64UrlEncode(
                $rawMessage
            )
        );

        try {
            $sent = $this->gmail
                ->users_messages
                ->send(
                    'me',
                    $message
                );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Gmail kon de e-mail niet versturen: '
                . $exception->getMessage(),
                previous: $exception
            );
        }

        return [
            'id' => $sent->getId(),
            'thread_id' => $sent->getThreadId(),
        ];
    }

    /**
     * Verstuur het contactbericht naar SmartDesk / Mashal.
     */
    public function sendContactMessage(
        array $data
    ): array {
        $firstName = trim(
            (string) ($data['first_name'] ?? '')
        );

        $lastName = trim(
            (string) ($data['last_name'] ?? '')
        );

        $email = strtolower(
            trim(
                (string) ($data['email'] ?? '')
            )
        );

        $message = trim(
            (string) ($data['message'] ?? '')
        );

        if ($firstName === '') {
            throw new RuntimeException(
                'Voornaam ontbreekt.'
            );
        }

        if ($lastName === '') {
            throw new RuntimeException(
                'Achternaam ontbreekt.'
            );
        }

        if (
            $email === ''
            || ! filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'Ongeldig e-mailadres in contactbericht.'
            );
        }

        if ($message === '') {
            throw new RuntimeException(
                'Contactbericht ontbreekt.'
            );
        }

        $fullName = trim(
            $firstName . ' ' . $lastName
        );

        $recipient = trim(
            (string) config(
                'mail.contact_to',
                ''
            )
        );

        if ($recipient === '') {
            $recipient = trim(
                (string) env(
                    'CONTACT_MAIL_TO',
                    ''
                )
            );
        }

        if ($recipient === '') {
            throw new RuntimeException(
                'CONTACT_MAIL_TO ontbreekt.'
            );
        }

        if (! filter_var(
            $recipient,
            FILTER_VALIDATE_EMAIL
        )) {
            throw new RuntimeException(
                'CONTACT_MAIL_TO bevat geen geldig e-mailadres.'
            );
        }

        $html = view(
            'emails.contact-received',
            [
                'data' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'message' => $message,
                ],
            ]
        )->render();

        return $this->sendEmail(
            to: $recipient,
            subject: 'Nieuw contactbericht via SmartDesk',
            html: $html,
            replyTo: $email,
            replyToName: $fullName
        );
    }

    /**
     * Verstuur automatische ontvangstbevestiging naar bezoeker.
     */
    public function sendContactConfirmation(
        array $data
    ): array {
        $firstName = trim(
            (string) ($data['first_name'] ?? '')
        );

        $lastName = trim(
            (string) ($data['last_name'] ?? '')
        );

        $email = strtolower(
            trim(
                (string) ($data['email'] ?? '')
            )
        );

        $message = trim(
            (string) ($data['message'] ?? '')
        );

        if (
            $email === ''
            || ! filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'Ongeldig e-mailadres voor bevestigingsmail.'
            );
        }

        $html = view(
            'emails.contact-confirmation',
            [
                'data' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'message' => $message,
                ],
            ]
        )->render();

        return $this->sendEmail(
            to: $email,
            subject: 'We hebben uw bericht ontvangen | SmartDesk',
            html: $html
        );
    }

    /**
     * Bouw MIME-bericht voor Gmail API.
     */
    protected function buildMimeMessage(
        string $to,
        string $subject,
        string $html,
        ?string $replyTo = null,
        ?string $replyToName = null
    ): string {
        $boundary = '=_SmartDesk_'
            . bin2hex(
                random_bytes(16)
            );

        $fromName = $this->encodeHeader(
            $this->fromName
        );

        $encodedSubject = $this->encodeHeader(
            $subject
        );

        $headers = [
            'MIME-Version: 1.0',

            'From: '
                . $fromName
                . ' <'
                . $this->fromEmail
                . '>',

            'To: <'
                . $to
                . '>',

            'Subject: '
                . $encodedSubject,

            'Content-Type: multipart/alternative; boundary="'
                . $boundary
                . '"',
        ];

        if (
            $replyTo !== null
            && filter_var(
                $replyTo,
                FILTER_VALIDATE_EMAIL
            )
        ) {
            if (
                $replyToName !== null
                && trim($replyToName) !== ''
            ) {
                $headers[] = 'Reply-To: '
                    . $this->encodeHeader(
                        trim($replyToName)
                    )
                    . ' <'
                    . $replyTo
                    . '>';
            } else {
                $headers[] = 'Reply-To: <'
                    . $replyTo
                    . '>';
            }
        }

        $plainText = $this->htmlToPlainText(
            $html
        );

        $body = [];

        $body[] = '--'
            . $boundary;

        $body[] = 'Content-Type: text/plain; charset=UTF-8';

        $body[] = 'Content-Transfer-Encoding: base64';

        $body[] = '';

        $body[] = chunk_split(
            base64_encode(
                $plainText
            )
        );

        $body[] = '--'
            . $boundary;

        $body[] = 'Content-Type: text/html; charset=UTF-8';

        $body[] = 'Content-Transfer-Encoding: base64';

        $body[] = '';

        $body[] = chunk_split(
            base64_encode(
                $html
            )
        );

        $body[] = '--'
            . $boundary
            . '--';

        return implode(
            "\r\n",
            $headers
        )
            . "\r\n\r\n"
            . implode(
                "\r\n",
                $body
            );
    }

    /**
     * Encode UTF-8 header.
     */
    protected function encodeHeader(
        string $value
    ): string {
        return '=?UTF-8?B?'
            . base64_encode(
                $value
            )
            . '?=';
    }

    /**
     * HTML naar simpele plaintext.
     */
    protected function htmlToPlainText(
        string $html
    ): string {
        $text = preg_replace(
            '/<br\s*\/?>/i',
            "\n",
            $html
        );

        $text = preg_replace(
            '/<\/p>/i',
            "\n\n",
            (string) $text
        );

        $text = strip_tags(
            (string) $text
        );

        $text = html_entity_decode(
            $text,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $text = preg_replace(
            "/[ \t]+\n/",
            "\n",
            $text
        );

        $text = preg_replace(
            "/\n{3,}/",
            "\n\n",
            (string) $text
        );

        return trim(
            (string) $text
        );
    }

    /**
     * Gmail API verwacht URL-safe Base64.
     */
    protected function base64UrlEncode(
        string $value
    ): string {
        return rtrim(
            strtr(
                base64_encode(
                    $value
                ),
                '+/',
                '-_'
            ),
            '='
        );
    }

    /**
     * OAuth-token veilig uit Laravel cache ophalen.
     */
    protected function getStoredToken(): array
    {
        $encrypted = Cache::get(
            self::TOKEN_CACHE_KEY
        );

        if (
            ! is_string($encrypted)
            || $encrypted === ''
        ) {
            return [];
        }

        try {
            $json = Crypt::decryptString(
                $encrypted
            );

            $token = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return is_array($token)
                ? $token
                : [];
        } catch (Throwable $exception) {
            report($exception);

            return [];
        }
    }

    /**
     * Vernieuwde OAuth-token opnieuw veilig opslaan.
     */
    protected function storeToken(
        array $token
    ): void {
        try {
            $json = json_encode(
                $token,
                JSON_THROW_ON_ERROR
            );

            $encrypted = Crypt::encryptString(
                $json
            );

            Cache::forever(
                self::TOKEN_CACHE_KEY,
                $encrypted
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Gmail OAuth-token kon niet worden opgeslagen: '
                . $exception->getMessage(),
                previous: $exception
            );
        }
    }
}