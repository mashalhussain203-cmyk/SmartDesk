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
    | worden uitsluitend via environment variables ingeladen.
    |
    | Plaats echte secrets nooit rechtstreeks in dit bestand
    | en commit ze nooit naar GitHub.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Postmark
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env(
            'POSTMARK_API_KEY'
        ),
    ],


    /*
    |--------------------------------------------------------------------------
    | Resend
    |--------------------------------------------------------------------------
    */

    'resend' => [
        'key' => env(
            'RESEND_API_KEY'
        ),
    ],


    /*
    |--------------------------------------------------------------------------
    | Amazon SES
    |--------------------------------------------------------------------------
    */

    'ses' => [

        'key' => env(
            'AWS_ACCESS_KEY_ID'
        ),

        'secret' => env(
            'AWS_SECRET_ACCESS_KEY'
        ),

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
        | Google Redirect URI
        |--------------------------------------------------------------------------
        */

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
    | De GITHUB_REDIRECT_URI moet exact overeenkomen met de
    | Authorization callback URL van de GitHub OAuth App.
    |
    | Eventuele GitHub OAuth-scopes worden in GitHubAuthController
    | ingesteld en niet in dit configuratiebestand.
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
        | GitHub Redirect URI
        |--------------------------------------------------------------------------
        */

        'redirect' => env(
            'GITHUB_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Facebook OAuth
    |--------------------------------------------------------------------------
    |
    | Facebook OAuth wordt gebruikt voor authenticatie via Facebook.
    |
    | Onder andere voor:
    |
    | - Inloggen met Facebook
    | - Registreren met Facebook
    | - Bestaande Mashal-accounts koppelen aan Facebook
    | - Nieuwe gebruikers automatisch registreren
    | - Facebook-profielinformatie ophalen via Laravel Socialite
    |
    | De FACEBOOK_REDIRECT_URI moet exact overeenkomen met de
    | Valid OAuth Redirect URI in Meta for Developers.
    |
    | Productie:
    |
    | https://mashalhussain.up.railway.app/auth/facebook/callback
    |
    | Eventuele Facebook OAuth-scopes worden in
    | FacebookAuthController ingesteld.
    |
    */

    'facebook' => [

        /*
        |--------------------------------------------------------------------------
        | Facebook App ID
        |--------------------------------------------------------------------------
        |
        | Dit is de App ID van je Mashal Automotive-app in
        | Meta for Developers.
        |
        */

        'client_id' => env(
            'FACEBOOK_CLIENT_ID'
        ),


        /*
        |--------------------------------------------------------------------------
        | Facebook App Secret
        |--------------------------------------------------------------------------
        |
        | Het App Secret hoort uitsluitend in .env of Railway Variables.
        |
        */

        'client_secret' => env(
            'FACEBOOK_CLIENT_SECRET'
        ),


        /*
        |--------------------------------------------------------------------------
        | Facebook Redirect URI
        |--------------------------------------------------------------------------
        */

        'redirect' => env(
            'FACEBOOK_REDIRECT_URI'
        ),

    ],


    /*
    |--------------------------------------------------------------------------
    | Brevo
    |--------------------------------------------------------------------------
    |
    | Mashal Automotive gebruikt Brevo voor transactionele e-mail.
    |
    | Bijvoorbeeld voor:
    |
    | - Welkomstmails
    | - Accountmeldingen
    | - Wachtwoordherstel
    | - Bestelbevestigingen
    | - Beveiligingsmeldingen
    |
    | E-mails worden via de Brevo HTTPS API verzonden.
    |
    */

    'brevo' => [

        /*
        |--------------------------------------------------------------------------
        | Brevo API Key
        |--------------------------------------------------------------------------
        |
        | Deze waarde mag uitsluitend via BREVO_API_KEY worden geladen.
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
        | Voorkeursvolgorde:
        |
        | 1. BREVO_FROM_EMAIL
        | 2. MAIL_FROM_ADDRESS
        |
        */

        'from_email' => env(
            'BREVO_FROM_EMAIL',
            env(
                'MAIL_FROM_ADDRESS'
            )
        ),


        /*
        |--------------------------------------------------------------------------
        | Sender Name
        |--------------------------------------------------------------------------
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
        | Brevo API Base URL
        |--------------------------------------------------------------------------
        */

        'base_url' => env(
            'BREVO_BASE_URL',
            'https://api.brevo.com/v3'
        ),

    ],

];