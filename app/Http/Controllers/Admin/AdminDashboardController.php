<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\InvestmentPackage;
use App\Models\FeatureSetting;
use App\Models\SupportTicket;
use App\Models\SecurityLog;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Platform stats
        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('status', 'active')->count();
        $newUsersToday = User::where('role', 'user')->whereDate('created_at', today())->count();

        $totalDeposits = Deposit::where('status', 'confirmed')->sum('amount');
        $pendingDeposits = Deposit::where('status', 'pending')->count();
        $pendingDepositsAmount = Deposit::where('status', 'pending')->sum('amount');
        $depositsToday = Deposit::whereDate('created_at', today())->count();

        $totalWithdrawals = Withdrawal::where('status', 'completed')->sum('amount');
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $pendingWithdrawalsAmount = Withdrawal::where('status', 'pending')->sum('amount');

        $totalInvestments = Investment::where('status', 'active')->sum('amount');
        $activeInvestments = Investment::where('status', 'active')->count();

        $totalPayouts = Transaction::where('type', 'payout')->sum('amount');
        $totalCommissions = Transaction::whereIn('type', ['direct_referral', 'matching_bonus'])->sum('amount');

        // Recent deposits & withdrawals
        $recentDeposits = Deposit::with('user')->orderBy('created_at', 'desc')->limit(6)->get();
        $recentWithdrawals = Withdrawal::with('user')->orderBy('created_at', 'desc')->limit(6)->get();
        $recentUsers = User::where('role', 'user')->orderBy('created_at', 'desc')->limit(6)->get();

        // Charts — last 30 days
        $depositChart = Deposit::where('status', 'confirmed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $withdrawalChart = Withdrawal::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $chartLabels = [];
        $depositData = [];
        $withdrawalData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('M d');
            $depositData[] = (float) ($depositChart->get($date)?->total ?? 0);
            $withdrawalData[] = (float) ($withdrawalChart->get($date)?->total ?? 0);
        }

        // Investment package stats
        $packages = InvestmentPackage::orderBy('sort_order')->limit(5)->get();

        // Feature flags
        $features = FeatureSetting::orderBy('key')->get();

        // Support ticket stats
        $ticketStats = [
            'open'      => SupportTicket::whereIn('status', ['open', 'pending'])->count(),
            'answered'  => SupportTicket::where('status', 'answered')->count(),
            'urgent'    => SupportTicket::where('priority', 'urgent')->whereIn('status', ['open', 'pending', 'answered'])->count(),
            'closed'    => SupportTicket::where('status', 'closed')->count(),
            'total'     => SupportTicket::count(),
            'today'     => SupportTicket::whereDate('created_at', today())->count(),
        ];
        $recentTickets = SupportTicket::with('user')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 END")
            ->orderByDesc('updated_at')
            ->whereIn('status', ['open', 'pending', 'answered'])
            ->limit(6)
            ->get();

        // Security overview
        $securityStats = [
            'failed_logins_today' => LoginAttempt::where('successful', false)->whereDate('created_at', today())->count(),
            'critical_events'    => SecurityLog::whereIn('severity', ['critical', 'danger'])->whereDate('created_at', today())->count(),
            'blocked_ips'         => \App\Models\BlockedIp::where('type', 'blocked')->where('is_active', true)->count(),
        ];

        return view('admin.dashboard', compact(
            'totalUsers', 'activeUsers', 'newUsersToday',
            'totalDeposits', 'pendingDeposits', 'pendingDepositsAmount', 'depositsToday',
            'totalWithdrawals', 'pendingWithdrawals', 'pendingWithdrawalsAmount',
            'totalInvestments', 'activeInvestments',
            'totalPayouts', 'totalCommissions',
            'recentDeposits', 'recentWithdrawals', 'recentUsers',
            'chartLabels', 'depositData', 'withdrawalData',
            'packages', 'features',
            'ticketStats', 'recentTickets',
            'securityStats'
        ));
    }
}
