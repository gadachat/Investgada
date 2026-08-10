<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentPayout extends Model
{
    protected $fillable = [
        'investment_id', 'user_id', 'amount', 'cycle_number', 'payout_at',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'payout_at' => 'datetime',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
