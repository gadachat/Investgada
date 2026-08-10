<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getByGroup(string $group)
    {
        return static::where('group', $group)->get()->pluck('value', 'key');
    }

    public static function is2FARequiredForAdmin(): bool
    {
        return static::get('require_2fa_admin', '0') === '1';
    }

    public static function isAuditTrailEnabled(): bool
    {
        return static::get('enable_audit_trail', '1') === '1';
    }

    public static function isIpBlacklistEnabled(): bool
    {
        return static::get('ip_blacklist_enabled', '1') === '1';
    }

    public static function isAutoBlockEnabled(): bool
    {
        return static::get('auto_block_failed_logins', '1') === '1';
    }

    public static function getMaxLoginAttempts(): int
    {
        return (int) static::get('max_login_attempts', '5');
    }

    public static function getLockoutMinutes(): int
    {
        return (int) static::get('lockout_duration_minutes', '30');
    }

    public static function getAutoBlockThreshold(): int
    {
        return (int) static::get('auto_block_threshold', '20');
    }

    public static function getSessionTimeoutMinutes(): int
    {
        return (int) static::get('session_timeout_minutes', '120');
    }

    public static function getLargeWithdrawalThreshold(): float
    {
        return (float) static::get('large_withdrawal_threshold', '10000');
    }

    public static function withdrawalRequires2FA(): bool
    {
        return static::get('withdrawal_requires_2fa', '0') === '1';
    }

    public static function largeWithdrawalRequiresApproval(): bool
    {
        return static::get('large_withdrawal_requires_approval', '1') === '1';
    }
}
