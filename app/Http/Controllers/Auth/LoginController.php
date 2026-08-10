<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\SecurityLog;
use App\Models\SecuritySetting;
use App\Models\ActiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show the admin-specific login form.
     */
    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    /**
     * Handle admin-only login attempt.
     * Non-admin accounts are rejected at this endpoint.
     */
    public function adminLogin(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password'  => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$field => $request->login, 'password' => $request->password];

        $user = \App\Models\User::where($field, $request->login)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid credentials.')->withInput();
        }

        if (!$user->isAdmin()) {
            return back()->with('error', 'This login page is for administrators only. Please use the regular login.')->withInput();
        }

        if ($user->status !== 'active') {
            return back()->with('error', 'Your account is ' . $user->status . '. Contact the system administrator.')->withInput();
        }

        // Check 2FA
        if ($user->two_factor_enabled) {
            session(['2fa:user:id' => $user->id, '2fa:admin_route' => true]);
            return redirect()->route('2fa.verify');
        }

        Auth::login($user, $request->boolean('remember'));

        // Log login
        \App\Models\LoginAttempt::create([
            'email'      => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => true,
        ]);

        return redirect()->route('admin.dashboard');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $ip = $request->ip();

        // Check if IP is locked out due to too many failed attempts
        $maxAttempts = SecuritySetting::getMaxLoginAttempts();
        $lockMinutes = SecuritySetting::getLockoutMinutes();

        if (LoginAttempt::isIpLocked($ip, $maxAttempts, $lockMinutes)) {
            SecurityLog::log(
                action: 'login_blocked_ip',
                module: 'auth',
                description: "IP {$ip} is locked out after {$maxAttempts} failed attempts",
                severity: 'warning',
                metadata: ['ip' => $ip, 'email' => $request->login]
            );

            return back()->withInput($request->only('login'))
                ->with('error', "Too many failed attempts from your IP. Try again in {$lockMinutes} minutes.");
        }

        // Find the user (for logging purposes, even if credentials are wrong)
        $user = \App\Models\User::where($field, $request->login)->first();

        if (Auth::attempt([
            $field     => $request->login,
            'password' => $request->password,
            'status'   => 'active',
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // ── 2FA Check ──
            if ($user->two_factor_enabled) {
                Auth::logout();
                $request->session()->put('2fa:user_id', $user->id);
                $request->session()->put('2fa:remember', $request->boolean('remember'));

                LoginAttempt::create([
                    'email'      => $request->login,
                    'user_id'    => $user->id,
                    'ip_address' => $ip,
                    'user_agent' => $request->userAgent(),
                    'successful' => true,
                ]);

                return redirect()->route('2fa.verify')
                    ->with('info', 'Enter your 2FA code to continue.');
            }

            // Log successful login
            LoginAttempt::create([
                'email'      => $request->login,
                'user_id'    => $user->id,
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'successful' => true,
            ]);

            SecurityLog::log(
                action: 'login_success',
                module: 'auth',
                description: "User {$user->name} logged in successfully",
                severity: 'info',
                metadata: ['user_id' => $user->id, 'role' => $user->role]
            );

            // Track active session
            ActiveSession::updateOrCreate(
                ['session_id' => $request->session()->getId()],
                [
                    'user_id'       => $user->id,
                    'ip_address'    => $ip,
                    'user_agent'    => $request->userAgent(),
                    'device_type'   => ActiveSession::detectDevice($request->userAgent()),
                    'last_activity' => now(),
                ]
            );

            if ($user->isAdmin()) {
                SecurityLog::log(
                    action: 'admin_login',
                    module: 'auth',
                    description: "Admin {$user->name} accessed admin panel",
                    severity: 'critical',
                    metadata: ['user_id' => $user->id, 'ip' => $ip]
                );
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Log failed login attempt
        $failureReason = 'wrong_password';
        if ($user && $user->status !== 'active') {
            $failureReason = 'account_' . $user->status;
        } elseif (!$user) {
            $failureReason = 'user_not_found';
        }

        LoginAttempt::create([
            'email'          => $request->login,
            'user_id'        => $user?->id,
            'ip_address'     => $ip,
            'user_agent'     => $request->userAgent(),
            'successful'     => false,
            'failure_reason' => $failureReason,
        ]);

        SecurityLog::log(
            action: 'login_failed',
            module: 'auth',
            description: "Failed login attempt for '{$request->login}' — {$failureReason}",
            severity: 'warning',
            metadata: ['email' => $request->login, 'ip' => $ip, 'reason' => $failureReason]
        );

        // Auto-block IP if it exceeds the auto-block threshold
        if (SecuritySetting::isAutoBlockEnabled()) {
            $threshold = SecuritySetting::getAutoBlockThreshold();
            $failedCount = LoginAttempt::countFailedByIp($ip, 60); // last hour

            if ($failedCount >= $threshold) {
                \App\Models\BlockedIp::create([
                    'ip_address' => $ip,
                    'type'       => 'blocked',
                    'reason'     => "Auto-blocked: {$failedCount} failed attempts in 1 hour",
                    'blocked_by' => null,
                    'expires_at' => now()->addHours(24),
                    'is_active'  => true,
                ]);

                SecurityLog::log(
                    action: 'ip_auto_blocked',
                    module: 'auth',
                    description: "IP {$ip} auto-blocked after {$failedCount} failed attempts",
                    severity: 'critical',
                    metadata: ['ip' => $ip, 'attempts' => $failedCount]
                );

                return back()->withInput($request->only('login'))
                    ->with('error', "Your IP has been blocked due to suspicious activity. Contact support.");
            }
        }

        // Check if account is suspended/banned
        if ($user && $user->status !== 'active') {
            return back()->withInput($request->only('login'))
                ->with('error', 'Your account has been ' . $user->status . '. Please contact support.');
        }

        $remaining = $maxAttempts - LoginAttempt::countFailedByIp($ip, $lockMinutes);

        return back()->withInput($request->only('login'))
            ->with('error', 'Invalid credentials. Please try again.' . ($remaining > 0 && $remaining <= 3 ? " {$remaining} attempts remaining before lockout." : ''));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            SecurityLog::log(
                action: 'logout',
                module: 'auth',
                description: "User {$user->name} logged out",
                severity: 'info'
            );

            // Remove active session
            ActiveSession::where('session_id', $request->session()->getId())->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
    /**
     * Show the 2FA verification form.
     */
    public function show2faVerify(Request $request)
    {
        if (!$request->session()->has('2fa:user_id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-verify');
    }

    /**
     * Verify the 2FA code and complete login.
     */
    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired.');
        }

        $user = \App\Models\User::find($userId);
        if (!$user || !$user->two_factor_enabled) {
            $request->session()->forget('2fa:user_id');
            return redirect()->route('login')->with('error', '2FA not enabled for this account.');
        }

        if (!\App\Services\TotpService::verify($user->two_factor_secret, $request->code)) {
            return back()->with('error', 'Invalid 2FA code. Please try again.');
        }

        // Success — complete login
        Auth::login($user, $request->session()->get('2fa:remember', false));
        $request->session()->forget(['2fa:user_id', '2fa:remember']);
        $request->session()->regenerate();

        SecurityLog::log(
            action: '2fa_verified',
            module: 'auth',
            description: "User {$user->name} passed 2FA verification",
            severity: 'info',
            metadata: ['user_id' => $user->id, 'ip' => $request->ip()]
        );

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Verify using a recovery code instead of TOTP.
     */
    public function verify2faRecovery(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa:user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired.');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'Account not found.');
        }

        $ok = \App\Http\Controllers\User\TwoFactorController::verifyRecoveryCode($user, $request->recovery_code);

        if (!$ok) {
            return back()->with('error', 'Invalid recovery code.');
        }

        Auth::login($user, $request->session()->get('2fa:remember', false));
        $request->session()->forget(['2fa:user_id', '2fa:remember']);
        $request->session()->regenerate();

        SecurityLog::log(
            action: '2fa_recovery_used',
            module: 'auth',
            description: "User {$user->name} logged in with a recovery code",
            severity: 'warning',
            metadata: ['user_id' => $user->id, 'ip' => $request->ip()]
        );

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Logged in via recovery code. Please generate new recovery codes.');
    }

}
