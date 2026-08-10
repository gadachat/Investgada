<?php

namespace App\Console\Commands;

use App\Models\MasterTrader;
use App\Models\CopyTradingSubscription;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessCopyTrades extends Command
{
    protected $signature = 'cron:copy-trades
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Process copy trading payouts based on admin-configured win rate and profit percentage for each master trader.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info('=== Copy Trading Processor ===');
        if ($dryRun) $this->warn('DRY RUN — no changes will be made.');

        // Get all active master traders with active subscriptions
        $masters = MasterTrader::where('is_active', true)
            ->whereHas('subscriptions', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['subscriptions' => function ($q) {
                $q->where('is_active', true)->with('user');
            }, 'user'])
            ->get();

        if ($masters->isEmpty()) {
            $this->info('No active copy trading subscriptions to process.');
            return self::SUCCESS;
        }

        $this->info("Found {$masters->count()} master traders with active subscribers.");
        $totalProcessed = 0;
        $totalPayout = 0;
        $totalWins = 0;
        $totalLosses = 0;

        foreach ($masters as $master) {
            $this->info("");
            $this->info("━━ Master: {$master->user->name} ({$master->title})");
            $this->line("  Win Rate: {$master->display_win_rate}% | Daily Profit: {$master->daily_profit_pct}% | Trades/Day: {$master->trades_per_day}");

            foreach ($master->subscriptions as $sub) {
                $result = $this->processSubscription($master, $sub, $dryRun);

                if ($result['processed']) {
                    $totalProcessed++;
                    $totalPayout += $result['payout'];
                    if ($result['is_win']) $totalWins++;
                    else $totalLosses++;
                }
            }
        }

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Subscriptions processed: {$totalProcessed}");
        $this->info("Wins: {$totalWins} | Losses: {$totalLosses}");
        $this->info("Total payout: $" . number_format($totalPayout, 2));

        return self::SUCCESS;
    }

    /**
     * Process a single subscription — generate trades and distribute P&L.
     */
    private function processSubscription(MasterTrader $master, CopyTradingSubscription $sub, bool $dryRun): array
    {
        // Check how many trades have been processed today for this subscription
        $todayCount = $this->getTodayTradeCount($sub);

        $remainingTrades = $master->trades_per_day - $todayCount;
        if ($remainingTrades <= 0) {
            $this->line("  → {$sub->user->name}: daily limit reached ({$todayCount}/{$master->trades_per_day})");
            return ['processed' => false, 'payout' => 0, 'is_win' => false];
        }

        $winRate = (float) $master->display_win_rate;
        $dailyProfitPct = (float) $master->daily_profit_pct;
        $lossPct = (float) $master->loss_rate_pct;
        $variance = (float) $master->profit_variance;

        $allocation = (float) $sub->allocation_amount;
        $tradePayout = 0;
        $wins = 0;
        $losses = 0;

        // Process remaining trades for today
        for ($i = 0; $i < $remainingTrades; $i++) {
            // Determine win or loss based on admin-set win rate
            $isWin = (rand(1, 100) <= $winRate);

            if ($isWin) {
                // Win: profit based on admin-set daily profit %
                $baseProfit = ($allocation * $dailyProfitPct) / 100;
                // Spread across trades_per_day
                $perTradeProfit = $baseProfit / $master->trades_per_day;
                // Add variance
                $varianceFactor = 1 + (rand(-100, 100) / 100) * ($variance / 100);
                $profit = round($perTradeProfit * $varianceFactor, 2);
                $wins++;
            } else {
                // Loss: admin-set loss rate %
                $baseLoss = ($allocation * $lossPct) / 100;
                $perTradeLoss = $baseLoss / $master->trades_per_day;
                $varianceFactor = 1 + (rand(-50, 50) / 100) * ($variance / 100);
                $profit = -round($perTradeLoss * $varianceFactor, 2);
                $losses++;
            }

            $tradePayout += $profit;

            $this->line("  → {$sub->user->name}: Trade " . ($i + 1) . "/" . $master->trades_per_day . " — " .
                ($isWin ? "WIN +$" . number_format(abs($profit), 2) : "LOSS -$" . number_format(abs($profit), 2)) .
                " on $" . number_format($allocation, 2) . " allocation");

            if (!$dryRun) {
                $this->recordTrade($master, $sub, $profit, $isWin);
            }
        }

        // Distribute net payout to user's wallet
        if (!$dryRun && abs($tradePayout) >= 0.01) {
            $this->distributePayout($sub, $tradePayout);
        }

        return [
            'processed' => true,
            'payout'    => $tradePayout,
            'is_win'    => $wins > $losses,
        ];
    }

    /**
     * Record a single copied trade in the database.
     */
    private function recordTrade(MasterTrader $master, CopyTradingSubscription $sub, float $profit, bool $isWin): void
    {
        // Update subscription stats
        $sub->increment('total_copied');
        $sub->increment('total_pnl', $profit);
        if ($isWin) {
            $sub->increment('wins_count');
        } else {
            $sub->increment('losses_count');
        }
        $sub->update([
            'last_payout_at'     => now(),
            'last_payout_amount' => $profit,
        ]);

        // Update master trader stats
        $master->increment('total_trades');
        if ($isWin) {
            $master->increment('winning_trades');
            $master->increment('total_profit', $profit);
        }

        // Create transaction record
        Transaction::create([
            'user_id'     => $sub->user_id,
            'type'        => 'copy_trade',
            'amount'      => abs($profit),
            'status'      => 'completed',
            'reference'   => 'CT-' . strtoupper(Str::random(10)),
            'description' => "Copy trade: {$master->title} — " . ($isWin ? 'Profit' : 'Loss'),
            'metadata'    => json_encode([
                'master_trader_id' => $master->id,
                'master_name'      => $master->user->name,
                'is_win'            => $isWin,
                'profit'            => $profit,
                'allocation'        => (float) $sub->allocation_amount,
            ]),
        ]);

        // Notify user on significant trades
        if (abs($profit) >= 0.50) {
            Notification::create([
                'user_id'  => $sub->user_id,
                'type'     => 'copy_trade',
                'title'    => $isWin ? "Copy Trade Profit 📈" : "Copy Trade Loss 📉",
                'message'  => "{$master->user->name}: " . ($isWin ? '+' : '') . "$" . number_format($profit, 2) .
                              " on $" . number_format((float) $sub->allocation_amount, 2) . " allocation",
                'data'     => json_encode([
                    'master_trader_id' => $master->id,
                    'subscription_id'   => $sub->id,
                ]),
            ]);
        }
    }

    /**
     * Distribute net payout to user's interest wallet.
     */
    private function distributePayout(CopyTradingSubscription $sub, float $netPayout): void
    {
        if ($netPayout > 0) {
            // Profit goes to interest wallet
            $wallet = $sub->user->wallet('interest') ?? $sub->user->wallet('deposit');
            if ($wallet) {
                $wallet->credit($netPayout);
            }
        }
        // Losses reduce the allocation (tracked in total_pnl, wallet not debited —
        // the allocation was already locked at subscribe time)
    }

    /**
     * Count trades processed today for a subscription (via transactions).
     */
    private function getTodayTradeCount(CopyTradingSubscription $sub): int
    {
        return Transaction::where('user_id', $sub->user_id)
            ->where('type', 'copy_trade')
            ->whereDate('created_at', today())
            ->count();
    }
}
