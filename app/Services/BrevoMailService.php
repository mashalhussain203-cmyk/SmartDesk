<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use RuntimeException;

class BrevoMailService
{
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $view,
        array $data = []
    ): void {
        $apiKey = config('services.brevo.api_key');

        $fromEmail = config(
            'services.brevo.from_email'
        );

        $fromName = config(
            'services.brevo.from_name',
            'SmartDesk'
        );

        $baseUrl = rtrim(
            config(
                'services.brevo.base_url',
                'https://api.brevo.com/v3'
            ),
            '/'
        );

        if (empty($apiKey)) {
            throw new RuntimeException(
                'BREVO_API_KEY ontbreekt.'
            );
        }

        if (empty($fromEmail)) {
            throw new RuntimeException(
                'BREVO_FROM_EMAIL ontbreekt.'
            );
        }

        if (! View::exists($view)) {
            throw new RuntimeException(
                'E-mailtemplate [' .
                $view .
                '] bestaat niet.'
            );
        }

        $htmlContent = View::make(
            $view,
            $data
        )->render();

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

    public function sendToMany(
        array $recipients,
        string $subject,
        string $view,
        array $data = []
    ): void {
        foreach ($recipients as $recipient) {
            $this->send(
                $recipient['email'],
                $recipient['name'] ?? '',
                $subject,
                $view,
                $data
            );
        }
    }
}