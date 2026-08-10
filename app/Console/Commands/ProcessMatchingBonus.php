<?php

namespace App\Console\Commands;

use App\Models\MatchingBonus;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Rank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessMatchingBonus extends Command
{
    protected $signature = 'cron:matching-bonus
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Calculate and distribute binary matching bonuses — pays users a percentage of their weaker leg\'s business volume based on their rank.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info('=== Binary Matching Bonus Processor ===');
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $defaultRate = (float) Setting::get('matching_bonus_rate', '10');
        $matchingCap = (float) Setting::get('matching_cap', '50000');
        $today = Carbon::today();

        // Get all users who have a binary tree position and a rank
        $users = DB::table('users')
            ->whereNotNull('parent_id')
            ->whereNotNull('rank_id')
            ->where('status', 'active')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users with binary positions and ranks found.');
            return self::SUCCESS;
        }

        $this->info("Found {$users->count()} users to evaluate.");

        $totalPaid = 0;
        $processed = 0;
        $skipped = 0;

        foreach ($users as $user) {
            // Get the user's rank matching rate
            $rank = Rank::find($user->rank_id);
            if (!$rank) {
                $skipped++;
                continue;
            }

            $matchRate = $rank->matching_bonus_percent > 0 ? (float) $rank->matching_bonus_percent : $defaultRate;

            // Calculate left and right leg volumes
            // Left leg: all users in the left subtree of this user's binary position
            // Right leg: all users in the right subtree
            $leftVolume = $this->calculateLegVolume($user->id, 'left');
            $rightVolume = $this->calculateLegVolume($user->id, 'right');

            // The matching bonus is paid on the WEAKER (lesser) leg
            $weakerVolume = min($leftVolume, $rightVolume);

            if ($weakerVolume <= 0) {
                $skipped++;
                continue;
            }

            // Apply the matching cap
            $cappedVolume = min($weakerVolume, $matchingCap);

            // Calculate bonus
            $bonus = round($cappedVolume * ($matchRate / 100), 2);

            if ($bonus <= 0) {
                $skipped++;
                continue;
            }

            $this->line("  → User: {$user->name} | Left: \${$leftVolume} | Right: \${$rightVolume} | Weaker: \${$weakerVolume} | Rate: {$matchRate}% | Bonus: \${$bonus}");

            if ($dryRun) {
                $processed++;
                $totalPaid += $bonus;
                continue;
            }

            DB::transaction(function () use ($user, $bonus, $matchRate, $leftVolume, $rightVolume, $cappedVolume, $rank, $today, &$processed, &$totalPaid) {
                // Create matching bonus record
                MatchingBonus::create([
                    'user_id'      => $user->id,
                    'left_volume'  => $leftVolume,
                    'right_volume' => $rightVolume,
                    'match_volume' => $cappedVolume,
                    'match_rate'   => $matchRate,
                    'bonus_amount' => $bonus,
                    'rank_id'      => $rank->id,
                    'cycle_date'   => $today,
                    'status'       => 'paid',
                ]);

                // Credit user's matching wallet
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $user->id, 'type' => 'matching'],
                    ['balance' => 0, 'currency' => 'USD']
                );
                $wallet->increment('balance', $bonus);

                // Record transaction
                Transaction::create([
                    'user_id'    => $user->id,
                    'type'       => 'matching_bonus',
                    'amount'     => $bonus,
                    'wallet_type'=> 'matching',
                    'status'     => 'completed',
                    'reference'  => 'MATCH-' . $user->id . '-' . $today->format('Ymd'),
                    'description'=> "Binary matching bonus ({$rank->name} rank) — Left: \${$leftVolume}, Right: \${$rightVolume}",
                    'metadata'   => json_encode([
                        'left_volume'  => $leftVolume,
                        'right_volume' => $rightVolume,
                        'match_volume' => $cappedVolume,
                        'rate'         => $matchRate,
                        'rank'         => $rank->name,
                    ]),
                ]);

                // Update total earned
                DB::table('users')->where('id', $user->id)->increment('total_earned', $bonus);

                // Notify user
                Notification::create([
                    'user_id'  => $user->id,
                    'type'     => 'matching',
                    'title'    => 'Matching Bonus Earned',
                    'message'  => "You earned \${$bonus} binary matching bonus ({$rank->name} rank).",
                    'data'     => json_encode(['amount' => $bonus, 'left' => $leftVolume, 'right' => $rightVolume]),
                ]);

                $processed++;
                $totalPaid += $bonus;
            });
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Processed: {$processed}");
        $this->info("Total paid: \${$totalPaid}");
        $this->info("Skipped: {$skipped}");

        Log::info('Cron: Matching bonuses processed', [
            'processed' => $processed,
            'total_paid'=> $totalPaid,
            'skipped'   => $skipped,
            'dry_run'   => $dryRun,
        ]);

        return self::SUCCESS;
    }

    /**
     * Calculate the total business volume (deposits + investments) for a leg.
     */
    private function calculateLegVolume(int $userId, string $position): float
    {
        // Find all descendants in the specified leg of the binary tree
        $descendants = $this->getBinaryDescendants($userId, $position);

        if ($descendants->isEmpty()) {
            return 0;
        }

        // Sum their deposits (approved) + investments (active/completed)
        $depositVolume = DB::table('deposits')
            ->whereIn('user_id', $descendants)
            ->where('status', 'approved')
            ->sum('amount');

        $investmentVolume = DB::table('investments')
            ->whereIn('user_id', $descendants)
            ->whereIn('status', ['active', 'completed'])
            ->sum('amount');

        return (float) ($depositVolume + $investmentVolume);
    }

    /**
     * Get all descendant user IDs in a binary tree leg.
     */
    private function getBinaryDescendants(int $parentId, string $position, $depth = 0): \Illuminate\Support\Collection
    {
        $maxDepth = 50; // Prevent infinite loops
        if ($depth > $maxDepth) {
            return collect();
        }

        // Find the direct child in the specified position
        $child = DB::table('users')
            ->where('parent_id', $parentId)
            ->where('binary_position', $position)
            ->first();

        if (!$child) {
            return collect();
        }

        // Collect this child and all of their descendants (both legs)
        $ids = collect([$child->id]);
        $leftDescendants = $this->getBinaryDescendants($child->id, 'left', $depth + 1);
        $rightDescendants = $this->getBinaryDescendants($child->id, 'right', $depth + 1);

        return $ids->merge($leftDescendants)->merge($rightDescendants);
    }
}
