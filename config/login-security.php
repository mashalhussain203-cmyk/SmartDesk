<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Loginbeveiliging aan/uit
    |--------------------------------------------------------------------------
    */

    'enabled' => env(
        'LOGIN_SECURITY_ENABLED',
        true
    ),


    /*
    |--------------------------------------------------------------------------
    | Bewaartermijn
    |--------------------------------------------------------------------------
    |
    | IP-adressen en geschatte locatie zijn persoonsgegevens.
    | Daarom wordt oude loginhistorie automatisch verwijderd.
    |
    */

    'retention_days' => env(
        'LOGIN_SECURITY_RETENTION_DAYS',
        90
    ),


    /*
    |--------------------------------------------------------------------------
    | Maximum aantal regels op Mijn account -> Beveiliging
    |--------------------------------------------------------------------------
    */

    'history_limit' => env(
        'LOGIN_SECURITY_HISTORY_LIMIT',
        50
    ),


    /*
    |--------------------------------------------------------------------------
    | Login e-mail via bestaande BrevoMailService
    |--------------------------------------------------------------------------
    */

    'email' => [
        'enabled' => env(
            'LOGIN_SECURITY_SEND_EMAIL',
            true
        ),
    ],


    /*
    |--------------------------------------------------------------------------
    | IP-geolocatie
    |--------------------------------------------------------------------------
    |
    | De locatie is ALTIJD slechts een schatting op basis van het IP-adres.
    | Deze informatie mag nooit gebruikt worden om automatisch toegang te
    | blokkeren of een identiteit vast te stellen.
    |
    | De standaard endpoint gebruikt ipapi.co en vereist voor eenvoudig
    | gebruik geen extra API-key. Controleer voor groter productiegebruik
    | altijd de actuele limieten/voorwaarden van je gekozen provider.
    |
    */

    'geolocation' => [
        'enabled' => env(
            'LOGIN_SECURITY_GEOLOCATION_ENABLED',
            true
        ),

        'url' => env(
            'LOGIN_SECURITY_GEOLOCATION_URL',
            'https://ipapi.co/{ip}/json/'
        ),

        'timeout_seconds' => env(
            'LOGIN_SECURITY_GEOLOCATION_TIMEOUT',
            4
        ),

        'cache_hours' => env(
            'LOGIN_SECURITY_GEOLOCATION_CACHE_HOURS',
            24
        ),
    ],

];
