<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityLog;
use App\Models\LoginAttempt;
use App\Models\BlockedIp;
use App\Models\ActiveSession;
use App\Models\SecuritySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AdminSecurityController extends Controller
{
    /**
     * Security Dashboard — overview of all security metrics.
     */
    public function index()
    {
        $today = now()->startOfDay();

        // Stats cards
        $stats = [
            'failed_logins_today'    => LoginAttempt::where('successful', false)->where('created_at', '>=', $today)->count(),
            'successful_logins_today' => LoginAttempt::where('successful', true)->where('created_at', '>=', $today)->count(),
            'blocked_ips'             => BlockedIp::where('type', 'blocked')->where('is_active', true)->count(),
            'whitelisted_ips'         => BlockedIp::where('type', 'whitelisted')->where('is_active', true)->count(),
            'active_sessions'         => ActiveSession::where('last_activity', '>=', now()->subMinutes(15))->distinct('user_id')->count(),
            'critical_events_today'   => SecurityLog::whereIn('severity', ['critical', 'danger'])->where('created_at', '>=', $today)->count(),
            'total_audit_logs'        => SecurityLog::count(),
            'suspended_users'         => User::whereIn('status', ['suspended', 'banned'])->count(),
        ];

        // Recent login attempts (last 50)
        $recentLogins = LoginAttempt::with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Recent security logs (last 50)
        $recentLogs = SecurityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Failed login chart data (last 7 days)
        $loginChartData = LoginAttempt::selectRaw('DATE(created_at) as date, successful, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date', 'successful')
            ->orderBy('date')
            ->get();

        $successData = [];
        $failedData = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('D');
            $successData[] = $loginChartData->where('date', $date)->where('successful', true)->sum('count');
            $failedData[]  = $loginChartData->where('date', $date)->where('successful', false)->sum('count');
        }

        // Top IPs by failed attempts
        $topBlockedIps = LoginAttempt::selectRaw('ip_address, COUNT(*) as attempts, MAX(created_at) as last_attempt')
            ->where('successful', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit(10)
            ->get();

        // Active admin sessions
        $adminSessions = ActiveSession::with('user')
            ->whereHas('user', fn ($q) => $q->whereIn('role', ['admin', 'super_admin']))
            ->where('last_activity', '>=', now()->subMinutes(15))
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get();

        return view('admin.security.dashboard', compact(
            'stats', 'recentLogins', 'recentLogs',
            'labels', 'successData', 'failedData',
            'topBlockedIps', 'adminSessions'
        ));
    }

    /**
     * Audit Trail — full security log viewer with filtering.
     */
    public function auditTrail(Request $request)
    {
        $query = SecurityLog::with('user');

        // Filters
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        $modules = SecurityLog::distinct('module')->whereNotNull('module')->pluck('module')->filter();
        $severities = ['info', 'warning', 'critical', 'danger'];

        return view('admin.security.audit-trail', compact('logs', 'modules', 'severities'));
    }

    /**
     * IP Management — block, whitelist, and view IPs.
     */
    public function ipManagement()
    {
        $blockedIps = BlockedIp::where('type', 'blocked')->with('blockedBy')->orderByDesc('created_at')->get();
        $whitelistedIps = BlockedIp::where('type', 'whitelisted')->with('blockedBy')->orderByDesc('created_at')->get();

        // Recent IPs from login attempts
        $recentIps = LoginAttempt::selectRaw('ip_address, COUNT(*) as attempts,
                SUM(CASE WHEN successful = 0 THEN 1 ELSE 0 END) as failed,
                MAX(created_at) as last_seen')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit(20)
            ->get();

        return view('admin.security.ip-management', compact('blockedIps', 'whitelistedIps', 'recentIps'));
    }

    /**
     * Block an IP address.
     */
    public function blockIp(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason'     => 'nullable|string|max:255',
            'duration'   => 'nullable|in:1h,6h,24h,7d,permanent',
        ]);

        $expiresAt = match ($request->duration) {
            '1h'   => now()->addHour(),
            '6h'   => now()->addHours(6),
            '24h'  => now()->addDay(),
            '7d'   => now()->addDays(7),
            default => null,
        };

        BlockedIp::updateOrCreate(
            ['ip_address' => $request->ip_address],
            [
                'type'       => 'blocked',
                'reason'     => $request->reason,
                'blocked_by' => auth()->id(),
                'expires_at' => $expiresAt,
                'is_active'  => true,
            ]
        );

        SecurityLog::log(
            action: 'ip_blocked',
            module: 'system',
            description: "Blocked IP: {$request->ip_address}" . ($request->reason ? " — {$request->reason}" : ''),
            severity: 'warning',
            metadata: ['ip' => $request->ip_address, 'reason' => $request->reason, 'duration' => $request->duration]
        );

        return back()->with('success', "IP {$request->ip_address} has been blocked.");
    }

    /**
     * Whitelist an IP address.
     */
    public function whitelistIp(Request $request)
    {
        $request->validate(['ip_address' => 'required|ip', 'reason' => 'nullable|string|max:255']);

        BlockedIp::updateOrCreate(
            ['ip_address' => $request->ip_address],
            [
                'type'       => 'whitelisted',
                'reason'     => $request->reason,
                'blocked_by' => auth()->id(),
                'is_active'  => true,
            ]
        );

        SecurityLog::log(
            action: 'ip_whitelisted',
            module: 'system',
            description: "Whitelisted IP: {$request->ip_address}",
            severity: 'info',
            metadata: ['ip' => $request->ip_address, 'reason' => $request->reason]
        );

        return back()->with('success', "IP {$request->ip_address} has been whitelisted.");
    }

    /**
     * Remove an IP from blocked/whitelisted list.
     */
    public function removeIp(BlockedIp $ip)
    {
        $ipAddress = $ip->ip_address;
        $ip->delete();

        SecurityLog::log(
            action: 'ip_removed',
            module: 'system',
            description: "Removed IP {$ipAddress} from {$ip->type} list",
            severity: 'info',
            metadata: ['ip' => $ipAddress]
        );

        return back()->with('success', "IP {$ipAddress} removed.");
    }

    /**
     * Active Sessions — view and terminate user sessions.
     */
    public function sessions()
    {
        $sessions = ActiveSession::with('user')
            ->orderByDesc('last_activity')
            ->paginate(50);

        return view('admin.security.sessions', compact('sessions'));
    }

    /**
     * Terminate a user session.
     */
    public function terminateSession(ActiveSession $session)
    {
        $userName = $session->user?->name ?? 'Unknown';
        $ipAddress = $session->ip_address;
        $sessionId = $session->session_id;

        // Remove from Laravel session store
        DB::table('sessions')->where('id', $sessionId)->delete();

        $session->delete();

        SecurityLog::log(
            action: 'session_terminated',
            module: 'auth',
            description: "Terminated session for {$userName} (IP: {$ipAddress})",
            severity: 'warning',
            metadata: ['user_id' => $session->user_id, 'ip' => $ipAddress, 'session_id' => $sessionId]
        );

        return back()->with('success', "Session for {$userName} terminated.");
    }

    /**
     * Security Settings page.
     */
    public function settings()
    {
        $groups = [
            'auth'         => 'Authentication & Lockout',
            'session'      => 'Session Management',
            'network'      => 'IP & Network Security',
            'logging'      => 'Audit & Logging',
            'notifications' => 'Security Notifications',
            'transactions' => 'Transaction Security',
        ];

        $settings = [];
        foreach ($groups as $group => $label) {
            $settings[$group] = [
                'label' => $label,
                'items' => SecuritySetting::where('group', $group)->get(),
            ];
        }

        return view('admin.security.settings', compact('settings', 'groups'));
    }

    /**
     * Update security settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate(['settings' => 'required|array']);

        foreach ($request->settings as $key => $value) {
            SecuritySetting::set($key, $value);
        }

        SecurityLog::log(
            action: 'security_settings_updated',
            module: 'settings',
            description: 'Updated security settings',
            severity: 'critical',
            metadata: ['updated_keys' => array_keys($request->settings)]
        );

        return back()->with('success', 'Security settings updated successfully.');
    }

    /**
     * Clear old logs (housekeeping).
     */
    public function clearLogs(Request $request)
    {
        $request->validate(['days' => 'required|integer|min:1|max:365']);

        $cutoff = now()->subDays($request->days);
        $count = SecurityLog::where('created_at', '<', $cutoff)->count();
        LoginAttempt::where('created_at', '<', $cutoff)->delete();
        SecurityLog::where('created_at', '<', $cutoff)->delete();

        SecurityLog::log(
            action: 'logs_cleared',
            module: 'system',
            description: "Cleared {$count} logs older than {$request->days} days",
            severity: 'warning',
            metadata: ['days' => $request->days, 'deleted_count' => $count]
        );

        return back()->with('success', "Cleared {$count} old log entries.");
    }
}
