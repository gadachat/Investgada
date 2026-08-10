<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA setup page (QR code + secret).
     */
    public function setup()
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->route('dashboard.2fa.manage')->with('info', '2FA is already enabled.');
        }

        // Generate a new secret (stored in session until confirmed)
        if (!session('2fa_pending_secret')) {
            $secret = TotpService::generateSecret();
            session(['2fa_pending_secret' => $secret]);
        }

        $secret = session('2fa_pending_secret');
        $qrTag = TotpService::getQrImageTag($secret, $user->email, config('app.name', 'Platform'));

        return view('dashboard.twofactor.setup', compact('secret', 'qrTag'));
    }

    /**
     * Verify the 2FA code and enable 2FA.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secret = session('2fa_pending_secret');

        if (!$secret) {
            return redirect()->route('dashboard.2fa.setup')->with('error', 'Session expired. Please start again.');
        }

        if (!TotpService::verify($secret, $request->code)) {
            return back()->with('error', 'Invalid verification code. Please try again.');
        }

        // Generate recovery codes
        $recoveryCodes = TotpService::generateRecoveryCodes();

        $user->update([
            'two_factor_secret'      => $secret,
            'two_factor_enabled'      => true,
            'two_factor_verified_at'  => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        session()->forget('2fa_pending_secret');

        return redirect()->route('dashboard.2fa.recovery')
            ->with('success', 'Two-factor authentication enabled successfully!')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Show recovery codes after enabling.
     */
    public function recovery()
    {
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return redirect()->route('dashboard.2fa.setup');
        }

        $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);

        if (session('recovery_codes')) {
            $recoveryCodes = session('recovery_codes');
        }

        return view('dashboard.twofactor.recovery', compact('recoveryCodes'));
    }

    /**
     * Show manage 2FA page (disable, regenerate codes).
     */
    public function manage()
    {
        $user = Auth::user();
        return view('dashboard.twofactor.manage', compact('user'));
    }

    /**
     * Disable 2FA (requires password + code).
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'code'     => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Incorrect password.');
        }

        if ($user->two_factor_enabled && !TotpService::verify($user->two_factor_secret, $request->code)) {
            return back()->with('error', 'Invalid 2FA code.');
        }

        $user->update([
            'two_factor_secret'          => null,
            'two_factor_enabled'          => false,
            'two_factor_verified_at'      => null,
            'two_factor_recovery_codes'   => null,
        ]);

        return redirect()->route('dashboard.security')
            ->with('success', 'Two-factor authentication disabled.');
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerate(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!TotpService::verify($user->two_factor_secret, $request->code)) {
            return back()->with('error', 'Invalid 2FA code.');
        }

        $recoveryCodes = TotpService::generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => json_encode($recoveryCodes)]);

        return redirect()->route('dashboard.2fa.recovery')
            ->with('success', 'Recovery codes regenerated.')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Verify a recovery code (used by login flow).
     */
    public static function verifyRecoveryCode($user, string $code): bool
    {
        $codes = json_decode($user->two_factor_recovery_codes ?? '[]', true);
        if (!is_array($codes)) return false;

        $code = strtoupper(trim($code));
        $idx = array_search($code, $codes);

        if ($idx !== false) {
            unset($codes[$idx]);
            $user->update(['two_factor_recovery_codes' => json_encode(array_values($codes))]);
            return true;
        }

        return false;
    }
}
