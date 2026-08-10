<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradingSignal extends Model
{
    protected $fillable = [
        'symbol',
        'direction',       // buy | sell
        'entry_price',
        'stop_loss',
        'take_profit',
        'take_profit_2',
        'category',         // crypto | forex | indices
        'timeframe',        // 15m | 1h | 4h | 1d
        'confidence',       // 0-100
        'analysis',
        'status',           // active | closed | expired
        'result',           // win | loss | breakeven | null
        'closed_at',
        'close_price',
        'created_by',
    ];

    protected $casts = [
        'entry_price'   => 'decimal:8',
        'stop_loss'     => 'decimal:8',
        'take_profit'   => 'decimal:8',
        'take_profit_2' => 'decimal:8',
        'close_price'   => 'decimal:8',
        'confidence'    => 'integer',
        'closed_at'     => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
