<?php

return [
    'enabled' => env('PASSKEYS_ENABLED', false),
    'rp_name' => env('APP_NAME', 'Mashal Studio'),
    'origin' => rtrim(env('PASSKEYS_ORIGIN', env('APP_URL', 'http://localhost:8000')), '/'),
    'iphone_only_ui' => env('PASSKEYS_IPHONE_ONLY_UI', true),
    'challenge_seconds' => 120,
    'recent_login_seconds' => 600,
];
