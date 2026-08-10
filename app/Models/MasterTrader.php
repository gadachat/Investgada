<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterTrader extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description', 'avatar',
        'win_rate', 'manual_win_rate', 'use_manual_win_rate',
        'strategy_type', 'monthly_return', 'total_profit',
        'daily_profit_pct', 'loss_rate_pct', 'trades_per_day',
        'profit_variance', 'use_manual_outcome',
        'total_trades', 'winning_trades', 'followers_count',
        'max_followers', 'is_active',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'use_manual_win_rate'    => 'boolean',
        'use_manual_outcome'     => 'boolean',
        'win_rate'               => 'decimal:2',
        'manual_win_rate'        => 'decimal:2',
        'monthly_return'         => 'decimal:2',
        'total_profit'           => 'decimal:2',
        'daily_profit_pct'       => 'decimal:2',
        'loss_rate_pct'          => 'decimal:2',
        'trades_per_day'         => 'integer',
        'profit_variance'        => 'decimal:2',
        'total_trades'           => 'integer',
        'winning_trades'         => 'integer',
        'followers_count'        => 'integer',
        'max_followers'          => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CopyTradingSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Returns the effective win rate — manual override if set, else calculated.
     */
    public function getDisplayWinRateAttribute(): float
    {
        if ($this->use_manual_win_rate && $this->manual_win_rate !== null) {
            return (float) $this->manual_win_rate;
        }
        return (float) $this->win_rate;
    }

    public function getLossRateAttribute(): float
    {
        $rate = $this->display_win_rate;
        return (float) round(100 - $rate, 2);
    }

    public function getFollowerSlotsAttribute(): string
    {
        if ($this->max_followers > 0) {
            $remaining = $this->max_followers - $this->followers_count;
            return "{$remaining} of {$this->max_followers} available";
        }
        return 'Unlimited';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return '';
    }
}
