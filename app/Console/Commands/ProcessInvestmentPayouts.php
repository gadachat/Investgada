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
            ->whereDate('start_date', '<=', $today)
            ->whereRaw('DATEDIFF(NOW(), start_date) <= duration_days')
            ->whereDoesntHave('payouts', function ($q) use ($today) {
                $q->whereDate('created_at', $today);
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

            // Calculate daily profit: (amount * return_rate / 100) / duration_days
            $dailyProfit = ($investment->amount * $package->return_rate / 100) / $package->duration_days;
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
                // Create payout record
                InvestmentPayout::create([
                    'investment_id' => $investment->id,
                    'user_id'       => $investment->user_id,
                    'amount'        => $dailyProfit,
                    'type'          => 'daily_profit',
                    'payout_date'   => $today,
                    'status'        => 'paid',
                ]);

                // Credit the user's earning wallet
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $investment->user_id, 'type' => 'earning'],
                    ['balance' => 0, 'currency' => 'USD']
                );
                $wallet->increment('balance', $dailyProfit);

                // Record transaction
                Transaction::create([
                    'user_id'    => $investment->user_id,
                    'type'       => 'investment_profit',
                    'amount'     => $dailyProfit,
                    'wallet_type'=> 'earning',
                    'status'     => 'completed',
                    'reference'  => 'INV-' . $investment->id . '-DAILY-' . $today->format('Ymd'),
                    'description'=> "Daily profit from {$package->name} package",
                    'metadata'   => json_encode([
                        'investment_id' => $investment->id,
                        'package'       => $package->name,
                        'day'           => $investment->start_date->diffInDays($today) + 1,
                        'total_days'     => $package->duration_days,
                    ]),
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
            ->whereRaw('DATEDIFF(NOW(), start_date) >= duration_days')
            ->get();

        if ($matured->isNotEmpty()) {
            $this->info("\n--- Maturing Investments ---");
            foreach ($matured as $inv) {
                $this->line("  → Investment #{$inv->id} has completed its duration. Marking as completed.");

                if (!$dryRun) {
                    // Return the principal to the user's main wallet
                    $principal = $inv->amount;

                    DB::transaction(function () use ($inv, $principal) {
                        $wallet = Wallet::firstOrCreate(
                            ['user_id' => $inv->user_id, 'type' => 'main'],
                            ['balance' => 0, 'currency' => 'USD']
                        );
                        $wallet->increment('balance', $principal);

                        $inv->update(['status' => 'completed', 'matures_at' => $inv->matures_at]);

                        Transaction::create([
                            'user_id'    => $inv->user_id,
                            'type'       => 'principal_return',
                            'amount'     => $principal,
                            'wallet_type'=> 'main',
                            'status'     => 'completed',
                            'reference'  => 'PRINCIPAL-' . $inv->id,
                            'description'=> 'Investment principal returned',
                        ]);

                        Notification::create([
                            'user_id'  => $inv->user_id,
                            'type'     => 'investment',
                            'title'    => 'Investment Completed',
                            'message'  => "Your investment of \${$principal} has completed. Principal returned to your main wallet.",
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
