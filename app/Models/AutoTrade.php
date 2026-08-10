<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoTrade extends Model
{
    protected $table = 'auto_trades';

    protected $fillable = [
        'reference', 'user_id', 'session_id', 'pair', 'pair_name', 'category',
        'direction', 'entry_price', 'exit_price', 'amount', 'profit', 'profit_pct',
        'status', 'is_win', 'opened_at', 'closed_at', 'duration_seconds',
    ];

    protected $casts = [
        'entry_price'  => 'decimal:8',
        'exit_price'   => 'decimal:8',
        'amount'       => 'decimal:2',
        'profit'       => 'decimal:2',
        'profit_pct'   => 'decimal:4',
        'is_win'        => 'boolean',
        'opened_at'    => 'datetime',
        'closed_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AutoTradeSession::class, 'session_id');
    }
}
