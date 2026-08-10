<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by the application.
     */
    protected $commands = [
        \App\Console\Commands\ProcessInvestmentPayouts::class,
        \App\Console\Commands\ProcessReferralCommissions::class,
        \App\Console\Commands\ProcessMatchingBonus::class,
        \App\Console\Commands\CheckRankAdvancement::class,
        \App\Console\Commands\CleanupExpiredRecords::class,
        \App\Console\Commands\RunAllCronJobs::class,
        \App\Console\Commands\ProcessAutoTrades::class,
        \App\Console\Commands\ProcessCopyTrades::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * CRON SETUP (shared hosting — cPanel Cron Jobs):
     *
     * * * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
     *
     * This single line runs ALL scheduled jobs below at their configured times.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily investment profit payouts — runs every day at 00:05
        $schedule->command('cron:investment-payouts')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->runInBackground();

        // Referral commissions — runs every 6 hours (catches approved deposits)
        $schedule->command('cron:referral-commissions')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();

        // Binary matching bonuses — runs daily at 00:30
        $schedule->command('cron:matching-bonus')
            ->dailyAt('00:30')
            ->withoutOverlapping()
            ->runInBackground();

        // Rank advancement check — runs daily at 01:00
        $schedule->command('cron:rank-advancement')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Cleanup expired records — runs daily at 02:00
        $schedule->command('cron:cleanup')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Auto-trade generator — runs every 5 minutes
        $schedule->command('cron:auto-trades')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Copy trading payouts — runs daily at 00:15 (after investment payouts)
        $schedule->command('cron:copy-trades')
            ->dailyAt('00:15')
            ->withoutOverlapping()
            ->runInBackground();

        // Clear expired cache — hourly
        $schedule->command('cache:clear')
            ->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
