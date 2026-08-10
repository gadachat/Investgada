@extends('layouts.admin')

@section('page-title', 'Cron Jobs')

@section('content')
<div class="fade-in">
    <h2 style="color: var(--text-bright); font-weight: 700; margin: 0 0 8px; font-size: 22px;">
        <i class="fas fa-clock" style="color: var(--purple-3);"></i> Automated Tasks (Cron Jobs)
    </h2>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Configure automated profit distribution, referral commissions, and system cleanup.</p>

    <!-- Cron Command -->
    <div class="card-custom mb-4">
        <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
            <i class="fas fa-terminal" style="color: var(--purple-3);"></i> cPanel Cron Setup
        </h5>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
            Add this single line to your hosting cron jobs (cPanel → Cron Jobs) to run all scheduled tasks automatically:
        </p>
        <div style="background: var(--bg-input); border: 1px solid var(--border); border-radius: 10px; padding: 16px; font-family: 'Courier New', monospace; font-size: 13px; color: var(--purple-3); overflow-x: auto;">
            * * * * * cd /home/yourusername/public_html && php artisan schedule:run >> /dev/null 2>&1
        </div>
        <p style="font-size: 12px; color: var(--text-dim); margin-top: 8px;">
            Set to run <strong>every minute</strong> — Laravel's scheduler handles the rest.
        </p>
    </div>

    <!-- Scheduled Jobs -->
    <div class="card-custom mb-4">
        <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
            <i class="fas fa-tasks" style="color: var(--purple-3);"></i> Scheduled Jobs
        </h5>

        <div style="overflow-x: auto;">
            <table class="table" style="color: var(--text); font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border); color: var(--text-muted); font-size: 11px; text-transform: uppercase;">
                        <th>Job</th>
                        <th>Schedule</th>
                        <th>Command</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td><i class="fas fa-coins" style="color: var(--green);"></i> Investment Payouts</td>
                        <td><span class="badge" style="background: rgba(99,102,241,0.15); color: var(--purple-1); font-size: 11px;">Daily 00:05</span></td>
                        <td style="font-family: monospace; font-size: 11px; color: var(--purple-3);">cron:investment-payouts</td>
                        <td>Distributes daily ROI profits to active investors and matures completed investments.</td>
                        <td><span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 10px;">Active</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td><i class="fas fa-users" style="color: var(--blue-1);"></i> Referral Commissions</td>
                        <td><span class="badge" style="background: rgba(99,102,241,0.15); color: var(--purple-1); font-size: 11px;">Every 6 hours</span></td>
                        <td style="font-family: monospace; font-size: 11px; color: var(--purple-3);">cron:referral-commissions</td>
                        <td>Pays direct referral commissions when referred users make approved deposits.</td>
                        <td><span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 10px;">Active</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td><i class="fas fa-sitemap" style="color: var(--purple-3);"></i> Matching Bonus</td>
                        <td><span class="badge" style="background: rgba(99,102,241,0.15); color: var(--purple-1); font-size: 11px;">Daily 00:30</span></td>
                        <td style="font-family: monospace; font-size: 11px; color: var(--purple-3);">cron:matching-bonus</td>
                        <td>Calculates binary matching bonuses on the weaker leg volume, capped by platform settings.</td>
                        <td><span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 10px;">Active</span></td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td><i class="fas fa-medal" style="color: var(--amber);"></i> Rank Advancement</td>
                        <td><span class="badge" style="background: rgba(99,102,241,0.15); color: var(--purple-1); font-size: 11px;">Daily 01:00</span></td>
                        <td style="font-family: monospace; font-size: 11px; color: var(--purple-3);">cron:rank-advancement</td>
                        <td>Auto-promotes users to higher ranks based on investment volume, referrals, and team volume.</td>
                        <td><span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 10px;">Active</span></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-broom" style="color: var(--text-muted);"></i> Cleanup</td>
                        <td><span class="badge" style="background: rgba(99,102,241,0.15); color: var(--purple-1); font-size: 11px;">Daily 02:00</span></td>
                        <td style="font-family: monospace; font-size: 11px; color: var(--purple-3);">cron:cleanup</td>
                        <td>Cancels expired deposits/withdrawals and clears old read notifications.</td>
                        <td><span class="badge" style="background: rgba(16,185,129,0.15); color: var(--green); font-size: 10px;">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manual Run -->
    <div class="card-custom mb-4">
        <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
            <i class="fas fa-play-circle" style="color: var(--purple-3);"></i> Manual Run (SSH / Terminal)
        </h5>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">
            If you have SSH access, you can run any job manually:
        </p>
        <div style="background: var(--bg-input); border: 1px solid var(--border); border-radius: 10px; padding: 16px; font-family: 'Courier New', monospace; font-size: 12px; color: var(--text); line-height: 1.8;">
            <span style="color: var(--text-dim);"># Run all jobs at once</span><br>
            <span style="color: var(--purple-3);">php artisan cron:run-all</span><br><br>
            <span style="color: var(--text-dim);"># Run a specific job</span><br>
            <span style="color: var(--purple-3);">php artisan cron:investment-payouts</span><br>
            <span style="color: var(--purple-3);">php artisan cron:referral-commissions</span><br>
            <span style="color: var(--purple-3);">php artisan cron:matching-bonus</span><br>
            <span style="color: var(--purple-3);">php artisan cron:rank-advancement</span><br>
            <span style="color: var(--purple-3);">php artisan cron:cleanup</span><br><br>
            <span style="color: var(--text-dim);"># Dry run (test without making changes)</span><br>
            <span style="color: var(--purple-3);">php artisan cron:run-all --dry-run</span><br>
            <span style="color: var(--purple-3);">php artisan cron:investment-payouts --dry-run</span>
        </div>
    </div>

    <!-- Log Location -->
    <div class="card-custom mb-4">
        <h5 style="color: var(--text-bright); font-weight: 700; margin-bottom: 16px;">
            <i class="fas fa-file-alt" style="color: var(--purple-3);"></i> Execution Logs
        </h5>
        <p style="font-size: 13px; color: var(--text-muted);">
            All cron job executions are logged to <code style="color: var(--purple-3); background: var(--bg-input); padding: 2px 8px; border-radius: 4px;">storage/logs/laravel.log</code>
            with details including processed counts, amounts paid, and any errors.
        </p>
    </div>
</div>
@endsection