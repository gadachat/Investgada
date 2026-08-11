<?php

namespace App\Console\Commands;

use App\Models\Investment;
use App\Models\InvestmentPayout;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessInvestmentPayouts extends Command
{
    protected $signature = 'cron:investment-payouts
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Process daily investment payouts — distribute ROI profits to investors based on their package return rates.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $today = Carbon::today();

        $this->info('=== Investment Payout Processor ===');
        $this->info("Date: {$today->toDateString()}");
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        // Find active investments that are due for a payout today
        $investments = Investment::where('status', 'active')
            ->whereNotNull('activated_at')
            ->where('activated_at', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('next_payout_at')->orWhere('next_payout_at', '<=', $today);
            })
            ->whereDoesntHave('payouts', function ($q) use ($today) {
                $q->whereDate('payout_at', $today);
            })
            ->with('package', 'user')
            ->get();

        if ($investments->isEmpty()) {
            $this->info('No investments due for payout today.');
            return self::SUCCESS;
        }

        $this->info("Found {$investments->count()} investments to process.");

        $totalPaid = 0;
        $processed = 0;
        $errors = 0;

        foreach ($investments as $investment) {
            $package = $investment->package;
            if (!$package) {
                $this->error("  ✗ Investment #{$investment->id}: Package not found");
                $errors++;
                continue;
            }

            // Calculate per-cycle profit: amount * return_rate / 100
            // return_rate is the yield per cycle (e.g., 2% daily, 10% weekly)
            $dailyProfit = ($investment->amount * $package->return_rate / 100);
            $dailyProfit = round($dailyProfit, 2);

            if ($dailyProfit <= 0) {
                $this->warn("  ⚠ Investment #{$investment->id}: Daily profit is 0");
                continue;
            }

            $this->line("  → Investment #{$investment->id} (User: {$investment->user->name}) — Daily: \${$dailyProfit}");

            if ($dryRun) {
                $processed++;
                $totalPaid += $dailyProfit;
                continue;
            }

            DB::transaction(function () use ($investment, $dailyProfit, $package, $today, &$processed, &$totalPaid) {
                // Calculate cycle number
                $cycleNumber = $investment->payouts()->count() + 1;

                // Create payout record
                InvestmentPayout::create([
                    'investment_id' => $investment->id,
                    'user_id'       => $investment->user_id,
                    'amount'        => $dailyProfit,
                    'cycle_number'  => $cycleNumber,
                    'payout_at'     => $today,
                ]);

                // Credit the user's interest wallet
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $investment->user_id, 'type' => 'interest'],
                    ['balance' => 0, 'currency' => 'USD']
                );
                $wallet->credit($dailyProfit);

                // Record transaction
                Transaction::create([
                    'user_id'       => $investment->user_id,
                    'wallet_id'     => $wallet->id,
                    'type'          => 'investment_profit',
                    'direction'     => 'credit',
                    'amount'        => $dailyProfit,
                    'balance_after' => $wallet->fresh()->balance,
                    'currency'      => 'USD',
                    'status'        => 'completed',
                    'reference'      => 'INV-' . $investment->id . '-DAILY-' . $today->format('Ymd'),
                    'description'   => "Daily profit from {$package->name} package",
                    'metadata'      => json_encode([
                        'investment_id' => $investment->id,
                        'package'       => $package->name,
                        'cycle'         => $cycleNumber,
                    ]),
                ]);

                // Update investment tracking
                $investment->update([
                    'earned_so_far' => $investment->earned_so_far + $dailyProfit,
                    'last_payout_at' => $today,
                    'next_payout_at' => Carbon::now()->addDays($package->cycle_days),
                ]);

                // Update user's total earned
                DB::table('users')->where('id', $investment->user_id)->increment('total_earned', $dailyProfit);

                // Notify the user
                Notification::create([
                    'user_id'  => $investment->user_id,
                    'type'     => 'investment',
                    'title'    => 'Daily Profit Received',
                    'message'  => "\${$dailyProfit} daily profit credited from your {$package->name} investment.",
                    'data'     => json_encode(['investment_id' => $investment->id, 'amount' => $dailyProfit]),
                ]);

                $processed++;
                $totalPaid += $dailyProfit;
            });
        }

        // Check for matured investments (completed their duration)
        $matured = Investment::where('status', 'active')
            ->whereNotNull('matures_at')
            ->where('matures_at', '<=', now())
            ->get();

        if ($matured->isNotEmpty()) {
            $this->info("\n--- Maturing Investments ---");
            foreach ($matured as $inv) {
                $pkg = $inv->package;
                $this->line("  → Investment #{$inv->id} has completed. Marking as completed.");

                if (!$dryRun) {
                    DB::transaction(function () use ($inv, $pkg) {
                        // Only return principal if the package allows it
                        if ($pkg && $pkg->principal_return) {
                            $principal = (float) $inv->amount;
                            $wallet = Wallet::firstOrCreate(
                                ['user_id' => $inv->user_id, 'type' => 'deposit'],
                                ['balance' => 0, 'currency' => 'USD']
                            );
                            $wallet->credit($principal);

                            Transaction::create([
                                'user_id'       => $inv->user_id,
                                'wallet_id'     => $wallet->id,
                                'type'          => 'principal_return',
                                'direction'     => 'credit',
                                'amount'        => $principal,
                                'balance_after' => $wallet->fresh()->balance,
                                'currency'      => 'USD',
                                'status'        => 'completed',
                                'reference'      => 'PRINCIPAL-' . $inv->id,
                                'description'   => 'Investment principal returned',
                            ]);
                        }

                        $inv->update(['status' => 'completed']);

                        Notification::create([
                            'user_id'  => $inv->user_id,
                            'type'     => 'investment',
                            'title'    => 'Investment Completed',
                            'message'  => "Your investment has completed." . ($pkg && $pkg->principal_return ? " Principal returned to your deposit wallet." : ""),
                        ]);
                    });
                }
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Processed: {$processed}");
        $this->info("Total paid: \${$totalPaid}");
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }

        Log::info('Cron: Investment payouts processed', [
            'processed' => $processed,
            'total_paid'=> $totalPaid,
            'errors'    => $errors,
            'dry_run'   => $dryRun,
        ]);

        return self::SUCCESS;
    }
}
