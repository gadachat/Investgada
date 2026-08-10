<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SecuritySetting;

class BlockedIp extends Model
{
    protected $fillable = [
        'ip_address', 'type', 'reason', 'blocked_by',
        'expires_at', 'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    // Check if an IP is blocked
    public static function isBlocked(string $ip): bool
    {
        return static::where('ip_address', $ip)
            ->where('type', 'blocked')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    // Check if IP whitelist is enabled and this IP is NOT whitelisted
    public static function isNotWhitelisted(string $ip): bool
    {
        $whitelistEnabled = SecuritySetting::get('ip_whitelist_enabled', '0') === '1';

        if (!$whitelistEnabled) return false;

        return !static::where('ip_address', $ip)
            ->where('type', 'whitelisted')
            ->where('is_active', true)
            ->exists();
    }
}
