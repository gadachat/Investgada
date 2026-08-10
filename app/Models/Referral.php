<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referred_id', 'referral_code',
        'status', 'commission_earned', 'activated_at',
    ];

    protected $casts = [
        'commission_earned' => 'decimal:2',
        'activated_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
