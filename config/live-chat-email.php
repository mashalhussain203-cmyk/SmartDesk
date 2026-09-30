<?php

return [
    'enabled' => (bool) env('LIVE_CHAT_EMAIL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Uitgaand: Brevo transactional API
    |--------------------------------------------------------------------------
    */
    'brevo_api_key' => env('BREVO_API_KEY'),
    'from_email' => env('BREVO_FROM_EMAIL', env('MAIL_FROM_ADDRESS')),
    'from_name' => env('BREVO_FROM_NAME', env('MAIL_FROM_NAME', 'Mashal Support')),
    'brevo_endpoint' => env('BREVO_API_ENDPOINT', 'https://api.brevo.com/v3/smtp/email'),
    'brevo_timeout_seconds' => (int) env('LIVE_CHAT_BREVO_TIMEOUT', 20),

    /*
    |--------------------------------------------------------------------------
    | Inkomend: Gmail via IMAP
    |--------------------------------------------------------------------------
    |
    | Als LIVE_CHAT_GMAIL_USERNAME leeg is, gebruiken we BREVO_FROM_EMAIL.
    | Voor een @gmail.com adres werkt reply+tagging zonder eigen domein.
    |
    */
    'gmail_username' => env('LIVE_CHAT_GMAIL_USERNAME', env('BREVO_FROM_EMAIL')),
    'gmail_app_password' => env('LIVE_CHAT_GMAIL_APP_PASSWORD'),
    'gmail_host' => env('LIVE_CHAT_GMAIL_HOST', 'imap.gmail.com'),
    'gmail_port' => (int) env('LIVE_CHAT_GMAIL_PORT', 993),
    'gmail_timeout_seconds' => (int) env('LIVE_CHAT_GMAIL_TIMEOUT', 15),
    'gmail_sync_limit' => (int) env('LIVE_CHAT_GMAIL_SYNC_LIMIT', 25),

    'strict_sender_match' => (bool) env('LIVE_CHAT_EMAIL_STRICT_SENDER_MATCH', true),
    'subject_prefix' => env('LIVE_CHAT_EMAIL_SUBJECT_PREFIX', 'Mashal Support'),

    'handoff_message' => env(
        'LIVE_CHAT_EMAIL_HANDOFF_MESSAGE',
        'Uw gesprek met Mashal Support gaat vanaf nu verder via e-mail. U kunt rechtstreeks op deze e-mail antwoorden.'
    ),

    'max_inbound_body_length' => (int) env('LIVE_CHAT_EMAIL_MAX_BODY', 12000),
    'max_attachment_bytes' => (int) env('LIVE_CHAT_EMAIL_MAX_ATTACHMENT_BYTES', 20 * 1024 * 1024),
];
