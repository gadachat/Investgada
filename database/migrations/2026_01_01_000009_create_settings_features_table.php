<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feature ON/OFF manager — controls which modules are enabled
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique()->comment('module identifier: crypto, forex, kyc, etc.');
            $table->string('label', 100);
            $table->boolean('is_enabled')->default(true);
            $table->text('description')->nullable();
            $table->json('config')->nullable()->comment('module-specific settings');
            $table->timestamps();
        });

        // General platform settings (key-value store)
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string')->comment('string, boolean, integer, json');
            $table->string('group', 50)->default('general')->comment('general, payment, investment, mlm, email');
            $table->timestamps();
        });

        // Crypto deposit addresses (per network)
        Schema::create('deposit_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('network', 20)->comment('TRC20, ERC20, BEP20, BTC, etc.');
            $table->string('coin', 10)->comment('USDT, BTC, ETH, etc.');
            $table->string('address');
            $table->string('qr_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['network', 'is_active']);
        });

        // Rank rewards history
        Schema::create('rank_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rank_id')->constrained()->cascadeOnDelete();
            $table->decimal('reward_amount', 18, 2);
            $table->enum('type', ['salary', 'bonus', 'gift'])->default('bonus');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        // Profit sharing pools
        Schema::create('profit_shares', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('pool_type', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->decimal('total_pool_amount', 18, 2);
            $table->decimal('distributed_amount', 18, 2)->default(0);
            $table->integer('eligible_users')->default(0);
            $table->timestamp('distribution_at');
            $table->enum('status', ['pending', 'distributed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_shares');
        Schema::dropIfExists('rank_rewards');
        Schema::dropIfExists('deposit_addresses');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('feature_settings');
    }
};
