<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BlockedIp;
use App\Models\SecuritySetting;

class SecurityGate
{
    /**
     * Block blacklisted IPs, enforce IP whitelist, and protect against
     * brute-force login attempts.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // 1. Check IP blacklist
        if (SecuritySetting::isIpBlacklistEnabled() && BlockedIp::isBlocked($ip)) {
            abort(403, 'Your IP address has been blocked for security reasons. Contact support if you believe this is an error.');
        }

        // 2. Check IP whitelist (if enabled, only whitelisted IPs can access)
        if (BlockedIp::isNotWhitelisted($ip)) {
            abort(403, 'Access restricted. Your IP is not on the whitelist.');
        }

        return $next($request);
    }
}
