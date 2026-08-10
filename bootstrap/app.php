<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckFeature;
use App\Http\Middleware\EnsureKycVerified;
use App\Http\Middleware\CheckMaintenance;
use App\Http\Middleware\CheckAccountStatus;
use App\Http\Middleware\SecurityGate;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth'           => Authenticate::class,
            'guest'          => RedirectIfAuthenticated::class,
            'role'           => CheckRole::class,
            'feature'        => CheckFeature::class,
            'kyc'            => EnsureKycVerified::class,
            'maintenance'    => CheckMaintenance::class,
            'account.status' => CheckAccountStatus::class,
            'security.gate'  => SecurityGate::class,
        ]);
        $middleware->web(append: [
            CheckMaintenance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
