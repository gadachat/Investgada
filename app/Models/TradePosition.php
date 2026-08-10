<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradePosition extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'symbol', 'market_type', 'direction',
        'entry_price', 'volume', 'amount', 'leverage', 'contract_value',
        'take_profit', 'stop_loss',
        'current_price', 'pnl', 'pnl_percent', 'fees',
        'status', 'close_price', 'close_pnl', 'closed_at', 'close_reason',
    ];

    protected $casts = [
        'entry_price'      => 'decimal:8',
        'volume'            => 'decimal:4',
        'amount'            => 'decimal:2',
        'contract_value'    => 'decimal:2',
        'take_profit'       => 'decimal:8',
        'stop_loss'         => 'decimal:8',
        'current_price'    => 'decimal:8',
        'pnl'               => 'decimal:2',
        'pnl_percent'       => 'decimal:2',
        'fees'              => 'decimal:2',
        'close_price'       => 'decimal:8',
        'close_pnl'         => 'decimal:2',
        'closed_at'         => 'datetime',
        'leverage'          => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Scope helpers ──
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->whereNot('status', 'open');
    }

    // ── Business Logic ──

    /**
     * Calculate P&L for this position given a current market price.
     */
    public function calculatePnl(float $currentPrice): array
    {
        $entry = (float) $this->entry_price;
        $contractValue = (float) $this->contract_value;
        $leverage = (int) $this->leverage;
        $amount = (float) $this->amount;

        if ($entry <= 0) {
            return ['pnl' => 0, 'percent' => 0];
        }

        // Price difference
        if ($this->direction === 'buy') {
            // Long: profit when price goes up
            $priceDiff = $currentPrice - $entry;
        } else {
            // Short: profit when price goes down
            $priceDiff = $entry - $currentPrice;
        }

        // P&L = (priceDiff / entryPrice) * contractValue
        $pnl = ($priceDiff / $entry) * $contractValue;
        $percent = $amount > 0 ? round(($pnl / $amount) * 100, 2) : 0;

        return [
            'pnl' => round($pnl, 2),
            'percent' => $percent,
        ];
    }

    /**
     * Update the P&L for this position with the current market price.
     * Also checks TP/SL and liquidation.
     */
    public function updatePnl(float $currentPrice): void
    {
        $pnlData = $this->calculatePnl($currentPrice);

        $this->update([
            'current_price' => $currentPrice,
            'pnl'           => $pnlData['pnl'],
            'pnl_percent'   => $pnlData['percent'],
        ]);
    }

    /**
     * Check if this position should be closed (TP/SL/liquidation).
     * Returns the close reason or null.
     */
    public function checkCloseConditions(float $currentPrice): ?string
    {
        $amount = (float) $this->amount;
        $pnl = (float) $this->pnl;

        // Liquidation check — if loss exceeds margin (amount)
        if ($pnl <= -$amount) {
            return 'liquidation';
        }

        // Take Profit check
        if ($this->take_profit) {
            $tp = (float) $this->take_profit;
            if ($this->direction === 'buy' && $currentPrice >= $tp) {
                return 'tp';
            }
            if ($this->direction === 'sell' && $currentPrice <= $tp) {
                return 'tp';
            }
        }

        // Stop Loss check
        if ($this->stop_loss) {
            $sl = (float) $this->stop_loss;
            if ($this->direction === 'buy' && $currentPrice <= $sl) {
                return 'sl';
            }
            if ($this->direction === 'sell' && $currentPrice >= $sl) {
                return 'sl';
            }
        }

        return null;
    }

    /**
     * Close this position at the given price.
     */
    public function close(float $closePrice, string $reason = 'manual'): array
    {
        $pnlData = $this->calculatePnl($closePrice);
        $fees = (float) $this->fees;
        $netPnl = $pnlData['pnl'] - $fees;

        $this->update([
            'status'       => match($reason) {
                'tp'          => 'tp_hit',
                'sl'          => 'sl_hit',
                'liquidation' => 'liquidated',
                default        => 'closed',
            },
            'close_price'  => $closePrice,
            'close_pnl'    => $netPnl,
            'pnl'          => $pnlData['pnl'],
            'pnl_percent'  => $pnlData['percent'],
            'closed_at'    => now(),
            'close_reason' => $reason,
        ]);

        return [
            'net_pnl'   => $netPnl,
            'gross_pnl' => $pnlData['pnl'],
            'fees'      => $fees,
            'amount'    => (float) $this->amount,
            // Return margin + net P&L (or 0 if liquidated)
            'return_amount' => $reason === 'liquidation' ? 0 : ((float) $this->amount + $netPnl),
        ];
    }

    /**
     * Get formatted P&L string.
     */
    public function pnlFormatted(): string
    {
        $pnl = (float) $this->pnl;
        $sign = $pnl >= 0 ? '+' : '';
        return $sign . '$' . number_format(abs($pnl), 2) . ($pnl < 0 ? ' (loss)' : '');
    }
}
