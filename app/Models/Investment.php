<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'package_id', 'amount',
        'expected_return', 'earned_so_far', 'status',
        'activated_at', 'matures_at', 'last_payout_at', 'next_payout_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'matures_at' => 'datetime',
        'last_payout_at' => 'datetime',
        'next_payout_at' => 'datetime',
        'amount' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'earned_so_far' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(InvestmentPackage::class, 'package_id');
    }

    public function payouts()
    {
        return $this->hasMany(InvestmentPayout::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isMatured(): bool
    {
        return $this->matures_at && now()->gte($this->matures_at);
    }
}
