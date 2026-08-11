<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use App\Models\Withdrawal;
use App\Services\FundService;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\PlatformSetting;
use App\Models\FeatureSetting;
use App\Models\Web3Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function create()
    {
        if (!FeatureSetting::isEnabled('withdrawal')) {
            return redirect()->route('dashboard.index')->with('error', 'Withdrawals are currently disabled.');
        }

        $user = auth()->user();

        // KYC check
        if ($user->kyc_status !== 'verified') {
            return redirect()->route('dashboard.index')
                ->with('error', 'Please complete KYC verification before requesting a withdrawal.');
        }

        $withdrawalWallet = $user->wallet('withdrawal');
        $available = $withdrawalWallet ? $withdrawalWallet->balance : 0;

        $minWithdrawal = (float) PlatformSetting::get('min_withdrawal', 0);
        $maxWithdrawal = (float) PlatformSetting::get('max_withdrawal', 0);
        $feePercent = (float) PlatformSetting::get('withdrawal_fee_percent', 0);
        $processingHours = (int) PlatformSetting::get('withdrawal_processing_hours', 0);

        $recentWithdrawals = Withdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $otherWallets = $user->wallets()
            ->where('type', '!=', 'withdrawal')
            ->where('balance', '>', 0)
            ->get();

        // Get connected Web3 wallets for withdrawal destination
        $web3Wallets = $user->web3Wallets()->orderBy('is_primary', 'desc')->get();
        $web3Enabled = PlatformSetting::get('web3_enabled', 'true') === 'true';

        // Special fund account summary
        $fundSummary = FundService::getWithdrawalSummary($user->id);

        return view('dashboard.withdrawal.create', compact(
            'available', 'minWithdrawal', 'maxWithdrawal', 'feePercent',
            'processingHours', 'recentWithdrawals', 'otherWallets', 'withdrawalWallet',
            'web3Wallets', 'web3Enabled', 'fundSummary'
        ));
    }

    public function store(Request $request)
    {
        if (!FeatureSetting::isEnabled('withdrawal')) {
            return back()->with('error', 'Withdrawals are currently disabled.');
        }

        $user = auth()->user();

        if ($user->kyc_status !== 'verified') {
            return back()->with('error', 'KYC verification required.');
        }

        // ── Fund recipient withdrawal guard ──
        // Special fund accounts can only withdraw commissions until team target is met
        $fundSummary = FundService::getWithdrawalSummary($user->id);
        if ($fundSummary['is_fund_recipient'] && !$fundSummary['target_met']) {
            // Target not met — can only withdraw commission-sourced funds
            $commissionAvailable = $fundSummary['commission_available'];

            if ($commissionAvailable <= 0) {
                return back()->with('error', $fundSummary['reason']
                    ?? 'Your account is a special fund account. You can only withdraw commissions until your team reaches 100% of the fund target.');
            }

            // Check if the withdrawal amount exceeds commission-sourced balance
            if ($request->amount > $commissionAvailable) {
                return back()->with('error',
                    'This is a special fund account. You can only withdraw up to $'
                    . number_format($commissionAvailable, 2)
                    . ' (commission-sourced funds). Profit and capital withdrawals are locked until your team reaches 100% of the fund target. Current progress: '
                    . $fundSummary['progress'] . '%'
                );
            }
        }

        $minWithdrawal = (float) PlatformSetting::get('min_withdrawal', 0);
        $maxWithdrawal = (float) PlatformSetting::get('max_withdrawal', 0);
        $feePercent = (float) PlatformSetting::get('withdrawal_fee_percent', 0);

        $request->validate([
            'method'             => 'required|in:crypto,bank_transfer,manual,wallet',
            'amount'             => "required|numeric|min:{$minWithdrawal}|max:{$maxWithdrawal}",
            'wallet_address'     => 'nullable|string|max:200',
            'network'            => 'nullable|string|max:20',
            'web3_wallet_id'     => 'nullable|integer|exists:web3_wallet_connections,id',
            'bank_account_name'  => 'nullable|required_if:method,bank_transfer|string|max:200',
            'bank_account_number'=> 'nullable|required_if:method,bank_transfer|string|max:50',
            'bank_name'          => 'nullable|required_if:method,bank_transfer|string|max:200',
            'bank_country'       => 'nullable|string|max:80',
        ]);

        // Resolve withdrawal destination
        $walletAddress = $request->wallet_address;
        $network = $request->network;

        if ($request->method === 'wallet' && $request->web3_wallet_id) {
            $web3Wallet = Web3Wallet::where('id', $request->web3_wallet_id)
                ->where('user_id', $user->id)
                ->first();
            if ($web3Wallet) {
                $walletAddress = $web3Wallet->address;
                $network = $web3Wallet->network_name ?? $network;
            } else {
                return back()->with('error', 'Selected Web3 wallet not found.');
            }
        } elseif ($request->method === 'crypto' && !$walletAddress) {
            return back()->with('error', 'Please enter a destination wallet address.');
        }

        $method = $request->method === 'wallet' ? 'crypto' : $request->method;
        $fee = round($request->amount * ($feePercent / 100), 2);
        $netAmount = $request->amount - $fee;

        DB::transaction(function () use ($user, $request, $fee, $netAmount, $walletAddress, $network, $method) {
            // Lock wallet row for safe balance check
            $wallet = Wallet::where('user_id', $user->id)
                ->where('type', 'withdrawal')
                ->lockForUpdate()
                ->first();

            if (!$wallet || $wallet->balance < $request->amount) {
                throw new \Exception('Insufficient balance in withdrawal wallet. Available: $' . number_format($wallet?->balance ?? 0, 2));
            }

            // Lock the funds
            $wallet->lock($request->amount);

            $withdrawal = Withdrawal::create([
                'reference'          => 'WDR-' . strtoupper(Str::random(12)),
                'user_id'            => $user->id,
                'method'             => $method,
                'currency'           => 'USD',
                'amount'             => $request->amount,
                'fee'                => $fee,
                'net_amount'         => $netAmount,
                'wallet_address'     => $walletAddress,
                'network'            => $network,
                'bank_account_name'  => $request->bank_account_name,
                'bank_account_number'=> $request->bank_account_number,
                'bank_name'          => $request->bank_name,
                'bank_country'       => $request->bank_country,
                'status'             => 'pending',
            ]);

            // Record transaction
            Transaction::create([
                'reference'     => 'TXN-' . strtoupper(Str::random(12)),
                'user_id'       => $user->id,
                'wallet_id'     => $wallet->id,
                'type'          => 'withdrawal',
                'direction'     => 'debit',
                'amount'        => $request->amount,
                'balance_after' => $wallet->fresh()->balance,
                'currency'      => 'USD',
                'description'   => 'Withdrawal request — ' . $method . ' (' . $withdrawal->reference . ')',
                'metadata'      => ['withdrawal_id' => $withdrawal->id],
                'status'        => 'pending',
            ]);
        });

        // Send notification
        NotifyService::withdrawalRequested($user, $request->amount, $method);

        return redirect()->route('dashboard.withdrawal.create')->with('success', 'Withdrawal request of $' . number_format($request->amount, 2) . ' submitted. You will receive $' . number_format($netAmount, 2) . ' after fees. Processing time: ' . PlatformSetting::get('withdrawal_processing_hours', 0) . ' hours.');
    }
}
