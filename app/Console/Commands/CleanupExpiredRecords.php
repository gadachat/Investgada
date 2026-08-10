<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupExpiredRecords extends Command
{
    protected $signature = 'cron:cleanup
                            {--dry-run : Show what would be cleaned without making changes}';

    protected $description = 'Clean up expired pending deposits, stale withdrawals, and old read notifications.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info('=== Cleanup Expired Records ===');
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $cutoff = Carbon::now()->subHours(48);

        // 1. Cancel pending deposits older than 48 hours
        $expiredDeposits = DB::table('deposits')
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->count();

        $this->line("  → Expired pending deposits (>{$cutoff->format('Y-m-d H:i')}): {$expiredDeposits}");

        if (!$dryRun && $expiredDeposits > 0) {
            DB::table('deposits')
                ->where('status', 'pending')
                ->where('created_at', '<', $cutoff)
                ->update(['status' => 'expired', 'updated_at' => now()]);
        }

        // 2. Cancel pending withdrawals older than 48 hours
        $expiredWithdrawals = DB::table('withdrawals')
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->count();

        $this->line("  → Expired pending withdrawals: {$expiredWithdrawals}");

        if (!$dryRun && $expiredWithdrawals > 0) {
            DB::table('withdrawals')
                ->where('status', 'pending')
                ->where('created_at', '<', $cutoff)
                ->update(['status' => 'cancelled', 'admin_note' => 'Auto-cancelled: expired', 'updated_at' => now()]);
        }

        // 3. Delete read notifications older than 30 days
        $oldNotifications = DB::table('notifications')
            ->where('is_read', true)
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->count();

        $this->line("  → Old read notifications (>30 days): {$oldNotifications}");

        if (!$dryRun && $oldNotifications > 0) {
            DB::table('notifications')
                ->where('is_read', true)
                ->where('created_at', '<', Carbon::now()->subDays(30))
                ->delete();
        }

        // 4. Delete expired sessions
        $expiredSessions = DB::table('sessions')
            ->where('last_activity', '<', Carbon::now()->subHours(12)->timestamp)
            ->count();

        $this->line("  → Expired sessions: {$expiredSessions}");

        if (!$dryRun && $expiredSessions > 0) {
            DB::table('sessions')
                ->where('last_activity', '<', Carbon::now()->subHours(12)->timestamp)
                ->delete();
        }

        $this->newLine();
        $this->info("=== Cleanup Complete ===");
        $this->info("  Deposits cancelled: {$expiredDeposits}");
        $this->info("  Withdrawals cancelled: {$expiredWithdrawals}");
        $this->info("  Notifications deleted: {$oldNotifications}");
        $this->info("  Sessions cleared: {$expiredSessions}");

        return self::SUCCESS;
    }
}
