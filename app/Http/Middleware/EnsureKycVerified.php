<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FeatureSetting;

class EnsureKycVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins bypass KYC
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if KYC feature is enabled — if off, skip entirely
        $kycEnabled = FeatureSetting::where('key', 'kyc')->value('is_enabled');
        if (!$kycEnabled) {
            return $next($request);
        }

        if ($user->kyc_status !== 'verified') {
            return redirect()->route('kyc.status')
                ->with('error', 'You must complete KYC verification before proceeding.');
        }

        return $next($request);
    }
}
