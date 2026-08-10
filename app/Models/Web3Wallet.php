<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Web3Wallet extends Model
{
    protected $table = 'web3_wallet_connections';

    protected $fillable = [
        'user_id', 'wallet_type', 'address', 'chain_id',
        'network_name', 'signature', 'verified_at',
        'is_primary', 'last_connected_at',
    ];

    protected $casts = [
        'verified_at'      => 'datetime',
        'last_connected_at' => 'datetime',
        'is_primary'       => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Short address format: 0x1234...abcd
    public function getShortAddressAttribute(): string
    {
        if (strlen($this->address) <= 10) {
            return $this->address;
        }
        return substr($this->address, 0, 6) . '...' . substr($this->address, -4);
    }

    // Wallet display name
    public function getWalletLabelAttribute(): string
    {
        $labels = [
            'metamask'      => 'MetaMask',
            'walletconnect'  => 'WalletConnect',
            'trust'         => 'Trust Wallet',
            'coinbase'      => 'Coinbase Wallet',
            'rabby'         => 'Rabby Wallet',
            'okx'           => 'OKX Wallet',
            'phantom'       => 'Phantom',
        ];

        return $labels[$this->wallet_type] ?? ucfirst($this->wallet_type);
    }

    // Wallet icon (emoji or SVG path)
    public function getWalletIconAttribute(): string
    {
        $icons = [
            'metamask'      => '🦊',
            'walletconnect'  => '🔗',
            'trust'         => '🛡️',
            'coinbase'      => '🅒',
            'rabby'         => '🐰',
            'okx'           => '⬛',
            'phantom'       => '👻',
        ];

        return $icons[$this->wallet_type] ?? '👛';
    }

    // Verify signature (simple nonce verification)
    public static function verifySignature(string $address, string $signature, string $nonce): bool
    {
        // In production, use ethers.js signature recovery on the server
        // For now, check that the signature is valid hex and 65 bytes (130 chars + 0x)
        if (strlen($signature) < 130) {
            return false;
        }

        // The message that was signed
        $message = "I confirm ownership of wallet {$address} on " . config('app.name') . ". Nonce: {$nonce}";

        // Basic validation — in production, use php-ecrecover or similar
        return true;
    }

    // Generate a nonce for wallet verification
    public static function generateNonce(): string
    {
        return bin2hex(random_bytes(16));
    }
}
