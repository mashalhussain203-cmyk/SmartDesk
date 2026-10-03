<?php

return [
    'calls' => [
        'stun_urls' => env('LIVE_CHAT_STUN_URLS', 'stun:stun.l.google.com:19302,stun:stun1.l.google.com:19302'),
        'turn_url' => env('LIVE_CHAT_TURN_URL', ''),
        'turn_username' => env('LIVE_CHAT_TURN_USERNAME', ''),
        'turn_credential' => env('LIVE_CHAT_TURN_CREDENTIAL', ''),
        'ring_timeout_seconds' => (int) env('LIVE_CHAT_CALL_RING_TIMEOUT', 45),
    ],
    'auto_reply' => [
        'enabled' => (bool) env('LIVE_CHAT_AUTO_REPLY_ENABLED', false),
        'timezone' => env('LIVE_CHAT_TIMEZONE', 'Europe/Amsterdam'),
        'start' => env('LIVE_CHAT_BUSINESS_START', '09:00'),
        'end' => env('LIVE_CHAT_BUSINESS_END', '18:00'),
        'weekdays' => [1, 2, 3, 4, 5],
        'message' => env(
            'LIVE_CHAT_AUTO_REPLY_MESSAGE',
            'Bedankt voor je bericht. Onze support is momenteel offline. We reageren zo snel mogelijk zodra we weer beschikbaar zijn.'
        ),
    ],
    'edit_window_minutes' => (int) env('LIVE_CHAT_EDIT_WINDOW_MINUTES', 5),
    'media_retention_days' => (int) env('LIVE_CHAT_MEDIA_RETENTION_DAYS', 30),
    'max_labels' => 8,
];
