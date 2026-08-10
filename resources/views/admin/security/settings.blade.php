@extends('layouts.admin')

@section('page-title', 'Security Settings')

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 4px; font-size: 22px;">
                <i class="fas fa-lock" style="color: #a855f7;"></i> Security Settings
            </h2>
            <p style="color: var(--text-muted); font-size: 13px; margin: 0;">Configure authentication, sessions, IP protection, and audit logging.</p>
        </div>
        <a href="{{ route('admin.security.index') }}" class="btn btn-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text); border-radius: 10px; padding: 8px 16px; font-size: 12px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; border-radius: 12px; padding: 12px 20px; margin-bottom: 20px; font-size: 14px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.security.update-settings') }}">
        @csrf

        @php
            $settingMeta = [
                'max_login_attempts'                => ['label' => 'Max Login Attempts', 'type' => 'number', 'desc' => 'Failed login attempts before account lockout'],
                'lockout_duration_minutes'         => ['label' => 'Lockout Duration (minutes)', 'type' => 'number', 'desc' => 'How long an account stays locked after max attempts'],
                'require_2fa_admin'                => ['label' => 'Require 2FA for Admins', 'type' => 'toggle', 'desc' => 'Force two-factor authentication for all admin accounts'],
                'session_timeout_minutes'          => ['label' => 'Session Timeout (minutes)', 'type' => 'number', 'desc' => 'Auto-logout after this many minutes of inactivity'],
                'allow_multiple_sessions'          => ['label' => 'Allow Multiple Sessions', 'type' => 'toggle', 'desc' => 'Let users be logged in from multiple devices simultaneously'],
                'ip_whitelist_enabled'             => ['label' => 'Enable IP Whitelist', 'type' => 'toggle', 'desc' => 'Only allow access from whitelisted IPs (very restrictive)'],
                'ip_blacklist_enabled'             => ['label' => 'Enable IP Blacklist', 'type' => 'toggle', 'desc' => 'Block access from blacklisted IPs'],
                'auto_block_failed_logins'         => ['label' => 'Auto-Block Failed Login IPs', 'type' => 'toggle', 'desc' => 'Automatically block IPs that exceed the threshold'],
                'auto_block_threshold'             => ['label' => 'Auto-Block Threshold', 'type' => 'number', 'desc' => 'Failed attempts before auto-blocking an IP'],
                'log_retention_days'               => ['label' => 'Log Retention (days)', 'type' => 'number', 'desc' => 'How long to keep security logs before auto-cleanup'],
                'enable_audit_trail'               => ['label' => 'Enable Audit Trail', 'type' => 'toggle', 'desc' => 'Log every admin action for compliance'],
                'notify_critical_actions'          => ['label' => 'Notify on Critical Actions', 'type' => 'toggle', 'desc' => 'Send alerts for critical security events'],
                'withdrawal_requires_2fa'           => ['label' => 'Require 2FA for Withdrawals', 'type' => 'toggle', 'desc' => 'Users must verify with 2FA before withdrawing'],
                'large_withdrawal_threshold'        => ['label' => 'Large Withdrawal Threshold ($)', 'type' => 'number', 'desc' => 'Withdrawals above this require extra admin approval'],
                'large_withdrawal_requires_approval' => ['label' => 'Large Withdrawals Need Approval', 'type' => 'toggle', 'desc' => 'Flag high-value withdrawals for manual review'],
            ];

            $groupIcons = [
                'auth'         => ['fa-key', '#6366f1'],
                'session'      => ['fa-clock', '#3b82f6'],
                'network'      => ['fa-network-wired', '#10b981'],
                'logging'      => ['fa-clipboard-list', '#a855f7'],
                'notifications' => ['fa-bell', '#f59e0b'],
                'transactions' => ['fa-money-bill-wave', '#ef4444'],
            ];
        @endphp

        @foreach($settings as $groupKey => $groupData)
        <div class="card-custom mb-3" style="padding: 0; overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ ($groupIcons[$groupKey][1] ?? '#6366f1') }}15; display: flex; align-items: center; justify-content: center;">
                    <i class="fas {{ $groupIcons[$groupKey][0] ?? 'fa-cog' }}" style="color: {{ $groupIcons[$groupKey][1] ?? '#6366f1' }};"></i>
                </div>
                <div>
                    <h6 style="margin: 0; color: var(--text-bright); font-weight: 700; font-size: 14px;">{{ $groupData['label'] }}</h6>
                </div>
            </div>
            <div style="padding: 16px 20px;">
                @foreach($groupData['items'] as $setting)
                @php $meta = $settingMeta[$setting->key] ?? ['label' => $setting->key, 'type' => 'text', 'desc' => '']; @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(51,65,85,0.2);">
                    <div style="flex: 1; padding-right: 20px;">
                        <label style="color: var(--text-bright); font-weight: 600; font-size: 13px; margin-bottom: 2px;">{{ $meta['label'] }}</label>
                        <p style="color: var(--text-dim); font-size: 12px; margin: 0;">{{ $meta['desc'] }}</p>
                    </div>
                    <div style="flex-shrink: 0;">
                        @if($meta['type'] === 'toggle')
                        <label class="toggle-switch" style="margin-bottom: 0;">
                            <input type="checkbox" name="settings[{{ $setting->key }}]" value="1" {{ $setting->value === '1' ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        @elseif($meta['type'] === 'number')
                        <input type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; width: 100px; font-size: 13px;">
                        @else
                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="form-control" style="background: var(--bg-input); border: 1px solid var(--border); color: var(--text); border-radius: 10px; width: 200px; font-size: 13px;">
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="text-end mt-4">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #6366f1, #7c3aed, #a855f7); color: white; border: none; border-radius: 12px; padding: 12px 40px; font-size: 14px; font-weight: 600;">
                <i class="fas fa-save"></i> Save Security Settings
            </button>
        </div>
    </form>
</div>
@endsection