<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveSession extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'ip_address', 'user_agent',
        'device_type', 'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Parse device type from user agent
    public static function detectDevice(?string $userAgent): string
    {
        if (!$userAgent) return 'Unknown';

        if (preg_match('/Mobile|Android|iPhone/i', $userAgent)) return 'Mobile';
        if (preg_match('/Tablet|iPad/i', $userAgent)) return 'Tablet';
        if (preg_match('/Windows/i', $userAgent)) return 'Desktop (Windows)';
        if (preg_match('/Macintosh|Mac OS/i', $userAgent)) return 'Desktop (Mac)';
        if (preg_match('/Linux/i', $userAgent)) return 'Desktop (Linux)';

        return 'Desktop';
    }

    // Parse browser from user agent
    public static function detectBrowser(?string $userAgent): string
    {
        if (!$userAgent) return 'Unknown';

        if (preg_match('/Edg/i', $userAgent)) return 'Edge';
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Opera|OPR/i', $userAgent)) return 'Opera';

        return 'Unknown';
    }
}
