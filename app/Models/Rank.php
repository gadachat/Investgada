<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $fillable = [
        'name', 'slug', 'badge_color', 'icon',
        'min_investment', 'min_direct_referrals',
        'min_team_volume', 'min_left_volume', 'min_right_volume',
        'matching_bonus_percent', 'direct_referral_percent',
        'profit_share_percent', 'salary_bonus',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rewards()
    {
        return $this->hasMany(RankReward::class);
    }
}
