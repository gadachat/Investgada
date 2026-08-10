<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FeatureSetting;

class CheckFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!FeatureSetting::isEnabled($feature)) {
            abort(404, 'This feature is currently disabled.');
        }

        return $next($request);
    }
}
