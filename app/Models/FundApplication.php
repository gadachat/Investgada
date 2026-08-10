<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FundApplication extends Model
{
    protected $fillable = [
        'reference', 'user_id', 'applicant_type', 'requested_amount',
        'approved_amount', 'purpose', 'admin_note', 'status',
        'approved_by', 'approved_at', 'funded_at',
        'team_production', 'target_production', 'production_percent',
        'target_met', 'target_met_at',
        'capital_withdrawn', 'profit_withdrawn',
    ];

    protected $casts = [
        'approved_at'     => 'datetime',
        'funded_at'        => 'datetime',
        'target_met_at'    => 'datetime',
        'target_met'       => 'boolean',
        'requested_amount' => 'decimal:2',
        'approved_amount'  => 'decimal:2',
        'team_production'  => 'decimal:2',
        'target_production'=> 'decimal:2',
        'production_percent'=> 'decimal:2',
        'capital_withdrawn'=> 'decimal:2',
        'profit_withdrawn' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Scope helpers ──
    public function scopeActive($query)
    {
        return $query->where('status', 'approved')->where('target_met', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'approved')->where('target_met', true);
    }

    // ── Business Logic ──

    /**
     * Recalculate team production percentage.
     */
    public function recalculateProduction(): void
    {
        $target = (float) $this->target_production;

        if ($target <= 0) {
            return;
        }

        $percent = round(($this->team_production / $target) * 100, 2);

        $this->update([
            'production_percent' => $percent,
            'target_met'          => $percent >= 100,
            'target_met_at'       => $percent >= 100 && !$this->target_met ? now() : $this->target_met_at,
        ]);

        if ($percent >= 100 && !$this->target_met) {
            // Mark fund as completed
            $this->update(['status' => 'completed']);
        }
    }

    /**
     * Add team production volume (called when a downline invests).
     */
    public function addProduction(float $amount): void
    {
        $this->increment('team_production', $amount);
        $this->refresh();
        $this->recalculateProduction();
    }

    /**
     * Can this user withdraw capital?
     */
    public function canWithdrawCapital(): bool
    {
        if (!$this->exists || $this->status === 'pending') {
            return true; // Not a fund recipient, normal rules
        }

        $setting = FundSetting::get('allow_capital_withdrawal', 'false');

        if ($setting === 'true') {
            return true; // Admin overrode to allow
        }

        return $this->target_met; // Can only withdraw if team hit 100%
    }

    /**
     * Can this user withdraw profits?
     */
    public function canWithdrawProfit(): bool
    {
        if (!$this->exists || $this->status === 'pending') {
            return true;
        }

        $setting = FundSetting::get('allow_profit_withdrawal', 'false');

        if ($setting === 'true') {
            return true;
        }

        return $this->target_met;
    }

    /**
     * Can this user withdraw commissions?
     */
    public function canWithdrawCommission(): bool
    {
        $setting = FundSetting::get('allow_commission_withdrawal', 'true');
        return $setting === 'true';
    }

    /**
     * Get progress bar percentage (capped at 100).
     */
    public function progressPercent(): float
    {
        return min(100, (float) $this->production_percent);
    }

    /**
     * Remaining production needed.
     */
    public function remainingProduction(): float
    {
        return max(0, (float) $this->target_production - (float) $this->team_production);
    }
}
