<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modal chat endpoint
    |--------------------------------------------------------------------------
    |
    | Mag een volledige OpenAI-compatible /v1/chat/completions URL zijn,
    | of een base URL die eindigt op /v1.
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
    | Optie A: één API key via Authorization: Bearer ...
    | Optie B: Modal Proxy Auth met Modal-Key + Modal-Secret.
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

    'allow_unauthenticated' => (bool) env(
        'MODAL_CHAT_ALLOW_UNAUTHENTICATED',
        false
    ),

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | Laat leeg als jouw Modal endpoint zelf al één model vastlegt.
    |
    */
    'model' => env(
        'MODAL_CHAT_MODEL',
        ''
    ),

    'timeout' => (int) env(
        'MODAL_CHAT_TIMEOUT',
        120
    ),

    'connect_timeout' => (int) env(
        'MODAL_CHAT_CONNECT_TIMEOUT',
        10
    ),

    'max_tokens' => (int) env(
        'MODAL_CHAT_MAX_TOKENS',
        1200
    ),

    'temperature' => (float) env(
        'MODAL_CHAT_TEMPERATURE',
        0.7
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
