<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add per-master-trader outcome controls
        Schema::table('master_traders', function (Blueprint $table) {
            $table->decimal('daily_profit_pct', 5, 2)->default(2.50)->after('monthly_return');
            $table->decimal('loss_rate_pct', 5, 2)->default(5.00)->after('daily_profit_pct');
            $table->integer('trades_per_day')->default(6)->after('loss_rate_pct');
            $table->decimal('profit_variance', 5, 2)->default(15.00)->after('trades_per_day');
            $table->boolean('use_manual_outcome')->default(true)->after('profit_variance');
        });

        // Add tracking fields to copy trading subscriptions
        Schema::table('copy_trading_subscriptions', function (Blueprint $table) {
            $table->timestamp('last_payout_at')->nullable()->after('started_at');
            $table->decimal('last_payout_amount', 18, 2)->default(0)->after('last_payout_at');
            $table->integer('wins_count')->default(0)->after('total_copied');
            $table->integer('losses_count')->default(0)->after('wins_count');
        });
    }

    public function down(): void
    {
        Schema::table('master_traders', function (Blueprint $table) {
            $table->dropColumn([
                'daily_profit_pct', 'loss_rate_pct', 'trades_per_day',
                'profit_variance', 'use_manual_outcome'
            ]);
        });

        Schema::table('copy_trading_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'last_payout_at', 'last_payout_amount', 'wins_count', 'losses_count'
            ]);
        });
    }
};
