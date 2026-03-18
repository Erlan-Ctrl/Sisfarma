<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | "local": respostas por busca + regras (sem chamadas externas)
    | "openai": usa a API da OpenAI quando OPENAI_API_KEY estiver configurada
    | "ollama": roda um modelo local via Ollama (gratuito, sem API externa)
    |
    */
    'provider' => env('AI_PROVIDER', 'local'),

    // When an external/local provider is offline (connection refused/timeout), avoid retrying every request.
    // This keeps the UI fast and prevents repeated delays during checkout.
    'cooldown_seconds' => (int) env('AI_PROVIDER_COOLDOWN', 30),

    'auto_learn' => [
        'enabled' => (bool) env('AI_AUTO_LEARN', true),
        'max_suggestions' => (int) env('AI_AUTO_LEARN_MAX', 3),

        // Por padrao, o que for "aprendido" fica como rascunho (is_active = false).
        'default_active' => (bool) env('AI_AUTO_LEARN_ACTIVE', false),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),
        'temperature' => (float) env('AI_OPENAI_TEMPERATURE', 0.2),
        'timeout' => (int) env('AI_OPENAI_TIMEOUT', 30),
        'base_url' => env('AI_OPENAI_BASE_URL', 'https://api.openai.com'),
        'verify_ssl' => (bool) env('AI_OPENAI_VERIFY_SSL', true),
        'ca_bundle' => env('AI_OPENAI_CA_BUNDLE'),
    ],

    'ollama' => [
        // Ex.: http://127.0.0.1:11434
        'url' => env('AI_OLLAMA_URL', 'http://127.0.0.1:11434'),
        // Ex.: llama3.1:8b, qwen2.5:7b, mistral:7b
        'model' => env('AI_OLLAMA_MODEL', 'llama3.1:8b'),
        'temperature' => (float) env('AI_OLLAMA_TEMPERATURE', 0.2),
        'timeout' => (int) env('AI_OLLAMA_TIMEOUT', 60),
        // How long to wait to establish TCP connection to the Ollama server.
        'connect_timeout' => (float) env('AI_OLLAMA_CONNECT_TIMEOUT', 1.2),
        // Perf knobs (all optional). Lower values => faster, but may reduce answer quality.
        'keep_alive' => env('AI_OLLAMA_KEEP_ALIVE', '10m'),
        'num_ctx' => (int) env('AI_OLLAMA_NUM_CTX', 2048),
        'num_predict' => (int) env('AI_OLLAMA_NUM_PREDICT', 384),
        'top_k' => (int) env('AI_OLLAMA_TOP_K', 40),
        'top_p' => (float) env('AI_OLLAMA_TOP_P', 0.9),
        'repeat_penalty' => (float) env('AI_OLLAMA_REPEAT_PENALTY', 1.1),
        'seed' => env('AI_OLLAMA_SEED'),
        'num_thread' => env('AI_OLLAMA_NUM_THREAD'),

        // Prompt sizing (biggest latency lever when running locally).
        'history_limit' => (int) env('AI_OLLAMA_HISTORY', 8),
        'message_char_limit' => (int) env('AI_OLLAMA_MSG_CHAR_LIMIT', 800),
    ],
];
