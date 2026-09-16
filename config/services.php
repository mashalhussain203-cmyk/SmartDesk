<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Hier staan de configuraties voor externe diensten die SmartDesk gebruikt.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Brevo
    |--------------------------------------------------------------------------
    |
    | SmartDesk gebruikt Brevo via de HTTPS API in plaats van SMTP.
    | Dit werkt beter op Railway en voorkomt SMTP-timeouts.
    |
    */

    'brevo' => [

        /*
        |--------------------------------------------------------------------------
        | API key
        |--------------------------------------------------------------------------
        */

        'api_key' => env(
            'BREVO_API_KEY'
        ),

        /*
        |--------------------------------------------------------------------------
        | Afzender e-mailadres
        |--------------------------------------------------------------------------
        */

        'from_email' => env(
            'BREVO_FROM_EMAIL',
            env('MAIL_FROM_ADDRESS')
        ),

        /*
        |--------------------------------------------------------------------------
        | Afzender naam
        |--------------------------------------------------------------------------
        */

        'from_name' => env(
            'BREVO_FROM_NAME',
            env(
                'MAIL_FROM_NAME',
                'SmartDesk'
            )
        ),

        /*
        |--------------------------------------------------------------------------
        | Brevo API URL
        |--------------------------------------------------------------------------
        */

        'base_url' => env(
            'BREVO_BASE_URL',
            'https://api.brevo.com/v3'
        ),
    ],

];