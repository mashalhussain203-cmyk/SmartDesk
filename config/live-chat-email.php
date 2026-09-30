<?php

return [
    'enabled' => (bool) env(
        'LIVE_CHAT_EMAIL_ENABLED',
        true
    ),

    /*
    |--------------------------------------------------------------------------
    | Gmail HTTPS API
    |--------------------------------------------------------------------------
    |
    | Live-chat e-mail gaat rechtstreeks via Gmail API zodat replies aan
    | dezelfde Gmail threadId kunnen worden toegevoegd. SMTP is niet nodig.
    |
    */
    'gmail_username' => env(
        'LIVE_CHAT_GMAIL_USERNAME',
        env('BREVO_FROM_EMAIL')
    ),

    'gmail_client_id' => env(
        'LIVE_CHAT_GMAIL_CLIENT_ID'
    ),

    'gmail_client_secret' => env(
        'LIVE_CHAT_GMAIL_CLIENT_SECRET'
    ),

    /*
     * Optioneel: normaal slaat /admin/live-chat/gmail/connect het refresh
     * token versleuteld in live_chat_settings op. Als je liever Railway
     * gebruikt, kun je LIVE_CHAT_GMAIL_REFRESH_TOKEN zetten.
     */
    'gmail_refresh_token' => env(
        'LIVE_CHAT_GMAIL_REFRESH_TOKEN'
    ),

    'gmail_redirect_uri' => env(
        'LIVE_CHAT_GMAIL_REDIRECT_URI'
    ),

    'gmail_scope' => env(
        'LIVE_CHAT_GMAIL_SCOPE',
        'https://www.googleapis.com/auth/gmail.modify'
    ),

    'gmail_api_base' => env(
        'LIVE_CHAT_GMAIL_API_BASE',
        'https://gmail.googleapis.com/gmail/v1'
    ),

    'google_authorize_endpoint' => env(
        'LIVE_CHAT_GOOGLE_AUTHORIZE_ENDPOINT',
        'https://accounts.google.com/o/oauth2/v2/auth'
    ),

    'google_token_endpoint' => env(
        'LIVE_CHAT_GOOGLE_TOKEN_ENDPOINT',
        'https://oauth2.googleapis.com/token'
    ),

    'gmail_api_timeout_seconds' => (int) env(
        'LIVE_CHAT_GMAIL_API_TIMEOUT',
        20
    ),

    'gmail_sync_limit' => (int) env(
        'LIVE_CHAT_GMAIL_SYNC_LIMIT',
        25
    ),

    'from_name' => env(
        'BREVO_FROM_NAME',
        env(
            'MAIL_FROM_NAME',
            'Mashal Support'
        )
    ),

    'strict_sender_match' => (bool) env(
        'LIVE_CHAT_EMAIL_STRICT_SENDER_MATCH',
        true
    ),

    'subject_prefix' => env(
        'LIVE_CHAT_EMAIL_SUBJECT_PREFIX',
        'Mashal Support'
    ),

    'handoff_message' => env(
        'LIVE_CHAT_EMAIL_HANDOFF_MESSAGE',
        'Uw gesprek met Mashal Support gaat vanaf nu verder via e-mail. U kunt rechtstreeks op deze e-mail antwoorden.'
    ),

    'max_inbound_body_length' => (int) env(
        'LIVE_CHAT_EMAIL_MAX_BODY',
        12000
    ),

    'max_attachment_bytes' => (int) env(
        'LIVE_CHAT_EMAIL_MAX_ATTACHMENT_BYTES',
        20 * 1024 * 1024
    ),
];
