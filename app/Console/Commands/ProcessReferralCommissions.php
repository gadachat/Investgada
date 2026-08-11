<?php

namespace App\Console\Commands;

use App\Models\Referral;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessReferralCommissions extends Command
{
    protected $signature = 'cron:referral-commissions
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Process direct referral commissions — pay sponsors a percentage when their referrals make deposits or investments.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info('=== Referral Commission Processor ===');
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        // Get the global referral commission rate
        $defaultRate = (float) Setting::get('referral_commission', '10');

        // Find deposits that were approved but commissions not yet paid
        $pendingDeposits = DB::table('deposits')
            ->where('status', 'confirmed')
            ->where('commission_paid', false)
            ->whereNotNull('user_id')
            ->limit(500)
            ->get();

        if ($pendingDeposits->isEmpty()) {
            $this->info('No pending referral commissions.');
            return self::SUCCESS;
        }

        $this->info("Found {$pendingDeposits->count()} approved deposits to process.");

        $totalPaid = 0;
        $processed = 0;
        $skipped = 0;

        foreach ($pendingDeposits as $deposit) {
            // Find the user's sponsor
            $user = DB::table('users')->where('id', $deposit->user_id)->first();
            if (!$user || !$user->sponsor_id) {
                $skipped++;
                continue;
            }

            $sponsor = DB::table('users')->where('id', $user->sponsor_id)->first();
            if (!$sponsor) {
                $skipped++;
                continue;
            }

            // Determine commission rate: check sponsor's rank for custom rate, else default
            $commissionRate = $defaultRate;
            if ($sponsor->rank_id) {
                $rank = DB::table('ranks')->where('id', $sponsor->rank_id)->first();
                if ($rank && $rank->direct_referral_percent > 0) {
                    $commissionRate = (float) $rank->direct_referral_percent;
                }
            }

            // Calculate commission on the net deposit amount (after deposit fee)
            $netAmount = $deposit->amount;
            $commission = round($netAmount * ($commissionRate / 100), 2);

            if ($commission <= 0) {
                $skipped++;
                continue;
            }

            $this->line("  → User: {$user->name} deposited \${$netAmount} → Sponsor: {$sponsor->name} gets \${$commission} ({$commissionRate}%)");

            if ($dryRun) {
                $processed++;
                $totalPaid += $commission;
                continue;
            }

            DB::transaction(function () use ($deposit, $sponsor, $commission, $commissionRate, $netAmount, $user, &$processed, &$totalPaid) {
                // Credit sponsor's referral wallet
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $sponsor->id, 'type' => 'referral'],
                    ['balance' => 0, 'currency' => 'USD']
                );
                $wallet->increment('balance', $commission);

                // Record transaction
                Transaction::create([
                    'user_id'    => $sponsor->id,
                    'type'       => 'referral_commission',
                    'amount'     => $commission,
                    'wallet_type'=> 'referral',
                    'status'     => 'completed',
                    'reference'  => 'REF-' . $deposit->id . '-' . $sponsor->id,
                    'description'=> "Direct referral commission from {$user->name}'s deposit of \${$netAmount}",
                    'metadata'   => json_encode([
                        'deposit_id'   => $deposit->id,
                        'referred_user' => $user->name,
                        'deposit_amount'=> $netAmount,
                        'rate'          => $commissionRate,
                    ]),
                ]);

                // Update referral record
                Referral::where('referred_id', $user->id)->update([
                    'commission_earned' => DB::raw('commission_earned + ' . $commission),
                    'status'            => 'active',
                ]);

                // Update sponsor's total referral earnings
                DB::table('users')->where('id', $sponsor->id)->increment('total_referral_earnings', $commission);

                // Mark deposit as commission paid
                DB::table('deposits')->where('id', $deposit->id)->update(['commission_paid' => true]);

                // Notify the sponsor
                Notification::create([
                    'user_id'  => $sponsor->id,
                    'type'     => 'referral',
                    'title'    => 'Referral Commission Earned',
                    'message'  => "You earned \${$commission} referral commission from {$user->name}'s deposit.",
                    'data'     => json_encode([
                        'amount'       => $commission,
                        'referred_user' => $user->name,
                    ]),
                ]);

                $processed++;
                $totalPaid += $commission;
            });
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Processed: {$processed}");
        $this->info("Total paid: \${$totalPaid}");
        $this->info("Skipped (no sponsor): {$skipped}");

        Log::info('Cron: Referral commissions processed', [
            'processed' => $processed,
            'total_paid'=> $totalPaid,
            'skipped'   => $skipped,
            'dry_run'   => $dryRun,
        ]);

        return self::SUCCESS;
    }
}
