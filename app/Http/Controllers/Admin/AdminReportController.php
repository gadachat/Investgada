<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    /**
     * Main reports dashboard with overview analytics.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        // Revenue stats
        $totalRevenue = DB::table('transactions')
            ->whereIn('type', ['deposit', 'investment'])
            ->where('direction', 'credit')
            ->where('status', 'completed')
            ->sum('amount');

        $totalPayouts = DB::table('transactions')
            ->where('type', 'profit_share')
            ->where('status', 'completed')
            ->sum('amount');

        $totalCommissions = DB::table('transactions')
            ->whereIn('type', ['referral_commission', 'matching_bonus'])
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawals = DB::table('withdrawals')
            ->where('status', 'completed')
            ->sum('amount');

        $netRevenue = $totalRevenue - $totalPayouts - $totalCommissions - $totalWithdrawals;

        // Period stats
        $periodDeposits = DB::table('deposits')
            ->where('status', 'confirmed')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $periodWithdrawals = DB::table('withdrawals')
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $periodUsers = DB::table('users')
            ->where('is_admin', false)
            ->where('created_at', '>=', $startDate)
            ->count();

        $periodInvestments = DB::table('investments')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        // Revenue chart data
        $revenueChart = $this->getRevenueChart($period);

        // User growth chart
        $userGrowthChart = $this->getUserGrowthChart($period);

        // Deposit vs Withdrawal chart
        $flowChart = $this->getFlowChart($period);

        // Investment by category
        $categoryBreakdown = $this->getCategoryBreakdown();

        // Top investors
        $topInvestors = DB::table('users')
            ->where('is_admin', false)
            ->orderByDesc('total_invested')
            ->limit(10)
            ->get(['id', 'name', 'email', 'total_invested', 'total_earned', 'created_at']);

        // Top earners
        $topEarners = DB::table('users')
            ->where('is_admin', false)
            ->orderByDesc('total_earned')
            ->limit(10)
            ->get(['id', 'name', 'email', 'total_invested', 'total_earned']);

        // Package performance
        $packagePerformance = DB::table('investment_packages')
            ->leftJoin('investments', 'investment_packages.id', '=', 'investments.package_id')
            ->select(
                'investment_packages.id',
                'investment_packages.name',
                'investment_packages.category',
                'investment_packages.return_rate',
                DB::raw('COUNT(investments.id) as total_investments'),
                DB::raw('COALESCE(SUM(investments.amount), 0) as total_volume'),
                DB::raw('COALESCE(SUM(CASE WHEN investments.status = "active" THEN investments.amount ELSE 0 END), 0) as active_volume'),
                DB::raw('COALESCE(SUM(investments.earned_amount), 0) as total_earned')
            )
            ->groupBy('investment_packages.id', 'investment_packages.name', 'investment_packages.category', 'investment_packages.return_rate')
            ->orderByDesc('total_volume')
            ->get();

        // Recent transactions
        $recentTransactions = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
            ->orderByDesc('transactions.created_at')
            ->limit(15)
            ->get();

        // User demographics
        $userStatusBreakdown = DB::table('users')
            ->where('is_admin', false)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // KYC stats
        $kycStats = [
            'verified' => DB::table('users')->where('kyc_status', 'verified')->count(),
            'pending' => DB::table('users')->where('kyc_status', 'pending')->count(),
            'rejected' => DB::table('users')->where('kyc_status', 'rejected')->count(),
            'not_submitted' => DB::table('users')->whereNull('kyc_status')->orWhere('kyc_status', '')->count(),
        ];

        return view('admin.reports.index', compact(
            'period', 'totalRevenue', 'totalPayouts', 'totalCommissions', 'totalWithdrawals', 'netRevenue',
            'periodDeposits', 'periodWithdrawals', 'periodUsers', 'periodInvestments',
            'revenueChart', 'userGrowthChart', 'flowChart', 'categoryBreakdown',
            'topInvestors', 'topEarners', 'packagePerformance', 'recentTransactions',
            'userStatusBreakdown', 'kycStats'
        ));
    }

    /**
     * Detailed transaction report.
     */
    public function transactions(Request $request)
    {
        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $search = $request->get('search');

        $query = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
            ->orderByDesc('transactions.created_at');

        if ($type !== 'all') {
            $query->where('transactions.type', $type);
        }
        if ($status !== 'all') {
            $query->where('transactions.status', $status);
        }
        if ($startDate) {
            $query->whereDate('transactions.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transactions.created_at', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('transactions.reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(25);

        // Summary
        $summary = [
            'total' => (clone $query)->count(),
            'volume' => (clone $query)->sum('transactions.amount'),
            'credits' => (clone $query)->where('direction', 'credit')->sum('transactions.amount'),
            'debits' => (clone $query)->where('direction', 'debit')->sum('transactions.amount'),
        ];

        return view('admin.reports.transactions', compact('transactions', 'summary', 'type', 'status', 'startDate', 'endDate', 'search'));
    }

    /**
     * User activity report.
     */
    public function users(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $query = DB::table('users')
            ->where('is_admin', false)
            ->leftJoin('investments', 'users.id', '=', 'investments.user_id')
            ->leftJoin('deposits', function ($join) {
                $join->on('users.id', '=', 'deposits.user_id')
                     ->where('deposits.status', 'confirmed');
            })
            ->leftJoin('withdrawals', function ($join) {
                $join->on('users.id', '=', 'withdrawals.user_id')
                     ->where('withdrawals.status', 'completed');
            })
            ->select(
                'users.id', 'users.name', 'users.email', 'users.status', 'users.kyc_status',
                'users.total_invested', 'users.total_earned', 'users.total_withdrawn',
                'users.created_at',
                DB::raw('COUNT(DISTINCT investments.id) as investment_count'),
                DB::raw('COUNT(DISTINCT deposits.id) as deposit_count'),
                DB::raw('COUNT(DISTINCT withdrawals.id) as withdrawal_count'),
                DB::raw('COALESCE(SUM(DISTINCT deposits.amount), 0) as total_deposited'),
                DB::raw('COALESCE(SUM(DISTINCT withdrawals.amount), 0) as total_withdrawn_amt')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.status', 'users.kyc_status',
                      'users.total_invested', 'users.total_earned', 'users.total_withdrawn', 'users.created_at')
            ->orderByDesc('users.created_at');

        if ($status !== 'all') {
            $query->where('users.status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(25);

        return view('admin.reports.users', compact('users', 'status', 'search'));
    }

    /**
     * Export report as CSV.
     */
    public function export(Request $request)
    {
        $report = $request->get('report', 'transactions');
        $format = $request->get('format', 'csv');

        // Get data based on report type
        [$headers, $rows] = $this->getExportData($report);

        if ($format === 'pdf') {
            return $this->exportPdf(ucfirst($report) . ' Report', $headers, $rows);
        }

        if ($format === 'excel') {
            return $this->exportExcel($report, $headers, $rows);
        }

        // Default: CSV
        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $report . '-report-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    private function getExportData(string $report): array
    {
        switch ($report) {
            case 'transactions':
                $headers = ['Date', 'User', 'Email', 'Type', 'Direction', 'Amount', 'Reference', 'Status'];
                $data = DB::table('transactions')
                    ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                    ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
                    ->orderByDesc('transactions.created_at')
                    ->limit(1000)
                    ->get();
                $rows = $data->map(fn($r) => [
                    $r->created_at, $r->user_name, $r->user_email,
                    $r->type, $r->direction, $r->amount,
                    $r->reference, $r->status
                ])->toArray();
                break;

            case 'users':
                $headers = ['Name', 'Email', 'Status', 'KYC', 'Total Invested', 'Total Earned', 'Total Withdrawn', 'Joined'];
                $data = DB::table('users')->where('is_admin', false)->orderByDesc('created_at')->get();
                $rows = $data->map(fn($r) => [
                    $r->name, $r->email, $r->status, $r->kyc_status ?? 'N/A',
                    $r->total_invested ?? 0, $r->total_earned ?? 0, $r->total_withdrawn ?? 0,
                    $r->created_at
                ])->toArray();
                break;

            case 'deposits':
                $headers = ['Date', 'User', 'Email', 'Method', 'Amount', 'Fee', 'Net', 'Reference', 'Status'];
                $data = DB::table('deposits')
                    ->leftJoin('users', 'deposits.user_id', '=', 'users.id')
                    ->select('deposits.*', 'users.name as user_name', 'users.email as user_email')
                    ->orderByDesc('deposits.created_at')
                    ->get();
                $rows = $data->map(fn($r) => [
                    $r->created_at, $r->user_name, $r->user_email,
                    $r->payment_method ?? $r->method ?? '—', $r->amount,
                    $r->fee ?? 0, ($r->amount - ($r->fee ?? 0)),
                    $r->reference, $r->status
                ])->toArray();
                break;

            case 'withdrawals':
                $headers = ['Date', 'User', 'Email', 'Method', 'Amount', 'Fee', 'Net', 'Reference', 'Status'];
                $data = DB::table('withdrawals')
                    ->leftJoin('users', 'withdrawals.user_id', '=', 'users.id')
                    ->select('withdrawals.*', 'users.name as user_name', 'users.email as user_email')
                    ->orderByDesc('withdrawals.created_at')
                    ->get();
                $rows = $data->map(fn($r) => [
                    $r->created_at, $r->user_name, $r->user_email,
                    $r->withdrawal_method ?? $r->method ?? '—', $r->amount,
                    $r->fee ?? 0, ($r->amount - ($r->fee ?? 0)),
                    $r->reference, $r->status
                ])->toArray();
                break;

            case 'commissions':
                $headers = ['Date', 'User', 'Email', 'Type', 'Amount', 'Reference', 'Status'];
                $data = DB::table('transactions')
                    ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                    ->select('transactions.*', 'users.name as user_name', 'users.email as user_email')
                    ->whereIn('transactions.type', [
                        'referral_commission', 'direct_referral_bonus', 'matching_bonus',
                        'leadership_bonus', 'rank_promotion_bonus', 'profit_share'
                    ])
                    ->orderByDesc('transactions.created_at')
                    ->limit(1000)
                    ->get();
                $rows = $data->map(fn($r) => [
                    $r->created_at, $r->user_name, $r->user_email,
                    $r->type, $r->amount, $r->reference, $r->status
                ])->toArray();
                break;

            default:
                $headers = [];
                $rows = [];
        }

        return [$headers, $rows];
    }

    private function exportExcel(string $report, array $headers, array $rows)
    {
        $filename = $report . '-report-' . now()->format('Y-m-d') . '.xls';
        $html = '<table border="1" style="border-collapse:collapse;font-family:Arial;font-size:11px;">';
        $html .= '<tr style="background:#6366f1;color:#fff;font-weight:bold;">';
        foreach ($headers as $h) {
            $html .= '<th style="padding:6px 10px;">' . e($h) . '</th>';
        }
        $html .= '</tr>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td style="padding:5px 10px;">' . e((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function exportPdf(string $title, array $headers, array $rows)
    {
        return view('admin.reports.print', [
            'title'   => $title,
            'headers' => $headers,
            'rows'    => $rows,
            'date'    => now()->format('M d, Y H:i'),
        ]);
    }

    private function getRevenueChart($days)
    {
        $data = DB::table('transactions')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, type, SUM(amount) as total')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $labels = [];
        $depositData = [];
        $payoutData = [];
        $commissionData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $depositData[] = (float) ($data->where('date', $date)->where('type', 'deposit')->sum('total'));
            $payoutData[] = (float) ($data->where('date', $date)->whereIn('type', ['profit_share', 'withdrawal'])->sum('total'));
            $commissionData[] = (float) ($data->where('date', $date)->whereIn('type', ['referral_commission', 'matching_bonus'])->sum('total'));
        }

        return ['labels' => $labels, 'deposits' => $depositData, 'payouts' => $payoutData, 'commissions' => $commissionData];
    }

    private function getUserGrowthChart($days)
    {
        $data = DB::table('users')
            ->where('is_admin', false)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $newUsers = [];
        $cumulative = 0;
        $cumulativeData = [];

        $baseCount = DB::table('users')
            ->where('is_admin', false)
            ->where('created_at', '<', now()->subDays($days))
            ->count();

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $daily = $data->get($date)?->count ?? 0;
            $newUsers[] = $daily;
            $cumulative += $daily;
            $cumulativeData[] = $baseCount + $cumulative;
        }

        return ['labels' => $labels, 'new' => $newUsers, 'cumulative' => $cumulativeData];
    }

    private function getFlowChart($days)
    {
        $deposits = DB::table('deposits')
            ->where('status', 'confirmed')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $withdrawals = DB::table('withdrawals')
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $depositData = [];
        $withdrawalData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $depositData[] = (float) ($deposits->get($date)?->total ?? 0);
            $withdrawalData[] = (float) ($withdrawals->get($date)?->total ?? 0);
        }

        return ['labels' => $labels, 'deposits' => $depositData, 'withdrawals' => $withdrawalData];
    }

    private function getCategoryBreakdown()
    {
        return DB::table('investments')
            ->leftJoin('investment_packages', 'investments.package_id', '=', 'investment_packages.id')
            ->select('investment_packages.category', DB::raw('SUM(investments.amount) as total'))
            ->where('investments.status', 'active')
            ->groupBy('investment_packages.category')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->category => (float) $item->total])
            ->toArray();
    }
}
