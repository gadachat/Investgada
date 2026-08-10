<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingBonus extends Model
{
    protected $table = 'matching_bonuses';
    protected $fillable = [
        'user_id', 'left_volume', 'right_volume',
        'matched_volume', 'bonus_percent', 'bonus_amount',
        'carry_forward_left', 'carry_forward_right', 'status',
    ];

    protected $casts = [
        'left_volume'           => 'decimal:2',
        'right_volume'          => 'decimal:2',
        'matched_volume'        => 'decimal:2',
        'bonus_percent'         => 'decimal:2',
        'bonus_amount'          => 'decimal:2',
        'carry_forward_left'    => 'decimal:2',
        'carry_forward_right'   => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
