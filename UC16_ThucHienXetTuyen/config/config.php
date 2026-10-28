<?php

declare(strict_types=1);

return [
    'app_name' => getenv('APP_NAME') ?: 'UC16 - Thực hiện xét tuyển',
    'app_env' => getenv('APP_ENV') ?: 'local',
    'app_debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Ho_Chi_Minh',
    'db' => [
        'dsn' => getenv('DB_DSN') ?: 'sqlite:' . dirname(__DIR__) . '/storage/admission.sqlite',
        'username' => getenv('DB_USERNAME') ?: null,
        'password' => getenv('DB_PASSWORD') ?: null,
    ],
];

