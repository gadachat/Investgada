<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Deposit history with filters and export.
     */
    public function depositHistory(Request $request)
    {
        $user = $request->user();

        $query = Deposit::where('user_id', $user->id);

        // Filters
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->method && $request->method !== 'all') {
            $query->where('payment_method', $request->method);
        }
        if ($request->from_date) {
            $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->to_date) {
            $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
        }

        $deposits = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Summary stats
        $stats = [
            'total'      => Deposit::where('user_id', $user->id)->count(),
            'completed'   => Deposit::where('user_id', $user->id)->where('status', 'completed')->count(),
            'pending'     => Deposit::where('user_id', $user->id)->where('status', 'pending')->count(),
            'total_amount' => Deposit::where('user_id', $user->id)->where('status', 'completed')->sum('amount'),
        ];

        return view('dashboard.history.deposits', compact('deposits', 'stats'));
    }

    /**
     * Withdrawal history with filters and export.
     */
    public function withdrawalHistory(Request $request)
    {
        $user = $request->user();

        $query = Withdrawal::where('user_id', $user->id);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->method && $request->method !== 'all') {
            $query->where('withdrawal_method', $request->method);
        }
        if ($request->from_date) {
            $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->to_date) {
            $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
        }

        $withdrawals = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $stats = [
            'total'       => Withdrawal::where('user_id', $user->id)->count(),
            'completed'    => Withdrawal::where('user_id', $user->id)->where('status', 'completed')->count(),
            'pending'      => Withdrawal::where('user_id', $user->id)->where('status', 'pending')->count(),
            'total_amount' => Withdrawal::where('user_id', $user->id)->where('status', 'completed')->sum('amount'),
        ];

        return view('dashboard.history.withdrawals', compact('withdrawals', 'stats'));
    }

    /**
     * All commission history — direct referral, matching bonus, leadership, rank promotion, profit share.
     */
    public function commissionHistory(Request $request)
    {
        $user = $request->user();

        $commissionTypes = [
            'referral_commission',
            'direct_referral_bonus',
            'matching_bonus',
            'leadership_bonus',
            'rank_promotion_bonus',
            'profit_share',
        ];

        $query = Transaction::where('user_id', $user->id)
            ->whereIn('type', $commissionTypes)
            ->where('direction', 'credit');

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->from_date) {
            $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->to_date) {
            $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
        }

        $commissions = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Per-type breakdown
        $breakdown = [];
        foreach ($commissionTypes as $type) {
            $breakdown[$type] = [
                'count'  => Transaction::where('user_id', $user->id)->where('type', $type)->where('direction', 'credit')->count(),
                'total'  => (float) Transaction::where('user_id', $user->id)->where('type', $type)->where('direction', 'credit')->sum('amount'),
            ];
        }

        $totalEarned = array_sum(array_column($breakdown, 'total'));

        return view('dashboard.history.commissions', compact('commissions', 'breakdown', 'totalEarned'));
    }

    /**
     * Export deposit history to Excel (XLS) or print as PDF.
     */
    public function exportDeposits(Request $request)
    {
        $user = $request->user();
        $format = $request->get('format', 'excel');

        $query = Deposit::where('user_id', $user->id);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->from_date) {
            $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->to_date) {
            $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
        }

        $deposits = $query->orderByDesc('created_at')->get();

        $headers = ['Date', 'Reference', 'Method', 'Amount', 'Fee', 'Net Amount', 'Status', 'TX Hash'];

        $rows = $deposits->map(function ($d) {
            return [
                $d->created_at->format('Y-m-d H:i'),
                $d->reference,
                ucfirst($d->payment_method ?? '—'),
                number_format((float) $d->amount, 2),
                number_format((float) ($d->fee ?? 0), 2),
                number_format((float) $d->amount - (float) ($d->fee ?? 0), 2),
                ucfirst($d->status),
                $d->tx_hash ?? '—',
            ];
        })->toArray();

        if ($format === 'pdf') {
            return $this->printPdf('Deposit History', $headers, $rows, $user);
        }

        return $this->exportExcel('deposits', $headers, $rows, $user);
    }

    /**
     * Export withdrawal history to Excel or PDF.
     */
    public function exportWithdrawals(Request $request)
    {
        $user = $request->user();
        $format = $request->get('format', 'excel');

        $query = Withdrawal::where('user_id', $user->id);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->from_date) {
            $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->to_date) {
            $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
        }

        $withdrawals = $query->orderByDesc('created_at')->get();

        $headers = ['Date', 'Reference', 'Method', 'Amount', 'Fee', 'Net Amount', 'Status', 'Wallet Address'];

        $rows = $withdrawals->map(function ($w) {
            return [
                $w->created_at->format('Y-m-d H:i'),
                $w->reference,
                ucfirst($w->withdrawal_method ?? '—'),
                number_format((float) $w->amount, 2),
                number_format((float) ($w->fee ?? 0), 2),
                number_format((float) $w->amount - (float) ($w->fee ?? 0), 2),
                ucfirst($w->status),
                substr($w->wallet_address ?? '—', 0, 30),
            ];
        })->toArray();

        if ($format === 'pdf') {
            return $this->printPdf('Withdrawal History', $headers, $rows, $user);
        }

        return $this->exportExcel('withdrawals', $headers, $rows, $user);
    }

    /**
     * Export commission history to Excel or PDF.
     */
    public function exportCommissions(Request $request)
    {
        $user = $request->user();
        $format = $request->get('format', 'excel');

        $commissionTypes = [
            'referral_commission', 'direct_referral_bonus', 'matching_bonus',
            'leadership_bonus', 'rank_promotion_bonus', 'profit_share',
        ];

        $query = Transaction::where('user_id', $user->id)
            ->whereIn('type', $commissionTypes)
            ->where('direction', 'credit');

        if ($request->type && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->from_date) {
            $query->where('created_at', '>=', Carbon::parse($request->from_date)->startOfDay());
        }
        if ($request->to_date) {
            $query->where('created_at', '<=', Carbon::parse($request->to_date)->endOfDay());
        }

        $commissions = $query->orderByDesc('created_at')->get();

        $typeLabels = [
            'referral_commission'   => 'Direct Referral',
            'direct_referral_bonus'  => 'Direct Referral Bonus',
            'matching_bonus'         => 'Matching Bonus',
            'leadership_bonus'       => 'Leadership Bonus',
            'rank_promotion_bonus'   => 'Rank Promotion Bonus',
            'profit_share'           => 'Profit Share',
        ];

        $headers = ['Date', 'Type', 'Description', 'Amount', 'Balance After', 'Reference', 'Status'];

        $rows = $commissions->map(function ($c) use ($typeLabels) {
            return [
                $c->created_at->format('Y-m-d H:i'),
                $typeLabels[$c->type] ?? ucfirst(str_replace('_', ' ', $c->type)),
                $c->description ?? '—',
                number_format((float) $c->amount, 2),
                $c->balance_after ? number_format((float) $c->balance_after, 2) : '—',
                $c->reference,
                ucfirst($c->status),
            ];
        })->toArray();

        if ($format === 'pdf') {
            return $this->printPdf('Commission History', $headers, $rows, $user);
        }

        return $this->exportExcel('commissions', $headers, $rows, $user);
    }

    /**
     * Generate Excel (.xls) file — HTML table with Excel headers.
     */
    private function exportExcel(string $filename, array $headers, array $rows, $user)
    {
        $filename = $filename . '-' . now()->format('Y-m-d') . '.xls';

        $html = '<table border="1" style="border-collapse:collapse;font-family:Arial;font-size:11px;">';
        $html .= '<tr style="background:#6366f1;color:#fff;font-weight:bold;">';
        foreach ($headers as $h) {
            $html .= '<th style="padding:6px 10px;">' . htmlspecialchars($h) . '</th>';
        }
        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td style="padding:5px 10px;">' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Generate print-optimized PDF view — user can save as PDF via browser print.
     */
    private function printPdf(string $title, array $headers, array $rows, $user)
    {
        return view('dashboard.history.print', [
            'title'   => $title,
            'headers' => $headers,
            'rows'    => $rows,
            'user'    => $user,
            'date'    => now()->format('M d, Y H:i'),
        ]);
    }
}
