<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecurityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'module', 'description',
        'ip_address', 'user_agent', 'severity', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Severity colors for the UI
    public static function severityColors(): array
    {
        return [
            'info'     => '#3b82f6',
            'warning'  => '#f59e0b',
            'critical' => '#ef4444',
            'danger'   => '#dc2626',
        ];
    }

    public static function moduleColors(): array
    {
        return [
            'auth'       => '#6366f1',
            'deposits'    => '#10b981',
            'withdrawals' => '#ef4444',
            'kyc'        => '#3b82f6',
            'settings'   => '#a855f7',
            'users'      => '#f59e0b',
            'packages'   => '#06b6d4',
            'system'     => '#64748b',
        ];
    }

    // Helper: log a security event
    public static function log(string $action, string $module = null, string $description = null, string $severity = 'info', array $metadata = []): void
    {
        $request = request();

        static::create([
            'user_id'     => $request->user()?->id,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'severity'    => $severity,
            'metadata'    => $metadata,
        ]);
    }
}
