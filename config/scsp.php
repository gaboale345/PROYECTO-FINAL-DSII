<?php

return [
    'whatsapp' => [
        'token' => env('WHATSAPP_API_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'scsp_webhook'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'default_chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    'canales' => [
        1 => 'web',
        2 => 'whatsapp',
        3 => 'telegram',
        4 => 'email',
    ],

    'backup' => [
        'path' => storage_path('app/backups'),
        'retention_days' => 30,
    ],

    'ml' => [
        'python_path' => env('PYTHON_PATH', 'python'),
        'model_path' => base_path('ml/modelo_predictivo.json'),
    ],
];
