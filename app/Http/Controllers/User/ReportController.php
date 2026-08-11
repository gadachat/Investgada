<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * User's personal analytics & performance report.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        // Portfolio summary
        $activeInvestments = DB::table('investments')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $completedInvestments = DB::table('investments')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->get();

        $totalActive = $activeInvestments->sum('amount');
        $totalEarned = $user->total_earned ?? 0;
        $totalInvested = $user->total_invested ?? 0;
        $totalWithdrawn = $user->total_withdrawn ?? 0;
        $roi = $totalInvested > 0 ? (($totalEarned / $totalInvested) * 100) : 0;

        // Deposit / withdrawal stats
        $totalDeposits = DB::table('deposits')
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->sum('amount');

        $totalWithdrawals = DB::table('withdrawals')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Period earnings
        $periodEarnings = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('direction', 'credit')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        // Earnings breakdown by type
        $earningsBreakdown = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where('direction', 'credit')
            ->where('created_at', '>=', $startDate)
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->type => (float) $item->total])
            ->toArray();

        // Earnings chart (daily)
        $earningsChart = $this->getEarningsChart($user->id, $period);

        // Investment performance by package
        $investmentPerformance = DB::table('investments')
            ->leftJoin('investment_packages', 'investments.package_id', '=', 'investment_packages.id')
            ->where('investments.user_id', $user->id)
            ->select(
                'investment_packages.name',
                'investment_packages.category',
                'investment_packages.return_rate',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(investments.amount) as invested'),
                DB::raw('SUM(investments.earned_amount) as earned'),
                DB::raw('AVG(investments.amount) as avg_amount')
            )
            ->groupBy('investment_packages.name', 'investment_packages.category', 'investment_packages.return_rate')
            ->orderByDesc('invested')
            ->get();

        // Monthly performance (last 6 months)
        $monthlyPerformance = $this->getMonthlyPerformance($user->id);

        // Transaction summary
        $transactionSummary = [
            'total' => DB::table('transactions')->where('user_id', $user->id)->count(),
            'credits' => DB::table('transactions')->where('user_id', $user->id)->where('direction', 'credit')->sum('amount'),
            'debits' => DB::table('transactions')->where('user_id', $user->id)->where('direction', 'debit')->sum('amount'),
        ];

        // Referral stats
        $directReferrals = DB::table('users')->where('sponsor_id', $user->id)->count();
        $referralEarnings = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereIn('type', ['referral_commission', 'matching_bonus'])
            ->sum('amount');

        return view('dashboard.reports.index', compact(
            'period', 'activeInvestments', 'completedInvestments',
            'totalActive', 'totalEarned', 'totalInvested', 'totalWithdrawn', 'roi',
            'totalDeposits', 'totalWithdrawals', 'periodEarnings',
            'earningsBreakdown', 'earningsChart', 'investmentPerformance',
            'monthlyPerformance', 'transactionSummary', 'directReferrals', 'referralEarnings'
        ));
    }

    private function getEarningsChart($userId, $days)
    {
        $data = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('direction', 'credit')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $values[] = (float) ($data->get($date)?->total ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function getMonthlyPerformance($userId)
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'label' => $date->format('M Y'),
                'invested' => DB::table('investments')
                    ->where('user_id', $userId)
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
                'earned' => DB::table('transactions')
                    ->where('user_id', $userId)
                    ->where('direction', 'credit')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
                'deposited' => DB::table('deposits')
                    ->where('user_id', $userId)
                    ->where('status', 'confirmed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
                'withdrawn' => DB::table('withdrawals')
                    ->where('user_id', $userId)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->sum('amount'),
            ];
        }
        return $months;
    }
}
