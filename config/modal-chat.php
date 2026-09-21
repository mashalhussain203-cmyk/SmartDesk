<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modal chat endpoint
    |--------------------------------------------------------------------------
    |
    | Gebruik een OpenAI-compatible Modal endpoint.
    |
    | Voorbeeld:
    | https://jouw-server.modal.direct/v1
    |
    | ModalChatService mag hier zelf /chat/completions achter zetten wanneer
    | de URL eindigt op /v1.
    |
    */
    'endpoint' => env(
        'MODAL_CHAT_ENDPOINT',
        ''
    ),

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Optie A:
    | MODAL_CHAT_API_KEY gebruikt:
    | Authorization: Bearer <token>
    |
    | Optie B:
    | MODAL_CHAT_PROXY_KEY + MODAL_CHAT_PROXY_SECRET gebruiken:
    | Modal-Key: ...
    | Modal-Secret: ...
    |
    | Houd deze waarden uitsluitend server-side in Railway/.env.
    |
    */
    'api_key' => env(
        'MODAL_CHAT_API_KEY',
        ''
    ),

    'proxy_key' => env(
        'MODAL_CHAT_PROXY_KEY',
        ''
    ),

    'proxy_secret' => env(
        'MODAL_CHAT_PROXY_SECRET',
        ''
    ),

    'allow_unauthenticated' => filter_var(
        env(
            'MODAL_CHAT_ALLOW_UNAUTHENTICATED',
            false
        ),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | Laat MODAL_CHAT_MODEL leeg wanneer het Modal endpoint zelf al één model
    | vastlegt.
    |
    */
    'model' => trim(
        (string) env(
            'MODAL_CHAT_MODEL',
            ''
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | HTTP timeouts
    |--------------------------------------------------------------------------
    */
    'timeout' => max(
        5,
        (int) env(
            'MODAL_CHAT_TIMEOUT',
            120
        )
    ),

    'connect_timeout' => max(
        1,
        (int) env(
            'MODAL_CHAT_CONNECT_TIMEOUT',
            10
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | Model generation
    |--------------------------------------------------------------------------
    */
    'max_tokens' => max(
        1,
        (int) env(
            'MODAL_CHAT_MAX_TOKENS',
            1200
        )
    ),

    'temperature' => max(
        0.0,
        min(
            2.0,
            (float) env(
                'MODAL_CHAT_TEMPERATURE',
                0.7
            )
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | Provider rate-limit cooldown
    |--------------------------------------------------------------------------
    |
    | Als Modal HTTP 429 teruggeeft, zet AiChatController deze gebruiker
    | tijdelijk in cooldown. Dit voorkomt dat voice-chat, dubbelklikken of
    | snelle herhaalverzoeken de provider blijven raken.
    |
    | Minimum in de controller: 5 seconden
    | Maximum in de controller: 300 seconden
    |
    */
    'rate_limit_cooldown' => max(
        5,
        min(
            300,
            (int) env(
                'MODAL_CHAT_RATE_LIMIT_COOLDOWN',
                30
            )
        )
    ),

    /*
    |--------------------------------------------------------------------------
    | Mashal AI system prompt
    |--------------------------------------------------------------------------
    */
    'system_prompt' => env(
        'MODAL_CHAT_SYSTEM_PROMPT',
        'Je bent Mashal AI, een behulpzame assistent. Antwoord duidelijk, compact en in dezelfde taal als de gebruiker.'
    ),
];
