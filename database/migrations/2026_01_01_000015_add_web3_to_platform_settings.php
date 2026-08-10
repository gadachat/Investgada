<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Insert default Web3 settings
        \Illuminate\Support\Facades\DB::table('platform_settings')->insertOrIgnore([
            ['group' => 'web3', 'key' => 'web3_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable Web3 wallet connections'],
            ['group' => 'web3', 'key' => 'web3_networks', 'value' => json_encode([
                ['chain_id' => 1, 'name' => 'Ethereum', 'symbol' => 'ETH', 'rpc' => 'https://mainnet.infura.io/v3/'],
                ['chain_id' => 56, 'name' => 'BNB Smart Chain', 'symbol' => 'BNB', 'rpc' => 'https://bsc-dataseed.binance.org'],
                ['chain_id' => 137, 'name' => 'Polygon', 'symbol' => 'MATIC', 'rpc' => 'https://polygon-rpc.com'],
                ['chain_id' => 42161, 'name' => 'Arbitrum One', 'symbol' => 'ETH', 'rpc' => 'https://arb1.arbitrum.io/rpc'],
                ['chain_id' => 10, 'name' => 'Optimism', 'symbol' => 'ETH', 'rpc' => 'https://mainnet.optimism.io'],
                ['chain_id' => 8453, 'name' => 'Base', 'symbol' => 'ETH', 'rpc' => 'https://mainnet.base.org'],
            ]), 'type' => 'json', 'description' => 'Supported Web3 networks'],
            ['group' => 'web3', 'key' => 'web3_wallets', 'value' => json_encode([
                'metamask', 'walletconnect', 'trust', 'coinbase', 'rabby', 'okx', 'phantom'
            ]), 'type' => 'json', 'description' => 'Supported wallet providers'],
            ['group' => 'web3', 'key' => 'web3_project_id', 'value' => '', 'type' => 'string', 'description' => 'WalletConnect Project ID'],
            ['group' => 'web3', 'key' => 'web3_require_signature', 'value' => 'true', 'type' => 'boolean', 'description' => 'Require signature verification on connect'],
        ]);
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('platform_settings')
            ->where('group', 'web3')
            ->delete();
    }
};
