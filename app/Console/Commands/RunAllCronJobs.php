<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RunAllCronJobs extends Command
{
    protected $signature = 'cron:run-all
                            {--only= : Run only a specific job: payouts, referrals, matching, ranks, cleanup, autotrade}
                            {--dry-run : Pass dry-run to all jobs}';

    protected $description = 'Run all automated cron jobs — investment payouts, referral commissions, matching bonuses, rank advancement, and cleanup.';

    private array $jobs = [
        'payouts'    => 'Investment payouts (daily profit distribution)',
        'referrals'  => 'Referral commissions (direct sponsor earnings)',
        'matching'   => 'Binary matching bonuses (weaker leg percentage)',
        'ranks'      => 'Rank advancement (auto-promotion check)',
        'cleanup'    => 'Cleanup expired pending deposits/withdrawals',
        'autotrade'  => 'Auto-trade generation (open/close trades)',
        'copytrade'  => 'Copy trading payouts (admin-set win rate & profit %)',
    ];

    public function handle(): int
    {
        $only = $this->option('only');
        $dryRun = $this->option('dry-run');

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║    APTrades — Cron Job Runner        ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('Started: ' . Carbon::now()->toDateTimeString());
        if ($dryRun) {
            $this->warn('DRY RUN MODE — no database changes will be made.');
        }
        $this->newLine();

        $jobsToRun = $only ? [$only => $this->jobs[$only] ?? null] : $this->jobs;

        $exitCode = self::SUCCESS;

        foreach ($jobsToRun as $key => $description) {
            if ($description === null) {
                $this->error("Unknown job: {$only}");
                $this->info("Available jobs: " . implode(', ', array_keys($this->jobs)));
                return self::FAILURE;
            }

            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("▶ [{$key}] {$description}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            $dryFlag = $dryRun ? '--dry-run' : '';

            $commands = [
                'payouts'   => 'cron:investment-payouts',
                'referrals'  => 'cron:referral-commissions',
                'matching'   => 'cron:matching-bonus',
                'ranks'      => 'cron:rank-advancement',
                'cleanup'    => 'cron:cleanup',
                'autotrade'  => 'cron:auto-trades',
                'copytrade'  => 'cron:copy-trades',
            ];

            $command = $commands[$key] . ($dryFlag ? ' ' . $dryFlag : '');

            $this->call($command);
            $this->newLine();
        }

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║    All cron jobs completed.              ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('Finished: ' . Carbon::now()->toDateTimeString());

        Log::info('Cron: All jobs completed', [
            'dry_run' => $dryRun,
            'only'    => $only,
        ]);

        return $exitCode;
    }
}
