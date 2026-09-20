<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Background Removal
    |--------------------------------------------------------------------------
    |
    | Instellingen voor echte achtergrondverwijdering via een externe provider.
    | De API-key hoort in .env / Railway Variables en nooit direct in deze file.
    |
    */

    'provider' => env(
        'BACKGROUND_REMOVAL_PROVIDER',
        'remove_bg'
    ),

    'remove_bg' => [

        'api_key' => env(
            'REMOVE_BG_API_KEY'
        ),

        'endpoint' => env(
            'REMOVE_BG_ENDPOINT',
            'https://api.remove.bg/v1.0/removebg'
        ),

        'timeout' => (int) env(
            'REMOVE_BG_TIMEOUT',
            90
        ),

        'connect_timeout' => (int) env(
            'REMOVE_BG_CONNECT_TIMEOUT',
            10
        ),

        /*
         * Maximale grootte van het inputbestand.
         */
        'max_input_bytes' => (int) env(
            'REMOVE_BG_MAX_INPUT_BYTES',
            22 * 1024 * 1024
        ),

        /*
         * PNG behoudt transparantie.
         * WebP kan ook transparantie bevatten.
         */
        'default_format' => env(
            'REMOVE_BG_DEFAULT_FORMAT',
            'png'
        ),

        /*
         * Resolutie-optie voor de provider.
         */
        'default_size' => env(
            'REMOVE_BG_DEFAULT_SIZE',
            'auto'
        ),
    ],
];
