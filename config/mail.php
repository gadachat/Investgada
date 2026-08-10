<?php

return [

    'default' => env('MAIL_MAILER', 'log'),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => env('MAIL_TIMEOUT', 60),
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -t'),
        ],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@platform.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Investment Platform')),
    ],
];
