<?php

return [
    // Keep the current working Groq chat until OpenAI is explicitly enabled.
    // Railway: MASHAL_AI_PROVIDER=openai and OPENAI_API_KEY=sk-...
    'provider' => env('MASHAL_AI_PROVIDER', 'groq'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
        'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
        'timeout' => max(30, min(300, (int) env('OPENAI_TIMEOUT', 120))),
        'max_output_tokens' => max(512, min(8192, (int) env('OPENAI_MAX_OUTPUT_TOKENS', 3000))),
        'auto_web_search' => env('OPENAI_AUTO_WEB_SEARCH', false),
    ],
];
