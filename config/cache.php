<?php

return [

    'default' => env('CACHE_STORE', 'database'),
    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CONNECTION'),
            'table' => 'cache',
            'lock_connection' => env('DB_CONNECTION'),
            'lock_table' => 'cache_locks',
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
    ],
    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),
];
