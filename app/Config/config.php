<?php
return [
    'app' => [
        'name' => 'TouchGrass AI',
        'env' => getenv('APP_ENV') ?: 'local',
        'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOL),
        'url' => getenv('APP_URL') ?: 'http://localhost:8081',
    ],
    'db' => ['path' => getenv('DB_PATH') ?: __DIR__ . '/../../storage/touchgrass.sqlite'],
    'ai' => [
        'url' => rtrim(getenv('OLLAMA_URL') ?: 'http://ollama:11434', '/'),
        'model' => getenv('OLLAMA_MODEL') ?: 'gemma3:4b',
        'timeout' => (int)(getenv('AI_TIMEOUT') ?: 120),
    ],
];
