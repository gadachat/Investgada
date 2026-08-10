<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = [
        'email', 'user_id', 'ip_address', 'user_agent',
        'successful', 'failure_reason',
    ];

    protected $casts = [
        'successful' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Count failed attempts for an IP in the last N minutes
    public static function countFailedByIp(string $ip, int $minutes = 30): int
    {
        return static::where('ip_address', $ip)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    // Count failed attempts for an email in the last N minutes
    public static function countFailedByEmail(string $email, int $minutes = 30): int
    {
        return static::where('email', $email)
            ->where('successful', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    // Check if an IP is currently locked out
    public static function isIpLocked(string $ip, int $maxAttempts, int $lockMinutes): bool
    {
        return static::countFailedByIp($ip, $lockMinutes) >= $maxAttempts;
    }
}
