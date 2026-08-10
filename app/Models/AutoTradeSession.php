<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class AutoTradeSession extends Model
{
    protected $table = 'auto_trade_sessions';

    protected $fillable = [
        'reference', 'user_id', 'allocated_capital', 'current_balance',
        'total_profit', 'total_loss', 'total_trades', 'winning_trades', 'losing_trades',
        'selected_pairs', 'status', 'started_at', 'stopped_at',
        'last_trade_at', 'next_trade_at',
    ];

    protected $casts = [
        'allocated_capital' => 'decimal:2',
        'current_balance'   => 'decimal:2',
        'total_profit'      => 'decimal:2',
        'total_loss'        => 'decimal:2',
        'selected_pairs'    => 'array',
        'started_at'        => 'datetime',
        'stopped_at'         => 'datetime',
        'last_trade_at'     => 'datetime',
        'next_trade_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trades(): HasMany
    {
        return $this->hasMany(AutoTrade::class, 'session_id');
    }

    public function winRate(): float
    {
        if ($this->total_trades == 0) return 0;
        return round(($this->winning_trades / $this->total_trades) * 100, 1);
    }

    public function netProfit(): float
    {
        return (float) $this->total_profit - (float) $this->total_loss;
    }
}
