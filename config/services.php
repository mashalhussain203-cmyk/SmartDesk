<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Centrale configuratie voor externe diensten binnen Mashal Automotive.
    |
    | Gevoelige gegevens zoals API keys, OAuth client secrets en tokens
    | worden uitsluitend via environment variables ingeladen.
    |
    | Plaats nooit echte secrets rechtstreeks in dit bestand.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Postmark
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],


    /*
    |--------------------------------------------------------------------------
    | Resend
    |--------------------------------------------------------------------------
    */

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],


    /*
    |--------------------------------------------------------------------------
    | Amazon SES
    |--------------------------------------------------------------------------
    */

    'ses' => [

        'key' => env('AWS_ACCESS_KEY_ID'),

        'secret' => env('AWS_SECRET_ACCESS_KEY'),

        'region' => env(
            'AWS_DEFAULT_REGION',
            'us-east-1'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Slack Notifications
    |--------------------------------------------------------------------------
    */

    'slack' => [

        'notifications' => [

            'bot_user_oauth_token' => env(
                'SLACK_BOT_USER_OAUTH_TOKEN'
            ),

            'channel' => env(
                'SLACK_BOT_USER_DEFAULT_CHANNEL'
            ),

        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Google
    |--------------------------------------------------------------------------
    |
    | Google wordt binnen Mashal Automotive voor twee verschillende
    | functies gebruikt:
    |
    | 1. Authenticatie
    |    - Inloggen met Google
    |    - Registreren met Google
    |    - Google-profiel ophalen via Laravel Socialite
    |
    | 2. Gmail API
    |    - Gmail-account koppelen
    |    - Inbox bekijken
    |    - E-mails openen
    |    - E-mails versturen
    |
    | Voor Google login en Gmail gebruiken we bewust aparte redirect URI's.
    | Hierdoor kunnen beide OAuth-flows naast elkaar blijven werken.
    |
    */

    'google' => [

        /*
        |--------------------------------------------------------------------------
        | Google Client ID
        |--------------------------------------------------------------------------
        */

        'client_id' => env(
            'GOOGLE_CLIENT_ID'
        ),


        /*
        |--------------------------------------------------------------------------
        | Google Client Secret
        |--------------------------------------------------------------------------
        */

        'client_secret' => env(
            'GOOGLE_CLIENT_SECRET'
        ),


        /*
        |--------------------------------------------------------------------------
        | Google Authentication Redirect URI
        |--------------------------------------------------------------------------
        |
        | Deze callback wordt gebruikt voor normaal:
        |
        | - Inloggen met Google
        | - Registreren met Google
        |
        | Bijvoorbeeld:
        |
        | https://mashalhussain.up.railway.app/auth/google/callback
        |
        */

        'redirect' => env(
            'GOOGLE_REDIRECT_URI'
        ),


        /*
        |--------------------------------------------------------------------------
        | Gmail OAuth Redirect URI
        |--------------------------------------------------------------------------
        |
        | Deze callback wordt uitsluitend gebruikt voor het koppelen
        | van Gmail aan een reeds ingelogde Mashal Automotive gebruiker.
        |
        | Productie:
        |
        | https://mashalhussain.up.railway.app/gmail/callback
        |
        */

        'gmail_redirect' => env(
            'GOOGLE_GMAIL_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | GitHub OAuth
    |--------------------------------------------------------------------------
    */

    'github' => [

        'client_id' => env(
            'GITHUB_CLIENT_ID'
        ),

        'client_secret' => env(
            'GITHUB_CLIENT_SECRET'
        ),

        'redirect' => env(
            'GITHUB_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Facebook OAuth
    |--------------------------------------------------------------------------
    */

    'facebook' => [

        'client_id' => env(
            'FACEBOOK_CLIENT_ID'
        ),

        'client_secret' => env(
            'FACEBOOK_CLIENT_SECRET'
        ),

        'redirect' => env(
            'FACEBOOK_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | TikTok OAuth
    |--------------------------------------------------------------------------
    */

    'tiktok' => [

        'client_id' => env(
            'TIKTOK_CLIENT_ID'
        ),

        'client_secret' => env(
            'TIKTOK_CLIENT_SECRET'
        ),

        'redirect' => env(
            'TIKTOK_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | X OAuth
    |--------------------------------------------------------------------------
    |
    | OAuth 2.0 Authorization Code Flow met PKCE.
    |
    */

    'x' => [

        'client_id' => env(
            'X_CLIENT_ID'
        ),

        'client_secret' => env(
            'X_CLIENT_SECRET'
        ),

        'redirect' => env(
            'X_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Brevo
    |--------------------------------------------------------------------------
    |
    | Brevo wordt gebruikt voor transactionele e-mail.
    |
    | Bijvoorbeeld:
    |
    | - Welkomstmails
    | - Accountmeldingen
    | - Wachtwoordherstel
    | - Bestelbevestigingen
    | - Beveiligingsmeldingen
    |
    */

    'brevo' => [

        'api_key' => env(
            'BREVO_API_KEY'
        ),

        'from_email' => env(
            'BREVO_FROM_EMAIL',
            env('MAIL_FROM_ADDRESS')
        ),

        'from_name' => env(
            'BREVO_FROM_NAME',
            env(
                'MAIL_FROM_NAME',
                'Mashal Automotive'
            )
        ),

        'base_url' => env(
            'BREVO_BASE_URL',
            'https://api.brevo.com/v3'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | LinkedIn OpenID Connect
    |--------------------------------------------------------------------------
    */

    'linkedin-openid' => [

        'client_id' => env(
            'LINKEDIN_CLIENT_ID'
        ),

        'client_secret' => env(
            'LINKEDIN_CLIENT_SECRET'
        ),

        'redirect' => env(
            'LINKEDIN_REDIRECT_URI',
            '/auth/linkedin/callback'
        ),

    ],

];
