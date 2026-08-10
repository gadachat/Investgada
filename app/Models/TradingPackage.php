<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TradingPackage extends Model
{
    protected $fillable = [
        'name', 'slug', 'description',
        'min_amount', 'max_amount',
        'max_pairs', 'scanner_enabled', 'has_short_selling',
        'daily_profit_percent', 'win_rate_percent', 'loss_rate_percent',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'min_amount'             => 'decimal:2',
        'max_amount'             => 'decimal:2',
        'daily_profit_percent'   => 'decimal:4',
        'win_rate_percent'       => 'decimal:4',
        'loss_rate_percent'      => 'decimal:4',
        'scanner_enabled'        => 'boolean',
        'has_short_selling'      => 'boolean',
        'is_active'              => 'boolean',
        'sort_order'             => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(TradingSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Check if an amount falls within this package's range.
     */
    public function amountInRange(float $amount): bool
    {
        return $amount >= (float) $this->min_amount && $amount <= (float) $this->max_amount;
    }

    /**
     * Find the appropriate package for a given amount.
     */
    public static function findForAmount(float $amount): ?self
    {
        return static::active()
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->first();
    }
}
