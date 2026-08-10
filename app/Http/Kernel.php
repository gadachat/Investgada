<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Http\Middleware\TrustProxies::class,
        \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareAliases = [
        'auth'             => \App\Http\Middleware\Authenticate::class,
        'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'role'             => \App\Http\Middleware\CheckRole::class,
        'feature'          => \App\Http\Middleware\CheckFeature::class,
        'kyc'              => \App\Http\Middleware\EnsureKycVerified::class,
        'maintenance'      => \App\Http\Middleware\CheckMaintenance::class,
        'account.status'   => \App\Http\Middleware\CheckAccountStatus::class,
        'security.gate'   => \App\Http\Middleware\SecurityGate::class,
        'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\CheckMaintenance::class,
        ],
        'api' => [
            'throttle:60,1',
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];
}
