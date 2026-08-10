<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CopyTradingSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'master_trader_id', 'allocation_amount',
        'allocation_percent', 'is_active', 'started_at',
        'stopped_at', 'last_payout_at', 'last_payout_amount',
        'total_copied', 'total_pnl', 'wins_count', 'losses_count',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'allocation_amount'   => 'decimal:2',
        'allocation_percent'  => 'decimal:2',
        'started_at'          => 'datetime',
        'stopped_at'          => 'datetime',
        'last_payout_at'      => 'datetime',
        'last_payout_amount'  => 'decimal:2',
        'total_copied'        => 'integer',
        'total_pnl'           => 'decimal:2',
        'wins_count'          => 'integer',
        'losses_count'        => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function masterTrader()
    {
        return $this->belongsTo(MasterTrader::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Stopped';
    }

    public function getWinRateAttribute(): float
    {
        if ($this->total_copied > 0) {
            return round(($this->wins_count / $this->total_copied) * 100, 2);
        }
        return 0;
    }
}
