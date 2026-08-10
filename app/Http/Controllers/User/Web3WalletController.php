<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Web3Wallet;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Web3WalletController extends Controller
{
    /**
     * Get Web3 configuration for frontend.
     */
    public function config()
    {
        $enabled = PlatformSetting::get('web3_enabled', 'true') === 'true';
        $networks = json_decode(PlatformSetting::get('web3_networks', '[]'), true) ?: [];
        $wallets = json_decode(PlatformSetting::get('web3_wallets', '[]'), true) ?: [];
        $projectId = PlatformSetting::get('web3_project_id', '');
        $requireSig = PlatformSetting::get('web3_require_signature', 'true') === 'true';

        // Generate nonce for signature verification
        $nonce = $requireSig ? Web3Wallet::generateNonce() : null;
        if ($nonce) {
            session(['web3_nonce' => $nonce]);
        }

        return response()->json([
            'success'      => true,
            'enabled'      => $enabled,
            'networks'      => $networks,
            'wallets'       => $wallets,
            'project_id'    => $projectId,
            'require_sig'   => $requireSig,
            'nonce'         => $nonce,
            'connected'     => auth()->user()->web3Wallets()->orderBy('is_primary', 'desc')->get(),
        ]);
    }

    /**
     * Store a connected wallet after verification.
     */
    public function connect(Request $request)
    {
        $request->validate([
            'wallet_type' => ['required', 'string', 'in:metamask,walletconnect,trust,coinbase,rabby,okx,phantom'],
            'address'     => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'chain_id'    => ['nullable', 'string'],
            'network_name' => ['nullable', 'string'],
            'signature'   => ['nullable', 'string'],
        ]);

        // Check if Web3 is enabled
        if (PlatformSetting::get('web3_enabled', 'true') !== 'true') {
            return response()->json([
                'success' => false,
                'message' => 'Web3 wallet connection is not enabled.',
            ], 403);
        }

        $user = auth()->user();
        $address = strtolower($request->address);

        // Check if already connected by this user
        $existing = Web3Wallet::where('user_id', $user->id)
            ->where('address', $address)
            ->first();

        if ($existing) {
            // Update last connected
            $existing->update([
                'last_connected_at' => now(),
                'chain_id'          => $request->chain_id,
                'network_name'      => $request->network_name,
            ]);
            return response()->json([
                'success'  => true,
                'message'  => 'Wallet reconnected successfully.',
                'wallet'   => $existing,
            ]);
        }

        // Check if another user has this wallet
        $takenByOther = Web3Wallet::where('address', $address)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($takenByOther) {
            return response()->json([
                'success' => false,
                'message' => 'This wallet address is already linked to another account.',
            ], 422);
        }

        // Verify signature if required
        $verified = !$request->require_sig;
        if ($request->signature) {
            $nonce = session('web3_nonce');
            if ($nonce) {
                $verified = Web3Wallet::verifySignature($address, $request->signature, $nonce);
                session()->forget('web3_nonce');
            }
        }

        // Count user's existing wallets to set primary
        $walletCount = Web3Wallet::where('user_id', $user->id)->count();

        $wallet = Web3Wallet::create([
            'user_id'          => $user->id,
            'wallet_type'      => $request->wallet_type,
            'address'          => $address,
            'chain_id'         => $request->chain_id,
            'network_name'     => $request->network_name,
            'signature'        => $request->signature,
            'verified_at'      => $verified ? now() : null,
            'is_primary'       => $walletCount === 0,
            'last_connected_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet connected successfully!',
            'wallet'  => $wallet,
        ]);
    }

    /**
     * Disconnect a wallet.
     */
    public function disconnect(Request $request, Web3Wallet $wallet)
    {
        if ($wallet->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $wasPrimary = $wallet->is_primary;
        $wallet->delete();

        // If it was primary, make the next one primary
        if ($wasPrimary) {
            $next = Web3Wallet::where('user_id', auth()->id())->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Wallet disconnected.',
        ]);
    }

    /**
     * Set a wallet as primary.
     */
    public function setPrimary(Request $request, Web3Wallet $wallet)
    {
        if ($wallet->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Unset all others
        Web3Wallet::where('user_id', auth()->id())
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        $wallet->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary wallet updated.',
        ]);
    }
}
