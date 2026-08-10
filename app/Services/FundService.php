<?php

namespace App\Services;

use App\Models\FundApplication;
use App\Models\FundSetting;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FundService
{
    /**
     * Called when a downline invests — adds production to the upline's active fund.
     * Walks up the sponsor chain looking for fund recipients.
     */
    public static function onDownlineInvestment(int $userId, float $amount): void
    {
        if (!FundSetting::isEnabled() || FundSetting::get('auto_calculate_production', 'true') !== 'true') {
            return;
        }

        $user = User::find($userId);
        if (!$user || !$user->sponsor_id) {
            return;
        }

        // Walk up the sponsor chain
        $current = $user;
        $depth = 0;
        $maxDepth = 50;

        while ($current && $current->sponsor_id && $depth < $maxDepth) {
            $sponsor = User::find($current->sponsor_id);
            if (!$sponsor) break;

            // If this sponsor has an active fund, add production
            if ($sponsor->is_fund_recipient && $sponsor->active_fund_id) {
                $fund = FundApplication::find($sponsor->active_fund_id);
                if ($fund && $fund->status === 'approved' && !$fund->target_met) {
                    $fund->addProduction($amount);
                    Log::info("Fund production: User {$sponsor->id} fund #{$fund->id} +\${$amount} (now {$fund->production_percent}%)");

                    if ($fund->fresh()->target_met) {
                        // Notify the fund recipient
                        \App\Models\Notification::create([
                            'user_id' => $sponsor->id,
                            'type'    => 'fund',
                            'title'   => 'Team Target Reached! 🎉',
                            'message' => "Your team has produced 100% of your fund capital. You can now withdraw profits and capital.",
                            'data'    => json_encode(['fund_id' => $fund->id]),
                        ]);
                    }
                }
            }

            $current = $sponsor;
            $depth++;
        }
    }

    /**
     * Check if a user can withdraw a specific wallet type.
     * Returns [allowed: bool, reason: string]
     */
    public static function checkWithdrawal(int $userId, string $walletType): array
    {
        $user = User::find($userId);

        if (!$user || !$user->is_fund_recipient || !$user->active_fund_id) {
            return ['allowed' => true, 'reason' => null];
        }

        $fund = FundApplication::find($user->active_fund_id);
        if (!$fund || $fund->status !== 'approved') {
            return ['allowed' => true, 'reason' => null];
        }

        // Map wallet types to fund withdrawal categories
        // 'commission' and 'bonus' wallets → commission category
        // 'interest' and 'earning' wallets → profit category
        // 'deposit' wallet → capital category
        $category = match ($walletType) {
            'commission', 'bonus'       => 'commission',
            'interest', 'earning'       => 'profit',
            'deposit'                    => 'capital',
            'withdrawal'                  => 'withdrawal', // already processed funds
            default                       => 'commission',
        };

        // Withdrawal wallet is always allowed (it's already been moved from other wallets)
        if ($category === 'withdrawal') {
            return ['allowed' => true, 'reason' => null];
        }

        // Commission withdrawals — allowed unless admin disabled
        if ($category === 'commission') {
            if (FundSetting::allowsWithdrawalType('commission')) {
                return ['allowed' => true, 'reason' => null];
            }
            return ['allowed' => false, 'reason' => 'Commission withdrawals are currently disabled by admin.'];
        }

        // Profit withdrawals — blocked until target met (unless admin overrides)
        if ($category === 'profit') {
            if (FundSetting::allowsWithdrawalType('profit')) {
                return ['allowed' => true, 'reason' => null];
            }
            if ($fund->target_met) {
                return ['allowed' => true, 'reason' => null];
            }
            $remaining = $fund->remainingProduction();
            $progress = $fund->progressPercent();
            return [
                'allowed' => false,
                'reason'  => "Profit withdrawal locked. Your team must produce 100% of fund capital to unlock. Current: {$progress}% (remaining: $" . number_format($remaining, 2) . ")",
            ];
        }

        // Capital withdrawals — blocked until target met (unless admin overrides)
        if ($category === 'capital') {
            if (FundSetting::allowsWithdrawalType('capital')) {
                return ['allowed' => true, 'reason' => null];
            }
            if ($fund->target_met) {
                return ['allowed' => true, 'reason' => null];
            }
            $remaining = $fund->remainingProduction();
            $progress = $fund->progressPercent();
            return [
                'allowed' => false,
                'reason'  => "Capital withdrawal locked. Your team must produce 100% of fund capital to unlock. Current: {$progress}% (remaining: $" . number_format($remaining, 2) . ")",
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Calculate how much of the user's withdrawal wallet balance came from commissions.
     * Uses transaction metadata to trace the source of funds.
     */
    public static function getCommissionSourcedBalance(int $userId): float
    {
        $wallet = \App\Models\Wallet::where('user_id', $userId)
            ->where('type', 'withdrawal')
            ->first();

        if (!$wallet) {
            return 0;
        }

        // Sum all credits to the withdrawal wallet that came from commission or bonus wallets
        $commissionCredits = \App\Models\Transaction::where('user_id', $userId)
            ->where('wallet_id', $wallet->id)
            ->where('direction', 'credit')
            ->where(function ($q) {
                $q->where('type', 'transfer_in')
                  ->whereRaw("JSON_EXTRACT(metadata, '$.transfer_from') IN ('commission', 'bonus')")
                  ->orWhere('type', 'commission')
                  ->orWhere('type', 'bonus')
                  ->orWhere('type', 'matching_bonus')
                  ->orWhere('type', 'leadership_bonus')
                  ->orWhere('type', 'referral_commission')
                  ->orWhere('type', 'profit_share');
            })
            ->sum('amount');

        // Sum all debits (withdrawals out) to know what's been consumed
        $totalDebits = \App\Models\Transaction::where('user_id', $userId)
            ->where('wallet_id', $wallet->id)
            ->where('direction', 'debit')
            ->sum('amount');

        // Available commission-sourced = credits - debits (can't go below 0)
        return max(0, (float) $commissionCredits - (float) $totalDebits);
    }

    /**
     * Get a summary of the fund recipient's withdrawal eligibility.
     */
    public static function getWithdrawalSummary(int $userId): array
    {
        $user = User::find($userId);

        if (!$user || !$user->is_fund_recipient || !$user->active_fund_id) {
            return [
                'is_fund_recipient' => false,
                'can_withdraw'      => true,
                'reason'            => null,
                'commission_available' => 0,
                'target_met'        => false,
                'progress'          => 0,
            ];
        }

        $fund = FundApplication::find($user->active_fund_id);
        if (!$fund) {
            return [
                'is_fund_recipient' => false,
                'can_withdraw'      => true,
                'reason'            => null,
                'commission_available' => 0,
                'target_met'        => false,
                'progress'          => 0,
            ];
        }

        $commissionAvailable = self::getCommissionSourcedBalance($userId);
        $targetMet = $fund->target_met;
        $progress = $fund->progressPercent();

        // If target met, everything is unlocked
        if ($targetMet) {
            return [
                'is_fund_recipient'   => true,
                'can_withdraw'        => true,
                'reason'              => null,
                'commission_available'=> $commissionAvailable,
                'target_met'          => true,
                'progress'            => $progress,
                'fund_reference'      => $fund->reference,
            ];
        }

        // Target not met — check if admin overrode
        if (FundSetting::allowsWithdrawalType('profit') && FundSetting::allowsWithdrawalType('capital')) {
            return [
                'is_fund_recipient'   => true,
                'can_withdraw'        => true,
                'reason'              => null,
                'commission_available'=> $commissionAvailable,
                'target_met'          => false,
                'progress'            => $progress,
                'fund_reference'      => $fund->reference,
            ];
        }

        // Target not met, admin hasn't overridden — can only withdraw commission-sourced funds
        return [
            'is_fund_recipient'   => true,
            'can_withdraw'        => $commissionAvailable > 0,
            'reason'              => $commissionAvailable > 0
                ? null
                : "Your account is a special fund account. You can only withdraw commissions until your team reaches 100% of the fund target. Current progress: {$progress}%.",
            'commission_available'=> $commissionAvailable,
            'target_met'          => false,
            'progress'            => $progress,
            'fund_reference'      => $fund->reference,
        ];
    }

}
