<?php

namespace App\Console\Commands;

use App\Models\AutoTradeSession;
use App\Models\AutoTrade;
use App\Models\Wallet;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessAutoTrades extends Command
{
    protected $signature = 'cron:auto-trades
                            {--dry-run : Show what would be processed without making changes}';

    protected $description = 'Generate and close auto-trades for active sessions based on admin-configured profit rates.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info('=== Auto-Trade Processor ===');
        if ($dryRun) $this->warn('DRY RUN — no changes will be made.');

        $settings = $this->getSettings();

        if (!$settings['enabled']) {
            $this->info('Auto-trading is disabled.');
            return self::SUCCESS;
        }

        // Find sessions that are due for a new trade
        $sessions = AutoTradeSession::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('next_trade_at')->orWhere('next_trade_at', '<=', now());
            })
            ->with('user')
            ->get();

        if ($sessions->isEmpty()) {
            $this->info('No sessions due for a trade.');
            return $this->closePendingTrades($dryRun, $settings);
        }

        $this->info("Found {$sessions->count()} sessions to process.");

        $tradesOpened = 0;
        $tradesClosed = 0;

        foreach ($sessions as $session) {
            // Check if session has hit daily trade limit
            $todayTrades = AutoTrade::where('session_id', $session->id)
                ->whereDate('created_at', today())
                ->count();

            if ($todayTrades >= $settings['trades_per_day']) {
                $this->line("  → Session {$session->reference}: daily limit reached ({$todayTrades}/{$settings['trades_per_day']})");
                continue;
            }

            // Check if session balance is too low
            if ((float) $session->current_balance < 1) {
                $this->line("  → Session {$session->reference}: balance too low, auto-stopping.");
                if (!$dryRun) $this->stopSession($session, 'Insufficient balance');
                continue;
            }

            // Generate a trade
            $trade = $this->generateTrade($session, $settings, $dryRun);
            if ($trade) $tradesOpened++;

            // Immediately close it with profit/loss (simulated instant trade)
            if (!$dryRun) {
                $closed = $this->closeTrade($trade, $session, $settings);
                if ($closed) $tradesClosed++;
            }

            // Schedule next trade
            if (!$dryRun) {
                $interval = rand(max(1, $settings['trade_interval_min'] - 15), $settings['trade_interval_min'] + 15);
                $session->update([
                    'next_trade_at' => now()->addMinutes($interval),
                    'last_trade_at' => now(),
                ]);
            }
        }

        // Also close any pending trades from previous runs
        $tradesClosed += $this->closePendingTrades($dryRun, $settings);

        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Trades opened: {$tradesOpened}");
        $this->info("Trades closed: {$tradesClosed}");

        return self::SUCCESS;
    }

    /**
     * Generate a trade for a session.
     */
    private function generateTrade(AutoTradeSession $session, array $settings, bool $dryRun): ?AutoTrade
    {
        $pairs = $session->selected_pairs ?? [];
        if (empty($pairs)) return null;

        $pair = $pairs[array_rand($pairs)];
        $category = $this->getPairCategory($pair);
        $pairName = $this->getPairName($pair);

        // Trade amount: 5-15% of current session balance per trade
        $tradePct = rand(5, 15) / 100;
        $tradeAmount = round((float) $session->current_balance * $tradePct, 2);
        $tradeAmount = max(1, $tradeAmount);

        // Entry price — realistic mock based on pair
        $entryPrice = $this->getMockPrice($pair);

        // Direction: random
        $direction = rand(0, 1) ? 'buy' : 'sell';

        $this->line("  → Session {$session->reference}: Opening {$direction} on {$pair} @ \${$entryPrice} (amount: \${$tradeAmount})");

        if ($dryRun) return null;

        $trade = AutoTrade::create([
            'reference'   => 'AT-' . strtoupper(Str::random(10)),
            'user_id'     => $session->user_id,
            'session_id'  => $session->id,
            'pair'        => $pair,
            'pair_name'   => $pairName,
            'category'    => $category,
            'direction'   => $direction,
            'entry_price' => $entryPrice,
            'exit_price'  => null,
            'amount'      => $tradeAmount,
            'profit'      => 0,
            'profit_pct'  => 0,
            'status'      => 'open',
            'is_win'      => null,
            'opened_at'   => now(),
            'closed_at'   => null,
            'duration_seconds' => 0,
        ]);

        return $trade;
    }

    /**
     * Close a trade with calculated profit/loss.
     */
    private function closeTrade(?AutoTrade $trade, AutoTradeSession $session, array $settings): bool
    {
        if (!$trade) return false;

        // Decide win or loss based on admin-configured win rate
        $isWin = (rand(1, 100) <= $settings['win_rate']);

        // Calculate profit
        if ($isWin) {
            // Win: profit is a slice of daily profit % spread across trades
            $baseProfit = ($trade->amount * $settings['daily_profit_pct']) / 100;
            // Add variance
            $variance = $settings['profit_variance'] / 100;
            $profit = $baseProfit * (1 + (rand(-100, 100) / 100) * $variance);
            $profit = round($profit, 2);
        } else {
            // Loss: smaller than wins, between 0.5% and stop_loss% of trade amount
            $lossPct = rand(50, (int)($settings['stop_loss_pct'] * 100)) / 100;
            $profit = -round($trade->amount * ($lossPct / 100), 2);
        }

        // Calculate exit price
        $entryPrice = (float) $trade->entry_price;
        $priceChange = $trade->direction === 'buy' ? $profit : -$profit;
        $pricePctChange = $trade->amount > 0 ? ($priceChange / $trade->amount) * 0.01 : 0;
        $exitPrice = $entryPrice * (1 + $pricePctChange);

        $duration = rand(15, 180); // 15 seconds to 3 minutes

        $trade->update([
            'exit_price'        => round($exitPrice, 8),
            'profit'            => $profit,
            'profit_pct'        => $trade->amount > 0 ? round(($profit / $trade->amount) * 100, 4) : 0,
            'status'            => 'closed',
            'is_win'            => $isWin,
            'closed_at'         => now(),
            'duration_seconds'  => $duration,
        ]);

        // Update session
        $newBalance = (float) $session->current_balance + $profit;
        $session->increment('total_trades');
        if ($isWin) {
            $session->increment('winning_trades');
            $session->increment('total_profit', $profit);
        } else {
            $session->increment('losing_trades');
            $session->increment('total_loss', abs($profit));
        }
        $session->update(['current_balance' => max(0, $newBalance)]);

        // Notify user on significant trades
        if (abs($profit) >= 1) {
            Notification::create([
                'user_id'  => $session->user_id,
                'type'     => 'autotrade',
                'title'    => $isWin ? 'Trade Won 📈' : 'Trade Lost 📉',
                'message'  => "{$trade->pair} {$trade->direction}: " . ($isWin ? '+' : '') . "\${$profit} on \${$trade->amount}",
                'data'     => json_encode(['trade_id' => $trade->id, 'pair' => $trade->pair]),
            ]);
        }

        $this->line("    → Closed: " . ($isWin ? 'WIN' : 'LOSS') . " {$trade->pair} profit: " . ($profit >= 0 ? '+' : '') . "\${$profit}");

        return true;
    }

    /**
     * Close any trades still in 'open' status (from interrupted runs).
     */
    private function closePendingTrades(bool $dryRun, array $settings): int
    {
        $open = AutoTrade::where('status', 'open')->with('session')->get();
        $closed = 0;

        foreach ($open as $trade) {
            if ($dryRun) { $closed++; continue; }
            $this->closeTrade($trade, $trade->session, $settings);
            $closed++;
        }

        return $closed;
    }

    /**
     * Auto-stop a session and return balance.
     */
    private function stopSession(AutoTradeSession $session, string $reason): void
    {
        $user = $session->user;
        $wallet = $user->wallet('deposit');

        if ($wallet && (float) $session->current_balance > 0) {
            $wallet->credit((float) $session->current_balance);
        }

        $session->update([
            'status'     => 'stopped',
            'stopped_at' => now(),
        ]);

        Notification::create([
            'user_id'  => $session->user_id,
            'type'     => 'autotrade',
            'title'    => 'Auto-Trade Session Stopped',
            'message'  => "Your session was stopped: {$reason}. $" . number_format((float) $session->current_balance, 2) . " returned to deposit wallet.",
        ]);
    }

    /**
     * Get admin-configured settings.
     */
    private function getSettings(): array
    {
        return [
            'enabled'            => Setting::get('autotrade_enabled', '1') === '1',
            'daily_profit_pct'   => (float) Setting::get('autotrade_daily_profit_pct', '2.5'),
            'win_rate'           => (float) Setting::get('autotrade_win_rate', '75'),
            'min_capital'        => (float) Setting::get('autotrade_min_capital', '50'),
            'max_capital'        => (float) Setting::get('autotrade_max_capital', '50000'),
            'trades_per_day'     => (int) Setting::get('autotrade_trades_per_day', '8'),
            'trade_interval_min' => (int) Setting::get('autotrade_trade_interval_min', '45'),
            'profit_variance'    => (float) Setting::get('autotrade_profit_variance', '20'),
            'stop_loss_pct'      => (float) Setting::get('autotrade_stop_loss_pct', '5'),
            'take_profit_pct'    => (float) Setting::get('autotrade_take_profit_pct', '3'),
        ];
    }

    private function getPairCategory(string $pair): string
    {
        if (str_contains($pair, '/')) return 'forex';
        if (in_array($pair, ['SPX', 'NDX', 'DJI', 'DAX', 'FTSE', 'NIKKEI', 'HSI', 'VIX'])) return 'indices';
        return 'crypto';
    }

    private function getPairName(string $pair): string
    {
        $names = [
            'BTC/USDT' => 'Bitcoin', 'ETH/USDT' => 'Ethereum', 'BNB/USDT' => 'BNB',
            'SOL/USDT' => 'Solana', 'XRP/USDT' => 'Ripple', 'ADA/USDT' => 'Cardano',
            'DOT/USDT' => 'Polkadot', 'DOGE/USDT' => 'Dogecoin',
            'EUR/USD' => 'Euro / US Dollar', 'GBP/USD' => 'British Pound / US Dollar',
            'USD/JPY' => 'US Dollar / Yen', 'AUD/USD' => 'Australian / US Dollar',
            'SPX' => 'S&P 500', 'NDX' => 'Nasdaq 100', 'DJI' => 'Dow Jones',
        ];
        return $names[$pair] ?? $pair;
    }

    private function getMockPrice(string $pair): float
    {
        $prices = [
            'BTC/USDT' => 67500, 'ETH/USDT' => 3450, 'BNB/USDT' => 590,
            'SOL/USDT' => 165, 'XRP/USDT' => 0.58, 'ADA/USDT' => 0.42,
            'EUR/USD' => 1.0850, 'GBP/USD' => 1.2720, 'USD/JPY' => 151.50,
            'AUD/USD' => 0.6580, 'SPX' => 5180, 'NDX' => 18250, 'DJI' => 38500,
        ];
        $base = $prices[$pair] ?? 100;
        $vol = $base > 1000 ? 0.01 : 0.03;
        return round($base * (1 + (rand(-100, 100) / 100) * $vol), $base < 1 ? 4 : 2);
    }
}
