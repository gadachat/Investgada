<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Transaction;
use App\Models\Investment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InvoiceController extends Controller
{
    /**
     * Generate a deposit receipt as downloadable HTML (PDF-ready).
     */
    public function depositReceipt(Deposit $deposit)
    {
        if ($deposit->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $html = $this->buildReceipt($deposit, 'Deposit Receipt');

        return $this->returnPdf($html, "deposit-{$deposit->id}.html");
    }

    /**
     * Generate a withdrawal receipt.
     */
    public function withdrawalReceipt(Withdrawal $withdrawal)
    {
        if ($withdrawal->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $html = $this->buildReceipt($withdrawal, 'Withdrawal Receipt');

        return $this->returnPdf($html, "withdrawal-{$withdrawal->id}.html");
    }

    /**
     * Generate a monthly account statement.
     */
    public function accountStatement(Request $request)
    {
        $user = auth()->user();
        $month = $request->query('month', now()->format('Y-m'));
        $startDate = now()->parse($month . '-01')->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        $deposits = Deposit::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $investments = Investment::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('package')
            ->orderBy('created_at')
            ->get();

        $totalDeposits = $deposits->where('status', 'confirmed')->sum('amount');
        $totalWithdrawals = $withdrawals->where('status', 'completed')->sum('amount');
        $totalCredits = $transactions->where('direction', 'credit')->sum('amount');
        $totalDebits = $transactions->where('direction', 'debit')->sum('amount');

        $html = view('invoices.statement', compact(
            'user', 'month', 'startDate', 'endDate',
            'deposits', 'withdrawals', 'transactions', 'investments',
            'totalDeposits', 'totalWithdrawals', 'totalCredits', 'totalDebits'
        ))->render();

        return $this->returnPdf($html, "statement-{$month}.html");
    }

    /**
     * Generate an investment receipt.
     */
    public function investmentReceipt(Investment $investment)
    {
        if ($investment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $html = $this->buildInvestmentReceipt($investment);

        return $this->returnPdf($html, "investment-{$investment->id}.html");
    }

    // ── Helpers ──

    private function buildReceipt($record, string $title): string
    {
        $user = auth()->user();
        $reference = $record->reference ?? $record->id;
        $amount = $record->amount ?? 0;
        $status = $record->status ?? 'pending';
        $date = $record->created_at?->format('M d, Y H:i') ?? now()->format('M d, Y H:i');
        $method = $record->method ?? 'N/A';

        return view('invoices.receipt', [
            'title' => $title,
            'user' => $user,
            'reference' => $reference,
            'amount' => $amount,
            'status' => $status,
            'date' => $date,
            'method' => $method,
            'record' => $record,
        ])->render();
    }

    private function buildInvestmentReceipt(Investment $investment): string
    {
        $user = auth()->user();

        return view('invoices.investment', [
            'user' => $user,
            'investment' => $investment,
            'package' => $investment->package,
        ])->render();
    }

    private function returnPdf(string $html, string $filename): \Illuminate\Http\Response
    {
        // Return as downloadable HTML (can be printed to PDF by the browser)
        return Response::make($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
