<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    'name' => env('APP_NAME', 'Investment Platform'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://localhost'),
    'asset_url' => env('ASSET_URL'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(
        array_map('trim', explode(',', env('APP_PREVIOUS_KEYS', '')))
    ),
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
    ],
    'support_email' => env('SUPPORT_EMAIL', 'support@aptrades.com'),

    'providers' => ServiceProvider::defaultProviders()->merge([
        // Package Service Providers
       PragmaRX\Google2FALaravel\ServiceProvider::class,
        // Application Service Providers
        App\Providers\AppServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        'Google2FA' => PragmaRX\Google2FALaravel\Facade::class,
    ])->toArray(),
];
