<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Server-side Text To Speech
    |--------------------------------------------------------------------------
    |
    | Groq blijft verantwoordelijk voor Whisper + chat.
    | Azure Speech wordt alleen gebruikt om de AI-antwoorden als audio terug
    | te sturen. Daardoor werkt Urdu ook op telefoons zonder lokale Urdu TTS.
    |
    */

    'azure' => [
        'key' => env(
            'AZURE_SPEECH_KEY',
            ''
        ),

        'region' => env(
            'AZURE_SPEECH_REGION',
            ''
        ),

        'output_format' => env(
            'AZURE_SPEECH_OUTPUT_FORMAT',
            'audio-24khz-48kbitrate-mono-mp3'
        ),

        'timeout' => (int) env(
            'AZURE_SPEECH_TIMEOUT',
            30
        ),

        'connect_timeout' => (int) env(
            'AZURE_SPEECH_CONNECT_TIMEOUT',
            10
        ),

        'voices' => [
            'nl' => env(
                'AZURE_SPEECH_VOICE_NL',
                'nl-NL-FennaNeural'
            ),

            'en' => env(
                'AZURE_SPEECH_VOICE_EN',
                'en-US-JennyNeural'
            ),

            'ur' => env(
                'AZURE_SPEECH_VOICE_UR',
                'ur-PK-UzmaNeural'
            ),
        ],
    ],
];
