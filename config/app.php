<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | De naam van de applicatie.
    |
    */

    'name' => env(
        'APP_NAME',
        'Mashal Automotive'
    ),


    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | Bijvoorbeeld:
    |
    | production
    | local
    | staging
    |
    */

    'env' => env(
        'APP_ENV',
        'production'
    ),


    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | Op productie hoort APP_DEBUG=false te staan.
    |
    */

    'debug' => (bool) env(
        'APP_DEBUG',
        false
    ),


    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | Op Railway bijvoorbeeld:
    |
    | https://mashalhussain.up.railway.app
    |
    */

    'url' => env(
        'APP_URL',
        'http://localhost'
    ),


    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Mashal Automotive gebruikt standaard Nederlandse tijd.
    |
    | Europe/Amsterdam regelt automatisch:
    |
    | - CET in de winter
    | - CEST in de zomer
    | - zomer-/wintertijd
    |
    | In Railway kun je optioneel instellen:
    |
    | APP_TIMEZONE=Europe/Amsterdam
    |
    */

    'timezone' => env(
        'APP_TIMEZONE',
        'Europe/Amsterdam'
    ),


    /*
    |--------------------------------------------------------------------------
    | Application Locale
    |--------------------------------------------------------------------------
    |
    | De standaardtaal van de applicatie.
    |
    */

    'locale' => env(
        'APP_LOCALE',
        'nl'
    ),


    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | Als een Nederlandse vertaling ontbreekt,
    | kan Laravel terugvallen op Engels.
    |
    */

    'fallback_locale' => env(
        'APP_FALLBACK_LOCALE',
        'en'
    ),


    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | Gebruikt voor testdata / factories.
    |
    */

    'faker_locale' => env(
        'APP_FAKER_LOCALE',
        'nl_NL'
    ),


    /*
    |--------------------------------------------------------------------------
    | Encryption Cipher
    |--------------------------------------------------------------------------
    */

    'cipher' => 'AES-256-CBC',


    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | Deze waarde komt uit APP_KEY.
    |
    | Deel APP_KEY nooit openbaar.
    |
    */

    'key' => env(
        'APP_KEY'
    ),


    /*
    |--------------------------------------------------------------------------
    | Previous Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Hiermee kan Laravel oude versleutelde data blijven lezen
    | nadat APP_KEY ooit is gewijzigd.
    |
    */

    'previous_keys' => [
        ...array_filter(
            explode(
                ',',
                (string) env(
                    'APP_PREVIOUS_KEYS',
                    ''
                )
            )
        ),
    ],


    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode
    |--------------------------------------------------------------------------
    |
    | Mogelijke drivers:
    |
    | file
    | cache
    | array
    |
    */

    'maintenance' => [

        'driver' => env(
            'APP_MAINTENANCE_DRIVER',
            'file'
        ),

        'store' => env(
            'APP_MAINTENANCE_STORE',
            'database'
        ),

    ],

];