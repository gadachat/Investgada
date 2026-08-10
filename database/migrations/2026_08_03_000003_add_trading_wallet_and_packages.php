<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add 'trading' to wallet types enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wallets MODIFY COLUMN type ENUM('deposit','interest','commission','bonus','withdrawal','trading') NOT NULL");
        } else {
            Schema::table('wallets', function (Blueprint $table) {
                $table->enum('type', ['deposit','interest','commission','bonus','withdrawal','trading'])->change();
            });
        }

        // 2. Trading packages — tiers that determine pairs, scanner, and earning rates
        Schema::create('trading_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->text('description')->nullable();

            // Price range for this tier
            $table->decimal('min_amount', 18, 2);
            $table->decimal('max_amount', 18, 2);

            // What the user gets
            $table->integer('max_pairs')->default(1)->comment('How many trading pairs they can access');
            $table->boolean('scanner_enabled')->default(false)->comment('Access to scanner for more pairs');
            $table->boolean('has_short_selling')->default(false);

            // Admin-set variable interest (profit rate the user actually earns)
            $table->decimal('daily_profit_percent', 8, 4)->default(1.0000)->comment('Daily profit rate on trading balance');
            $table->decimal('win_rate_percent', 8, 4)->default(65.0000)->comment('Simulated win rate for realistic feel');
            $table->decimal('loss_rate_percent', 8, 4)->default(35.0000)->comment('Simulated loss rate');

            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Trading subscriptions — when a user subscribes to a package
        Schema::create('trading_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trading_package_id')->constrained('trading_packages')->cascadeOnDelete();

            // Amount transferred to trading wallet for this subscription
            $table->decimal('amount', 18, 2);

            // Which pairs the user selected (within their package limit)
            $table->json('selected_pairs')->nullable()->comment('Array of symbol strings the user chose');

            // Scanner usage
            $table->boolean('scanner_active')->default(false);

            // Profit tracking
            $table->decimal('total_profit', 18, 2)->default(0);
            $table->decimal('total_loss', 18, 2)->default(0);
            $table->integer('total_trades')->default(0);
            $table->integer('total_wins')->default(0);
            $table->integer('total_losses')->default(0);

            // Status
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // 4. Seed default trading packages
        $packages = [
            [
                'name'               => 'Starter',
                'slug'               => 'starter',
                'description'        => 'Basic trading access. Choose 1 pair. No scanner. Limited daily earning.',
                'min_amount' => 0,
                'max_amount' => 0,
                'max_pairs'          => 1,
                'scanner_enabled'    => false,
                'has_short_selling'  => false,
                'daily_profit_percent' => 0,
                'win_rate_percent' => 0,
                'loss_rate_percent' => 0,
                'is_active'          => true,
                'sort_order'         => 1,
            ],
            [
                'name'               => 'Premium',
                'slug'               => 'premium',
                'description'        => 'Full scanner access. Trade up to 5 pairs simultaneously. Higher earning rate with short selling.',
                'min_amount' => 0,
                'max_amount' => 0,
                'max_pairs'          => 5,
                'scanner_enabled'    => true,
                'has_short_selling'  => true,
                'daily_profit_percent' => 0,
                'win_rate_percent' => 0,
                'loss_rate_percent' => 0,
                'is_active'          => true,
                'sort_order'         => 2,
            ],
            [
                'name'               => 'VIP Trader',
                'slug'               => 'vip',
                'description'        => 'Unlimited pairs. Advanced scanner. Maximum earning rate. Priority features.',
                'min_amount' => 0,
                'max_amount' => 0,
                'max_pairs'          => 99,
                'scanner_enabled'    => true,
                'has_short_selling'  => true,
                'daily_profit_percent' => 0,
                'win_rate_percent' => 0,
                'loss_rate_percent' => 0,
                'is_active'          => true,
                'sort_order'         => 3,
            ],
        ];

        foreach ($packages as $pkg) {
            DB::table('trading_packages')->insert(array_merge($pkg, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 5. Add trading-specific settings to trade_settings
        $newSettings = [
            'trade_wallet_transfer_enabled' => 'true',
            'auto_close_on_profit_target'   => 'true',
            'profit_target_multiplier'       => '0',  // User earns up to 2x their subscription
            'min_trade_duration_seconds'    => '0',
            'max_trade_duration_seconds'     => '0',
        ];

        foreach ($newSettings as $key => $value) {
            DB::table('trade_settings')->insertOrIgnore([
                'key'   => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_subscriptions');
        Schema::dropIfExists('trading_packages');
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wallets MODIFY COLUMN type ENUM('deposit','interest','commission','bonus','withdrawal') NOT NULL");
        } else {
            Schema::table('wallets', function (Blueprint $table) {
                $table->enum('type', ['deposit','interest','commission','bonus','withdrawal'])->change();
            });
        }
    }
};
