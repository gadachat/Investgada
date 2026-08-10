<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PlatformSetting;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip maintenance check during installation
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Skip if database/table not available (pre-install state)
        try {
            $maintenanceMode = PlatformSetting::get('maintenance_mode', 'false') === 'true';
        } catch (\Exception $e) {
            // Database not configured yet — allow request through
            return $next($request);
        }

        if (!$maintenanceMode) {
            return $next($request);
        }

        // Allow admins to access during maintenance
        if ($request->user() && $request->user()->isAdmin()) {
            return $next($request);
        }

        // Allow access to login/logout
        if ($request->routeIs('login', 'logout')) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [], 503);
    }
}
