<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentPackage extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'category', 'type',
        'min_amount', 'max_amount', 'return_rate', 'return_type',
        'duration_days', 'cycle_days', 'total_return_cap',
        'principal_return', 'compounding', 'is_active', 'featured',
        'image', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'principal_return' => 'boolean',
        'compounding' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'return_rate' => 'decimal:2',
    ];

    public function investments()
    {
        return $this->hasMany(Investment::class, 'package_id');
    }

    // Calculate expected total return for a given amount
    public function expectedReturn(float $amount): float
    {
        if ($this->total_return_cap > 0) {
            return bcmul($amount, bcdiv($this->total_return_cap, 100, 8), 2);
        }
        $cycles = intdiv($this->duration_days, $this->cycle_days);
        $perCycle = bcmul($amount, bcdiv($this->return_rate, 100, 8), 8);
        return bcmul($perCycle, $cycles, 2);
    }
}
