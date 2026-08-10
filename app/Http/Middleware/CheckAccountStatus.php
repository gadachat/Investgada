<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CheckAccountStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->status === 'suspended' || $user->status === 'banned') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $user->status === 'banned'
                ? 'Your account has been banned. Contact support for assistance.'
                : 'Your account has been suspended. Contact support for assistance.';

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
