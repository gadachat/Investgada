<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Referral;
use App\Models\MatchingBonus;
use App\Models\Rank;
use App\Models\RankReward;
use App\Models\LeadershipBonus;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\DB;
use App\Services\FundService;
use Illuminate\Support\Facades\Log;

class CommissionEngine
{
    /**
     * Process all commissions when a user makes a new investment.
     * This is the main entry point — call after an investment is activated.
     */
    public function onInvestmentActivated(int $userId, float $amount, int $investmentId): void
    {
        DB::transaction(function () use ($userId, $amount, $investmentId) {
            // 1. Direct referral commission
            $this->payDirectReferralCommission($userId, $amount);

            // 2. Binary volume update + matching bonus
            $this->updateBinaryVolume($userId, $amount);
            $this->processMatchingBonus($userId, $amount);

            // 3. Rank qualification check (upline chain)
            $this->checkRankQualifications($userId);

            // 4. Update user total_invested
            User::where('id', $userId)->increment('total_invested', $amount);

            // 5. Track team production for upline fund recipients
            FundService::onDownlineInvestment($userId, $amount);
        });
    }

    /**
     * Pay direct referral commission to the sponsor.
     */
    public function payDirectReferralCommission(int $userId, float $investmentAmount): float
    {
        $user = User::find($userId);
        if (!$user || !$user->sponsor_id) {
            return 0;
        }

        $sponsor = User::find($user->sponsor_id);
        if (!$sponsor) {
            return 0;
        }

        // Get commission rate from sponsor's rank or global setting
        $commissionRate = $this->getDirectReferralRate($sponsor);

        $commission = $investmentAmount * ($commissionRate / 100);

        if ($commission <= 0) {
            return 0;
        }

        // Credit sponsor's commission wallet
        $this->creditWallet($sponsor->id, 'commission', $commission, [
            'type'           => 'referral_commission',
            'description'    => "Direct referral commission from {$user->name}'s investment of \${$investmentAmount}",
            'reference'      => 'RC-' . now()->format('YmdHis') . '-' . $userId,
            'metadata'       => ['referred_user_id' => $userId, 'investment_amount' => $investmentAmount, 'rate' => $commissionRate],
        ]);

        // Update sponsor's referral earnings
        User::where('id', $sponsor->id)->increment('total_referral_earnings', $commission);

        // Update referral record
        Referral::where('referrer_id', $sponsor->id)
            ->where('referred_id', $userId)
            ->increment('commission_earned', $commission);

        Log::info("Direct referral commission: User {$sponsor->id} earned \${$commission} from user {$userId}'s investment");

        return $commission;
    }

    /**
     * Update binary tree volumes up the chain.
     */
    public function updateBinaryVolume(int $userId, float $amount): void
    {
        $user = User::find($userId);
        if (!$user || !$user->parent_id) {
            return;
        }

        $currentParent = User::find($user->parent_id);
        $position = $user->binary_position ?? 'left';
        $depth = 0;
        $maxDepth = 50;

        while ($currentParent && $depth < $maxDepth) {
            // Update the parent's volume for the appropriate leg
            $volumeField = $position === 'left' ? 'left_volume' : 'right_volume';
            $countField  = $position === 'left' ? 'left_count'  : 'right_count';

            // Update binary_tree record
            DB::table('binary_tree')
                ->where('user_id', $currentParent->id)
                ->increment($volumeField, $amount);

            DB::table('binary_tree')
                ->where('user_id', $currentParent->id)
                ->increment($countField, 1);

            // Move up the tree
            $position = $currentParent->binary_position ?? 'left';
            $currentParent = $currentParent->parent_id ? User::find($currentParent->parent_id) : null;
            $depth++;
        }
    }

    /**
     * Process matching bonus for upline.
     * Matching bonus = weak leg volume × matching_rate (capped).
     */
    public function processMatchingBonus(int $userId, float $investmentAmount): float
    {
        $matchingRate = (float) PlatformSetting::get('matching_bonus_rate', '10');
        $matchingCap  = (float) PlatformSetting::get('matching_bonus_cap', '5000');

        // Walk up the upline and check each user's legs
        $user = User::find($userId);
        if (!$user || !$user->parent_id) {
            return 0;
        }

        $currentParent = User::find($user->parent_id);
        $position = $user->binary_position ?? 'left';
        $totalPaid = 0;
        $depth = 0;
        $maxDepth = 50;

        while ($currentParent && $depth < $maxDepth) {
            $binaryNode = DB::table('binary_tree')->where('user_id', $currentParent->id)->first();

            if ($binaryNode) {
                $leftVolume  = (float) $binaryNode->left_volume + (float) $binaryNode->left_carry_forward;
                $rightVolume = (float) $binaryNode->right_volume + (float) $binaryNode->right_carry_forward;

                $weakVolume = min($leftVolume, $rightVolume);

                if ($weakVolume > 0) {
                    // Get rank-based matching rate
                    $rankRate = $this->getMatchingRate($currentParent);
                    $effectiveRate = min($rankRate, $matchingRate);

                    $bonusAmount = $weakVolume * ($effectiveRate / 100);
                    $bonusAmount = min($bonusAmount, $matchingCap);

                    if ($bonusAmount > 0) {
                        // Create matching bonus record
                        MatchingBonus::create([
                            'user_id'              => $currentParent->id,
                            'left_volume'          => $leftVolume,
                            'right_volume'         => $rightVolume,
                            'matched_volume'       => $weakVolume,
                            'bonus_percent'        => $effectiveRate,
                            'bonus_amount'         => $bonusAmount,
                            'carry_forward_left'   => max(0, $leftVolume - $weakVolume),
                            'carry_forward_right'  => max(0, $rightVolume - $weakVolume),
                            'status'               => 'paid',
                        ]);

                        // Credit wallet
                        $this->creditWallet($currentParent->id, 'commission', $bonusAmount, [
                            'type'        => 'matching_bonus',
                            'description' => "Matching bonus: weak leg \${$weakVolume} × {$effectiveRate}%",
                            'reference'   => 'MB-' . now()->format('YmdHis') . '-' . $currentParent->id,
                        ]);

                        // Update binary tree totals
                        DB::table('binary_tree')
                            ->where('user_id', $currentParent->id)
                            ->increment('total_matching_bonus', $bonusAmount);

                        // Only set carry_forward, don't duplicate into left_volume/right_volume
                        // The volume columns accumulate new volume; carry_forward holds the unmatched remainder
                        DB::table('binary_tree')
                            ->where('user_id', $currentParent->id)
                            ->update([
                                'left_volume'         => 0,
                                'right_volume'        => 0,
                                'left_carry_forward'  => max(0, $leftVolume - $weakVolume),
                                'right_carry_forward' => max(0, $rightVolume - $weakVolume),
                                'last_matched_at'     => now(),
                            ]);

                        $totalPaid += $bonusAmount;
                    }
                }
            }

            // Move up
            $position = $currentParent->binary_position ?? 'left';
            $currentParent = $currentParent->parent_id ? User::find($currentParent->parent_id) : null;
            $depth++;
        }

        if ($totalPaid > 0) {
            Log::info("Matching bonus processed for user {$userId} upline: total \${$totalPaid}");
        }

        return $totalPaid;
    }

    /**
     * Check and promote user ranks up the line.
     */
    public function checkRankQualifications(int $userId): void
    {
        $user = User::find($userId);
        if (!$user) return;

        // Walk up the entire upline and check each user
        $current = $user;
        $depth = 0;
        $maxDepth = 50;

        while ($current && $depth < $maxDepth) {
            $this->evaluateRankForUser($current);
            $current = $current->parent_id ? User::find($current->parent_id) : null;
            $depth++;
        }
    }

    /**
     * Evaluate if a user qualifies for a higher rank.
     */
    public function evaluateRankForUser(User $user): ?Rank
    {
        $currentRankId = $user->rank_id;
        
        // Get stats
        $totalInvested = (float) $user->total_invested;
        $directReferrals = User::where('sponsor_id', $user->id)->where('status', 'active')->count();
        $teamVolume = $this->getTeamVolume($user->id);
        
        $binaryNode = DB::table('binary_tree')->where('user_id', $user->id)->first();
        $leftVolume = $binaryNode ? (float) $binaryNode->left_volume : 0;
        $rightVolume = $binaryNode ? (float) $binaryNode->right_volume : 0;

        // Find the highest rank the user qualifies for
        $qualifiedRank = Rank::where('is_active', true)
            ->where('min_investment', '<=', $totalInvested)
            ->where('min_direct_referrals', '<=', $directReferrals)
            ->where('min_team_volume', '<=', $teamVolume)
            ->where('min_left_volume', '<=', $leftVolume)
            ->where('min_right_volume', '<=', $rightVolume)
            ->orderByDesc('sort_order')
            ->first();

        if (!$qualifiedRank) {
            return null;
        }

        // Check if this is actually a promotion
        $currentRank = $currentRankId ? Rank::find($currentRankId) : null;
        $currentSort = $currentRank ? $currentRank->sort_order : 0;

        if ($qualifiedRank->sort_order <= $currentSort) {
            return $currentRank; // Already at or above this rank
        }

        // Promote!
        $user->update(['rank_id' => $qualifiedRank->id]);

        // Create rank reward record
        RankReward::create([
            'user_id'       => $user->id,
            'rank_id'       => $qualifiedRank->id,
            'reward_amount' => $qualifiedRank->salary_bonus,
            'type'          => 'bonus',
            'description'   => "Promoted to {$qualifiedRank->name}",
        ]);

        // Pay salary bonus if applicable
        if ($qualifiedRank->salary_bonus > 0) {
            $this->creditWallet($user->id, 'commission', (float) $qualifiedRank->salary_bonus, [
                'type'        => 'rank_promotion_bonus',
                'description' => "Salary bonus for achieving {$qualifiedRank->name} rank",
                'reference'   => 'RR-' . now()->format('YmdHis') . '-' . $user->id,
            ]);
        }

        // Create notification
        DB::table('notifications')->insert([
            'user_id'    => $user->id,
            'type'       => 'rank_promotion',
            'title'      => 'Rank Promoted!',
            'message'    => "Congratulations! You've been promoted to {$qualifiedRank->name}.",
            'link'       => '/dashboard/referral',
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("User {$user->id} promoted to rank {$qualifiedRank->name}");

        return $qualifiedRank;
    }

    /**
     * Run the monthly leadership bonus pool distribution.
     * Distributes a pool amount equally among users at qualifying ranks.
     */
    public function distributeLeadershipBonus(float $poolAmount, string $minRankSlug = 'silver', ?string $note = null): array
    {
        // Find the minimum qualifying rank
        $minRank = Rank::where('slug', $minRankSlug)->first();
        if (!$minRank) {
            return ['success' => false, 'message' => "Rank '{$minRankSlug}' not found"];
        }

        // Get all users at or above the qualifying rank
        $qualifiedUsers = User::whereNotNull('rank_id')
            ->whereHas('rank', function ($q) use ($minRank) {
                $q->where('sort_order', '>=', $minRank->sort_order)
                  ->where('is_active', true);
            })
            ->where('status', 'active')
            ->get();

        if ($qualifiedUsers->isEmpty()) {
            return ['success' => false, 'message' => 'No users qualify for leadership bonus'];
        }

        // Calculate weighted distribution based on rank sort_order (higher ranks get bigger share)
        $totalWeight = 0;
        foreach ($qualifiedUsers as $u) {
            $rank = Rank::find($u->rank_id);
            $totalWeight += $rank ? $rank->sort_order : 1;
        }

        $cycleId = 'LB-' . now()->format('Ym');
        $distributed = 0;
        $recipientCount = 0;

        DB::transaction(function () use ($qualifiedUsers, $poolAmount, $totalWeight, $cycleId, $note, &$distributed, &$recipientCount) {
            foreach ($qualifiedUsers as $user) {
                $rank = Rank::find($user->rank_id);
                $weight = $rank ? $rank->sort_order : 1;
                $sharePercent = ($weight / $totalWeight) * 100;
                $bonusAmount = ($weight / $totalWeight) * $poolAmount;

                if ($bonusAmount < 0.01) continue;

                // Get qualification snapshot
                $teamVolume = $this->getTeamVolume($user->id);
                $directReferrals = User::where('sponsor_id', $user->id)->where('status', 'active')->count();
                $totalDownline = $this->getDownlineCount($user->id);

                // Create leadership bonus record
                LeadershipBonus::create([
                    'user_id'             => $user->id,
                    'rank_id'             => $user->rank_id,
                    'pool_name'           => $cycleId . ' Leadership Pool',
                    'pool_type'           => 'monthly',
                    'total_pool_amount'   => $poolAmount,
                    'eligible_rank_count' => $qualifiedUsers->count(),
                    'user_share_percent'  => $sharePercent,
                    'bonus_amount'        => $bonusAmount,
                    'team_volume'         => $teamVolume,
                    'direct_referrals'    => $directReferrals,
                    'total_downline'      => $totalDownline,
                    'status'              => 'paid',
                    'paid_at'             => now(),
                    'cycle_id'            => $cycleId,
                    'note'                => $note,
                ]);

                // Credit wallet
                $this->creditWallet($user->id, 'commission', $bonusAmount, [
                    'type'        => 'leadership_bonus',
                    'description' => "Leadership bonus from {$cycleId} pool ({$rank->name} rank)",
                    'reference'   => $cycleId . '-' . $user->id,
                ]);

                // Notification
                DB::table('notifications')->insert([
                    'user_id'    => $user->id,
                    'type'       => 'leadership_bonus',
                    'title'      => 'Leadership Bonus Received',
                    'message'    => "You received $" . number_format($bonusAmount, 2) . " leadership bonus from the {$cycleId} monthly pool.",
                    'link'       => '/dashboard/referral',
                    'is_read'    => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $distributed += $bonusAmount;
                $recipientCount++;
            }
        });

        Log::info("Leadership bonus distributed: \${$distributed} to {$recipientCount} users (cycle {$cycleId})");

        return [
            'success'        => true,
            'cycle_id'       => $cycleId,
            'distributed'    => $distributed,
            'recipients'     => $recipientCount,
            'pool_amount'    => $poolAmount,
        ];
    }

    // =====================
    // Helper methods
    // =====================

    private function getDirectReferralRate(User $sponsor): float
    {
        // Use rank-based rate if available, else global setting
        if ($sponsor->rank_id) {
            $rank = Rank::find($sponsor->rank_id);
            if ($rank && $rank->direct_referral_percent > 0) {
                return (float) $rank->direct_referral_percent;
            }
        }
        return (float) PlatformSetting::get('direct_referral_commission', '10');
    }

    private function getMatchingRate(User $user): float
    {
        if ($user->rank_id) {
            $rank = Rank::find($user->rank_id);
            if ($rank && $rank->matching_bonus_percent > 0) {
                return (float) $rank->matching_bonus_percent;
            }
        }
        return (float) PlatformSetting::get('matching_bonus_rate', '10');
    }

    private function getTeamVolume(int $userId): float
    {
        $volume = 0;
        $currentLevel = [$userId];

        for ($i = 0; $i < 15; $i++) {
            $users = User::whereIn('id', $currentLevel)->get();
            foreach ($users as $u) {
                $volume += (float) ($u->total_invested ?? 0);
            }
            $nextLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $currentLevel = $nextLevel;
        }

        return $volume;
    }

    private function getDownlineCount(int $userId, int $maxDepth = 15): int
    {
        $count = 0;
        $currentLevel = [$userId];

        for ($i = 0; $i < $maxDepth; $i++) {
            $nextLevel = User::whereIn('parent_id', $currentLevel)->pluck('id')->toArray();
            if (empty($nextLevel)) break;
            $count += count($nextLevel);
            $currentLevel = $nextLevel;
        }

        return $count;
    }

    private function creditWallet(int $userId, string $walletType, float $amount, array $txData): void
    {
        $wallet = DB::table('wallets')->where('user_id', $userId)->where('type', $walletType)->first();

        if (!$wallet) {
            // Auto-create the wallet
            $walletId = DB::table('wallets')->insertGetId([
                'user_id'    => $userId,
                'type'       => $walletType,
                'balance'    => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $wallet = (object) ['id' => $walletId];
        } else {
            DB::table('wallets')->where('id', $wallet->id)->increment('balance', $amount);
        }

        // Create transaction
        DB::table('transactions')->insert([
            'user_id'     => $userId,
            'wallet_id'   => $wallet->id,
            'type'        => $txData['type'],
            'amount'      => $amount,
            'direction'   => 'credit',
            'reference'   => $txData['reference'] ?? 'TXN-' . now()->format('YmdHis'),
            'description' => $txData['description'] ?? '',
            'status'      => 'completed',
            'metadata'    => json_encode($txData['metadata'] ?? []),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
