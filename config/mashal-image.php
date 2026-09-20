<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Passport / ID photo presets
    |--------------------------------------------------------------------------
    |
    | Deze presets worden gebruikt door de Pasfoto/ID-foto editor.
    | De waarden zijn exportdoelen in pixels.
    |
    | De visuele guides zijn hulpmiddelen en vormen geen automatische
    | officiële goedkeuring voor paspoort-, ID- of visumfoto's.
    |
    */

    'passport_presets' => [

        'nl_35x45' => [
            'label' => 'Nederland · 35 × 45 mm',
            'short_label' => 'NL 35×45',
            'width' => 413,
            'height' => 531,
            'description' => 'Staande pasfotoverhouding 35:45.',
        ],

        'us_2x2' => [
            'label' => 'Verenigde Staten · 2 × 2 inch',
            'short_label' => 'US 2×2',
            'width' => 600,
            'height' => 600,
            'description' => 'Vierkante ID/paspoortfoto-export van 600 × 600 px.',
        ],

        'square_800' => [
            'label' => 'Vierkante ID-foto · 800 × 800 px',
            'short_label' => 'ID square',
            'width' => 800,
            'height' => 800,
            'description' => 'Algemene vierkante profielfoto/ID-export.',
        ],

        'portrait_600x800' => [
            'label' => 'Portret ID · 600 × 800 px',
            'short_label' => 'ID portrait',
            'width' => 600,
            'height' => 800,
            'description' => 'Algemene staande ID-fotoverhouding 3:4.',
        ],
    ],
];
