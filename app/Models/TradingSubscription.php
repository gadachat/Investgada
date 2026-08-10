<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TradingSubscription extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'trading_package_id',
        'amount', 'selected_pairs', 'scanner_active',
        'total_profit', 'total_loss', 'total_trades', 'total_wins', 'total_losses',
        'status', 'expires_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'total_profit'   => 'decimal:2',
        'total_loss'     => 'decimal:2',
        'selected_pairs' => 'array',
        'scanner_active' => 'boolean',
        'expires_at'     => 'datetime',
        'total_trades'   => 'integer',
        'total_wins'     => 'integer',
        'total_losses'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(TradingPackage::class, 'trading_package_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the subscription allows trading a given pair.
     */
    public function canTradePair(string $symbol): bool
    {
        if ($this->status !== 'active') return false;

        $pairs = $this->selected_pairs ?? [];
        return in_array($symbol, $pairs);
    }

    /**
     * Record a trade result.
     */
    public function recordTrade(float $profit, bool $isWin): void
    {
        $this->increment('total_trades');

        if ($isWin) {
            $this->increment('total_wins');
            $this->increment('total_profit', $profit);
        } else {
            $this->increment('total_losses');
            $this->increment('total_loss', abs($profit));
        }
    }

    /**
     * Net P&L for this subscription.
     */
    public function netPnl(): float
    {
        return (float) $this->total_profit - (float) $this->total_loss;
    }

    /**
     * Win rate as a percentage.
     */
    public function winRate(): float
    {
        if ($this->total_trades === 0) return 0;
        return round(($this->total_wins / $this->total_trades) * 100, 1);
    }
}
