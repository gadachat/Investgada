<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinaryTree extends Model
{
    protected $table = 'binary_tree';

    protected $fillable = [
        'user_id', 'parent_id', 'position',
        'left_child_id', 'right_child_id',
        'left_volume', 'right_volume',
        'left_carry_forward', 'right_carry_forward',
        'total_matching_bonus', 'last_matched_at',
        'left_count', 'right_count', 'level',
    ];

    protected $casts = [
        'left_volume' => 'decimal:2',
        'right_volume' => 'decimal:2',
        'left_carry_forward' => 'decimal:2',
        'right_carry_forward' => 'decimal:2',
        'total_matching_bonus' => 'decimal:2',
        'last_matched_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function leftChild()
    {
        return $this->belongsTo(User::class, 'left_child_id');
    }

    public function rightChild()
    {
        return $this->belongsTo(User::class, 'right_child_id');
    }

    public function matchingBonuses()
    {
        return $this->hasMany(MatchingBonus::class, 'user_id', 'user_id');
    }
}
