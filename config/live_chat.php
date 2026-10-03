<?php

return [
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
