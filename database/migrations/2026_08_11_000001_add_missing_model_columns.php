<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Users — 2FA and referral code columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('referred_by_code')->nullable()->after('sponsor_id');
            $table->boolean('two_factor_enabled')->default(false)->after('referred_by_code');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_verified_at')->nullable()->after('two_factor_secret');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_verified_at');
        });

        // 2. Master Traders — extended profile fields
        Schema::table('master_traders', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('title');
            $table->string('strategy_type')->nullable()->after('avatar');
            $table->decimal('monthly_return', 8, 2)->nullable()->after('win_rate');
            $table->decimal('total_profit', 18, 8)->default(0)->after('monthly_return');
            $table->boolean('use_manual_win_rate')->default(false)->after('total_profit');
            $table->decimal('manual_win_rate', 8, 2)->nullable()->after('use_manual_win_rate');
            $table->boolean('use_manual_outcome')->default(false)->after('manual_win_rate');
            $table->decimal('daily_profit_pct', 8, 2)->nullable()->after('use_manual_outcome');
            $table->decimal('loss_rate_pct', 8, 2)->nullable()->after('daily_profit_pct');
            $table->integer('trades_per_day')->nullable()->after('loss_rate_pct');
            $table->decimal('profit_variance', 8, 2)->nullable()->after('trades_per_day');
        });

        // 3. Support Tickets — rating fields
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->integer('rating')->nullable()->after('closed_at');
            $table->text('rating_comment')->nullable()->after('rating');
            $table->timestamp('rated_at')->nullable()->after('rating_comment');
        });

        // 4. Copy Trading Subscriptions — payout tracking fields
        Schema::table('copy_trading_subscriptions', function (Blueprint $table) {
            $table->timestamp('last_payout_at')->nullable()->after('stopped_at');
            $table->decimal('last_payout_amount', 18, 8)->default(0)->after('last_payout_at');
            $table->integer('wins_count')->default(0)->after('last_payout_amount');
            $table->integer('losses_count')->default(0)->after('wins_count');
        });

        // 5. Activity Logs — polymorphic and admin fields
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('subject_type')->nullable()->after('admin_id');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            $table->json('properties')->nullable()->after('subject_id');

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropColumn(['properties', 'subject_id', 'subject_type', 'admin_id']);
        });

        Schema::table('copy_trading_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['losses_count', 'wins_count', 'last_payout_amount', 'last_payout_at']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['rated_at', 'rating_comment', 'rating']);
        });

        Schema::table('master_traders', function (Blueprint $table) {
            $table->dropColumn([
                'profit_variance', 'trades_per_day', 'loss_rate_pct', 'daily_profit_pct',
                'use_manual_outcome', 'manual_win_rate', 'use_manual_win_rate',
                'total_profit', 'monthly_return', 'strategy_type', 'avatar'
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_recovery_codes', 'two_factor_verified_at',
                'two_factor_secret', 'two_factor_enabled', 'referred_by_code'
            ]);
        });
    }
};
