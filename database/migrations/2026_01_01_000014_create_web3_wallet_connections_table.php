<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web3_wallet_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('wallet_type', 30)->comment('metamask, walletconnect, trust, coinbase, rabby, etc.');
            $table->string('address', 42)->comment('Wallet address (0x...)');
            $table->string('chain_id', 20)->nullable()->comment('EVM chain ID: 1=ETH, 56=BSC, 137=MATIC, etc.');
            $table->string('network_name', 30)->nullable()->comment('Ethereum, BSC, Polygon, etc.');
            $table->string('signature', 130)->nullable()->comment('Ownership proof signature');
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'address']);
            $table->index('address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web3_wallet_connections');
    }
};
