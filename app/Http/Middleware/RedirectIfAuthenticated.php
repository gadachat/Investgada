<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, \Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                if ($user && in_array($user->role, ['admin', 'super_admin'])) {
                    return redirect()->route('admin.dashboard');
                }
                return redirect()->route('dashboard.index');
            }
        }

        return $next($request);
    }
}
