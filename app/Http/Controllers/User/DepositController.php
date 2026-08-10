<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use App\Models\Deposit;
use App\Models\DepositAddress;
use App\Models\PlatformSetting;
use App\Models\FeatureSetting;
use App\Models\Web3Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepositController extends Controller
{
    public function create()
    {
        if (!FeatureSetting::isEnabled('deposit')) {
            return redirect()->route('dashboard.index')->with('error', 'Deposits are currently disabled.');
        }

        $user = auth()->user();
        $addresses = DepositAddress::where('is_active', true)->get()->groupBy('network');
        $minDeposit = (float) PlatformSetting::get('min_deposit', 50);
        $maxDeposit = (float) PlatformSetting::get('max_deposit', 100000);
        $feePercent = (float) PlatformSetting::get('deposit_fee_percent', 0);

        // Get user's connected Web3 wallets
        $web3Wallets = $user->web3Wallets()->orderBy('is_primary', 'desc')->get();
        $web3Enabled = PlatformSetting::get('web3_enabled', 'true') === 'true';

        $recentDeposits = Deposit::where('user_id', $user->id)
            ->orderBy('created_date', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.deposit.create', compact(
            'addresses', 'minDeposit', 'maxDeposit', 'feePercent', 'recentDeposits',
            'web3Wallets', 'web3Enabled'
        ));
    }

    public function store(Request $request)
    {
        if (!FeatureSetting::isEnabled('deposit')) {
            return back()->with('error', 'Deposits are currently disabled.');
        }

        $minDeposit = (float) PlatformSetting::get('min_deposit', 50);
        $maxDeposit = (float) PlatformSetting::get('max_deposit', 100000);
        $feePercent = (float) PlatformSetting::get('deposit_fee_percent', 0);

        $request->validate([
            'method'      => 'required|in:crypto,bank_transfer,manual,wallet',
            'amount'      => "required|numeric|min:{$minDeposit}|max:{$maxDeposit}",
            'network'     => 'nullable|string|max:20',
            'tx_hash'     => 'nullable|string|max:200',
            'from_address'=> 'nullable|string|max:200',
            'bank_reference' => 'nullable|string|max:200',
            'web3_wallet_id' => 'nullable|integer|exists:web3_wallet_connections,id',
        ]);

        // If Web3 wallet method, attach the wallet address
        $fromAddress = $request->from_address;
        if ($request->method === 'wallet' && $request->web3_wallet_id) {
            $wallet = Web3Wallet::where('id', $request->web3_wallet_id)
                ->where('user_id', auth()->id())
                ->first();
            if ($wallet) {
                $fromAddress = $wallet->address;
            }
        }

        $fee = round($request->amount * ($feePercent / 100), 2);
        $netAmount = $request->amount - $fee;

        Deposit::create([
            'reference'      => 'DEP-' . strtoupper(Str::random(12)),
            'user_id'        => auth()->id(),
            'method'         => $request->method === 'wallet' ? 'crypto' : $request->method,
            'currency'       => 'USD',
            'amount'         => $request->amount,
            'fee'            => $fee,
            'net_amount'     => $netAmount,
            'network'        => $request->network,
            'tx_hash'        => $request->tx_hash,
            'from_address'   => $fromAddress,
            'bank_reference' => $request->bank_reference,
            'status'         => 'pending',
        ]);

        return redirect()->route('dashboard.deposit.create')
            ->with('success', 'Deposit request submitted! Your deposit of $' . number_format($request->amount, 2) . ' is pending confirmation.');
    }
}
