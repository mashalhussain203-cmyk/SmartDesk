<?php

namespace App\Services;

use RuntimeException;

class GmailLiveChatService
{
    protected string $fromEmail;

    protected string $fromName;

    public function __construct(
        protected GmailApiClient $gmail
    ) {
        $this->fromEmail = trim(
            $this->gmail->username()
        );

        $this->fromName = trim((string) env(
            'LIVE_CHAT_GMAIL_FROM_NAME',
            'SmartDesk'
        ));

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
    }

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
            || ! filter_var($to, FILTER_VALIDATE_EMAIL)
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

        return $this->gmail->sendRaw(
            $rawMessage
        );
    }

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
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
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

        if (
            $recipient === ''
            || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)
        ) {
            throw new RuntimeException(
                'CONTACT_MAIL_TO ontbreekt of bevat geen geldig e-mailadres.'
            );
        }

        $fullName = trim(
            $firstName . ' ' . $lastName
        );

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
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
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

    protected function buildMimeMessage(
        string $to,
        string $subject,
        string $html,
        ?string $replyTo = null,
        ?string $replyToName = null
    ): string {
        $boundary = '=_SmartDesk_'
            . bin2hex(random_bytes(16));

        $headers = [
            'MIME-Version: 1.0',
            'From: '
                . $this->encodeHeader($this->fromName)
                . ' <'
                . $this->fromEmail
                . '>',
            'To: <' . $to . '>',
            'Subject: ' . $this->encodeHeader($subject),
            'Content-Type: multipart/alternative; boundary="'
                . $boundary
                . '"',
        ];

        if (
            $replyTo !== null
            && filter_var($replyTo, FILTER_VALIDATE_EMAIL)
        ) {
            if (
                $replyToName !== null
                && trim($replyToName) !== ''
            ) {
                $headers[] = 'Reply-To: '
                    . $this->encodeHeader(trim($replyToName))
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

        $body = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($plainText)),
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($html)),
            '--' . $boundary . '--',
        ];

        return implode("\r\n", $headers)
            . "\r\n\r\n"
            . implode("\r\n", $body);
    }

    protected function encodeHeader(
        string $value
    ): string {
        return '=?UTF-8?B?'
            . base64_encode($value)
            . '?=';
    }

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
}
