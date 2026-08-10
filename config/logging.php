<?php

return [

    'default' => env('LOG_CHANNEL', env('LOG_STACK', 'single')),
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],
    'channels' => [
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],
        'stderr' => [
            'driver' => 'stderr',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],
        'null' => [
            'driver' => 'null',
        ],
        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],
];
