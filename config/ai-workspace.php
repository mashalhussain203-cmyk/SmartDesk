<?php

return [
    'enabled' => (bool) env('AI_WORKSPACE_ENABLED', true),
    'disk' => env('AI_WORKSPACE_DISK', 'local'),

    'max_conversations' => (int) env('AI_WORKSPACE_MAX_CONVERSATIONS', 100),
    'max_messages_per_conversation' => (int) env('AI_WORKSPACE_MAX_MESSAGES', 200),

    'retrieval' => [
        'enabled' => (bool) env('AI_WORKSPACE_RAG_ENABLED', true),
        'max_documents_to_scan' => (int) env('AI_WORKSPACE_RAG_SCAN_DOCS', 40),
        'max_sources' => (int) env('AI_WORKSPACE_RAG_MAX_SOURCES', 5),
        'excerpt_chars' => (int) env('AI_WORKSPACE_RAG_EXCERPT_CHARS', 3500),
        'max_context_chars' => (int) env('AI_WORKSPACE_RAG_MAX_CONTEXT_CHARS', 14000),
    ],

    'memory' => [
        'enabled' => (bool) env('AI_WORKSPACE_MEMORY_ENABLED', true),
        'max_items' => (int) env('AI_WORKSPACE_MEMORY_MAX_ITEMS', 20),
        'max_chars' => (int) env('AI_WORKSPACE_MEMORY_MAX_CHARS', 5000),
    ],

    'sharing' => [
        'enabled' => (bool) env('AI_WORKSPACE_SHARING_ENABLED', true),
        'default_expiry_days' => (int) env('AI_WORKSPACE_SHARE_DAYS', 30),
    ],
];
