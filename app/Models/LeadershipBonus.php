<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadershipBonus extends Model
{
    protected $table = 'leadership_bonuses';
    protected $fillable = [
        'user_id', 'rank_id', 'pool_name', 'pool_type',
        'total_pool_amount', 'eligible_rank_count',
        'user_share_percent', 'bonus_amount',
        'team_volume', 'direct_referrals', 'total_downline',
        'status', 'paid_at', 'cycle_id', 'note',
    ];

    protected $casts = [
        'total_pool_amount'    => 'decimal:2',
        'user_share_percent'   => 'decimal:2',
        'bonus_amount'         => 'decimal:2',
        'team_volume'          => 'decimal:2',
        'eligible_rank_count'  => 'integer',
        'direct_referrals'     => 'integer',
        'total_downline'       => 'integer',
        'paid_at'              => 'datetime',
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
