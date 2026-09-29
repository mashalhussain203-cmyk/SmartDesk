<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Live chat -> e-mail handoff
    |--------------------------------------------------------------------------
    |
    | De admin blijft in het bestaande live-chatpaneel. Zodra een gesprek
    | naar e-mail wordt overgezet, worden adminberichten ook per e-mail naar
    | de klant gestuurd. Antwoorden van de klant komen via Mailgun terug in
    | hetzelfde live_chat_messages gesprek.
    |
    */

    'enabled' => (bool) env('LIVE_CHAT_EMAIL_ENABLED', true),

    'reply_domain' => env('LIVE_CHAT_REPLY_DOMAIN'),

    'inbound_secret' => env('LIVE_CHAT_INBOUND_SECRET'),

    'mailgun_signing_key' => env('MAILGUN_WEBHOOK_SIGNING_KEY'),

    'strict_sender_match' => (bool) env('LIVE_CHAT_EMAIL_STRICT_SENDER_MATCH', true),

    'subject_prefix' => env('LIVE_CHAT_EMAIL_SUBJECT_PREFIX', 'Mashal Support'),

    'handoff_message' => env(
        'LIVE_CHAT_EMAIL_HANDOFF_MESSAGE',
        'Uw gesprek met Mashal Support gaat vanaf nu verder via e-mail. U kunt rechtstreeks op deze e-mail antwoorden.'
    ),

    'max_inbound_body_length' => (int) env('LIVE_CHAT_EMAIL_MAX_BODY', 12000),

    'max_attachment_bytes' => (int) env('LIVE_CHAT_EMAIL_MAX_ATTACHMENT_BYTES', 20 * 1024 * 1024),

    'webhook_max_age_seconds' => (int) env('LIVE_CHAT_EMAIL_WEBHOOK_MAX_AGE', 1800),
];
