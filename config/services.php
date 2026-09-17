<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Hier worden alle externe diensten geconfigureerd die binnen
    | Mashal Automotive worden gebruikt.
    |
    | Gevoelige gegevens zoals API keys, OAuth-secrets en tokens
    | worden uitsluitend via het .env-bestand ingeladen.
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
    | Google OAuth
    |--------------------------------------------------------------------------
    |
    | Google OAuth wordt gebruikt voor authenticatie via Google.
    |
    | Onder andere voor:
    |
    | - Inloggen met Google
    | - Registreren met Google
    | - Bestaande Mashal-accounts koppelen aan Google
    | - Nieuwe gebruikers automatisch registreren
    | - Google-profielinformatie ophalen via Laravel Socialite
    |
    | De GOOGLE_REDIRECT_URI moet exact overeenkomen met de
    | Authorized redirect URI in Google Cloud Console.
    |
    */

    'google' => [

        'client_id' => env(
            'GOOGLE_CLIENT_ID'
        ),

        'client_secret' => env(
            'GOOGLE_CLIENT_SECRET'
        ),

        'redirect' => env(
            'GOOGLE_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | GitHub OAuth
    |--------------------------------------------------------------------------
    |
    | GitHub OAuth wordt gebruikt voor authenticatie via GitHub.
    |
    | Onder andere voor:
    |
    | - Inloggen met GitHub
    | - Registreren met GitHub
    | - Bestaande Mashal-accounts koppelen aan GitHub
    | - Nieuwe gebruikers automatisch registreren
    | - GitHub-profielinformatie ophalen via Laravel Socialite
    |
    | GitHub wordt standaard ondersteund door Laravel Socialite.
    |
    | De GITHUB_REDIRECT_URI moet exact overeenkomen met de
    | Authorization callback URL van de GitHub OAuth App.
    |
    */

    'github' => [

        /*
        |--------------------------------------------------------------------------
        | GitHub Client ID
        |--------------------------------------------------------------------------
        */

        'client_id' => env(
            'GITHUB_CLIENT_ID'
        ),


        /*
        |--------------------------------------------------------------------------
        | GitHub Client Secret
        |--------------------------------------------------------------------------
        */

        'client_secret' => env(
            'GITHUB_CLIENT_SECRET'
        ),


        /*
        |--------------------------------------------------------------------------
        | GitHub OAuth Redirect URI
        |--------------------------------------------------------------------------
        */

        'redirect' => env(
            'GITHUB_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Brevo
    |--------------------------------------------------------------------------
    |
    | Mashal Automotive gebruikt Brevo voor transactionele e-mail.
    |
    | E-mails worden via de Brevo HTTPS API verstuurd in plaats van
    | rechtstreeks via SMTP.
    |
    | Dit is geschikt voor hostingomgevingen waar SMTP-poorten beperkt
    | of geblokkeerd kunnen zijn, zoals bepaalde cloudplatforms.
    |
    */

    'brevo' => [

        /*
        |--------------------------------------------------------------------------
        | API Key
        |--------------------------------------------------------------------------
        |
        | De persoonlijke Brevo API-key.
        |
        | Deze waarde mag nooit rechtstreeks in deze configuratie worden
        | geplaatst en hoort uitsluitend in het .env-bestand.
        |
        */

        'api_key' => env(
            'BREVO_API_KEY'
        ),


        /*
        |--------------------------------------------------------------------------
        | Sender E-mail Address
        |--------------------------------------------------------------------------
        |
        | Het e-mailadres dat Brevo gebruikt als afzender.
        |
        | Voorkeursvolgorde:
        |
        | 1. BREVO_FROM_EMAIL
        | 2. MAIL_FROM_ADDRESS
        |
        */

        'from_email' => env(
            'BREVO_FROM_EMAIL',
            env('MAIL_FROM_ADDRESS')
        ),


        /*
        |--------------------------------------------------------------------------
        | Sender Name
        |--------------------------------------------------------------------------
        |
        | De zichtbare afzendernaam in e-mails.
        |
        | Voorkeursvolgorde:
        |
        | 1. BREVO_FROM_NAME
        | 2. MAIL_FROM_NAME
        | 3. Mashal Automotive
        |
        */

        'from_name' => env(
            'BREVO_FROM_NAME',
            env(
                'MAIL_FROM_NAME',
                'Mashal Automotive'
            )
        ),


        /*
        |--------------------------------------------------------------------------
        | API Base URL
        |--------------------------------------------------------------------------
        |
        | Standaard wordt de officiële Brevo API v3 gebruikt.
        |
        */

        'base_url' => env(
            'BREVO_BASE_URL',
            'https://api.brevo.com/v3'
        ),

    ],

];