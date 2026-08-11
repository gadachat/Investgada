<?php

namespace App\Console\Commands;

use App\Models\Rank;
use App\Models\RankReward;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Console\Command;
use App\Services\NotifyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckRankAdvancement extends Command
{
    protected $signature = 'cron:rank-advancement
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Check and promote users to higher ranks based on investment volume, direct referrals, and team volume.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info('=== Rank Advancement Checker ===');
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $ranks = Rank::orderBy('sort_order')->get();

        if ($ranks->isEmpty()) {
            $this->info('No ranks configured.');
            return self::SUCCESS;
        }

        $this->info("Found {$ranks->count()} ranks to evaluate.");

        $promoted = 0;
        $kept = 0;

        // Get all active users
        $users = DB::table('users')
            ->where('status', 'active')
            ->where('is_admin', false)
            ->get();

        foreach ($users as $user) {
            $currentRankId = $user->rank_id;
            $newRank = null;

            // Check each rank from highest to lowest
            foreach ($ranks as $rank) {
                if ($rank->id === $currentRankId) {
                    break; // Already at this rank
                }

                $meetsRequirements = true;

                // Check minimum investment
                if ($rank->min_investment > 0 && $user->total_invested < $rank->min_investment) {
                    $meetsRequirements = false;
                }

                // Check minimum direct referrals
                if ($rank->min_direct_referrals > 0) {
                    $directCount = DB::table('referrals')
                        ->where('referrer_id', $user->id)
                        ->where('status', 'active')
                        ->count();
                    if ($directCount < $rank->min_direct_referrals) {
                        $meetsRequirements = false;
                    }
                }

                // Check minimum team volume
                if ($rank->min_team_volume > 0) {
                    $teamVolume = $this->calculateTeamVolume($user->id);
                    if ($teamVolume < $rank->min_team_volume) {
                        $meetsRequirements = false;
                    }
                }

                if ($meetsRequirements) {
                    $newRank = $rank;
                    break;
                }
            }

            if ($newRank && $newRank->id !== $currentRankId) {
                $this->line("  → PROMOTE: {$user->name} → {$newRank->name}");

                if ($dryRun) {
                    $promoted++;
                    continue;
                }

                DB::transaction(function () use ($user, $newRank, &$promoted) {
                    // Update user's rank
                    DB::table('users')->where('id', $user->id)->update(['rank_id' => $newRank->id]);

                    // Create rank reward record
                    RankReward::create([
                        'user_id'       => $user->id,
                        'rank_id'       => $newRank->id,
                        'reward_amount' => $newRank->salary_bonus ?? 0,
                        'type'          => $newRank->salary_bonus > 0 ? 'salary' : 'bonus',
                        'description'   => "Promoted to {$newRank->name} rank",
                    ]);

                    // If there's a salary bonus, credit it
                    if ($newRank->salary_bonus > 0) {
                        $wallet = \App\Models\Wallet::firstOrCreate(
                            ['user_id' => $user->id, 'type' => 'deposit'],
                            ['balance' => 0, 'currency' => 'USD']
                        );
                        $wallet->credit($newRank->salary_bonus);

                        \App\Models\Transaction::create([
                            'user_id'       => $user->id,
                            'wallet_id'     => $wallet->id,
                            'type'          => 'rank_bonus',
                            'direction'     => 'credit',
                            'amount'        => $newRank->salary_bonus,
                            'balance_after' => $wallet->fresh()->balance,
                            'currency'      => 'USD',
                            'status'        => 'completed',
                            'reference'     => 'RANK-' . $user->id . '-' . $newRank->id,
                            'description'   => "{$newRank->name} rank promotion bonus",
                        ]);
                    }

                    // Notify user
                    Notification::create([
                        'user_id'  => $user->id,
                        'type'     => 'rank',
                        'title'    => 'Rank Promotion!',
                        'message'  => "Congratulations! You've been promoted to {$newRank->name} rank" . ($newRank->salary_bonus > 0 ? " with a \${$newRank->salary_bonus} bonus." : "."),
                        'data'     => json_encode(['rank' => $newRank->name, 'bonus' => $newRank->salary_bonus ?? 0]),
                    ]);

                    $promoted++;
                });
            } else {
                $kept++;
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Promoted: {$promoted}");
        $this->info("Kept current rank: {$kept}");

        Log::info('Cron: Rank advancement checked', [
            'promoted' => $promoted,
            'kept'     => $kept,
            'dry_run'  => $dryRun,
        ]);

        return self::SUCCESS;
    }

    /**
     * Calculate total team volume (all referrals' investments).
     */
    private function calculateTeamVolume(int $userId): float
    {
        // Direct referrals' investments
        $directReferrals = DB::table('referrals')
            ->where('referrer_id', $userId)
            ->where('status', 'active')
            ->pluck('referred_id');

        if ($directReferrals->isEmpty()) {
            return 0;
        }

        $volume = DB::table('investments')
            ->whereIn('user_id', $directReferrals)
            ->whereIn('status', ['active', 'completed'])
            ->sum('amount');

        // Also include binary subtree volume
        $binaryVolume = $this->getBinarySubtreeVolume($userId);
        $volume += $binaryVolume;

        return (float) $volume;
    }

    /**
     * Get total investment volume of all binary subtree members.
     */
    private function getBinarySubtreeVolume(int $parentId, int $depth = 0): float
    {
        if ($depth > 50) {
            return 0;
        }

        $children = DB::table('users')
            ->where('parent_id', $parentId)
            ->get();

        $volume = 0;
        foreach ($children as $child) {
            $volume += DB::table('investments')
                ->where('user_id', $child->id)
                ->whereIn('status', ['active', 'completed'])
                ->sum('amount');

            $volume += $this->getBinarySubtreeVolume($child->id, $depth + 1);
        }

        return (float) $volume;
    }
}
