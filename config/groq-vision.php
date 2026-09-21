<?php

return [
    'api_key' => env(
        'GROQ_API_KEY',
        ''
    ),

    'endpoint' => env(
        'GROQ_VISION_ENDPOINT',
        'https://api.groq.com/openai/v1/chat/completions'
    ),

    'model' => env(
        'GROQ_VISION_MODEL',
        'qwen/qwen3.8-27b'
    ),

    'timeout' => (int) env(
        'GROQ_VISION_TIMEOUT',
        90
    ),

    'connect_timeout' => (int) env(
        'GROQ_VISION_CONNECT_TIMEOUT',
        10
    ),

    'max_completion_tokens' => (int) env(
        'GROQ_VISION_MAX_TOKENS',
        2600
    ),

    'pdf' => [
        'enabled' => env(
            'GROQ_PDF_VISION_ENABLED',
            true
        ),

        // Tekst-PDF: visueel de eerste pagina's controleren voor grafieken/layout.
        'visual_pages_with_text' => (int) env(
            'GROQ_PDF_VISUAL_PAGES',
            3
        ),

        // Scan-PDF: OCR/vision op meer pagina's, in batches van maximaal 3.
        'max_scanned_pages' => (int) env(
            'GROQ_PDF_MAX_SCANNED_PAGES',
            6
        ),
    ],
];
