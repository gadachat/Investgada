<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfitShare extends Model
{
    protected $fillable = [
        'name', 'pool_type', 'total_pool_amount', 'distributed_amount',
        'eligible_users', 'distribution_at', 'status',
    ];

    protected $casts = [
        'total_pool_amount' => 'decimal:2',
        'distributed_amount' => 'decimal:2',
        'distribution_at' => 'datetime',
    ];
}
