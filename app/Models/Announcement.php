<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'target',
        'target_user_id',
        'is_active',
        'is_dismissible',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_dismissible' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $now);
            });
    }

    public function scopeForUser($query, $user)
    {
        if (!$user) {
            return $query->where('target', 'all');
        }

        $allowedTargets = ['all'];

        if ($user->kyc_status === 'verified') {
            $allowedTargets[] = 'verified';
        }

        $isInvestor = ($user->total_invested ?? 0) > 0 || (method_exists($user, 'investments') && $user->investments()->exists());
        if ($isInvestor) {
            $allowedTargets[] = 'investors';
        }

        $isTrader = $user->role === 'trader'
            || (Schema::hasTable('trade_positions') && DB::table('trade_positions')->where('user_id', $user->id)->exists())
            || (Schema::hasTable('trading_subscriptions') && DB::table('trading_subscriptions')->where('user_id', $user->id)->exists())
            || (Schema::hasTable('auto_trade_sessions') && DB::table('auto_trade_sessions')->where('user_id', $user->id)->exists());

        if ($isTrader) {
            $allowedTargets[] = 'traders';
        }

        return $query->where(function ($q) use ($allowedTargets, $user) {
            $q->whereIn('target', $allowedTargets)
              ->orWhere(function ($sq) use ($user) {
                  $sq->where('target', 'specific')
                     ->where('target_user_id', $user->id);
              });
        });
    }
}
