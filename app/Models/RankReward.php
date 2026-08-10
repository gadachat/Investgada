<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankReward extends Model
{
    protected $fillable = [
        'user_id', 'rank_id', 'reward_amount', 'type', 'description',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }
}
