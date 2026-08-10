<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Auto-trade sessions — a user starts a session, allocates capital, picks pairs
        Schema::create('auto_trade_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->decimal('allocated_capital', 18, 2)->comment('Amount taken from deposit wallet to trade with');
            $table->decimal('current_balance', 18, 2)->comment('Running balance of the session');
            $table->decimal('total_profit', 18, 2)->default(0);
            $table->decimal('total_loss', 18, 2)->default(0);
            $table->decimal('total_trades', 18, 2)->default(0);
            $table->decimal('winning_trades', 18, 2)->default(0);
            $table->decimal('losing_trades', 18, 2)->default(0);

            $table->json('selected_pairs')->comment('Array of trading pair symbols');
            $table->enum('status', ['active', 'stopped', 'completed'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('last_trade_at')->nullable();
            $table->timestamp('next_trade_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        // Individual auto-generated trades
        Schema::create('auto_trades', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('auto_trade_sessions')->cascadeOnDelete();

            $table->string('pair', 20)->comment('e.g. BTC/USDT, EUR/USD');
            $table->string('pair_name', 50);
            $table->string('category', 10)->comment('crypto, forex, indices');
            $table->enum('direction', ['buy', 'sell']);
            $table->decimal('entry_price', 18, 8);
            $table->decimal('exit_price', 18, 8)->nullable();
            $table->decimal('amount', 18, 2)->comment('Trade amount in USD');
            $table->decimal('profit', 18, 2)->default(0)->comment('Net profit/loss');
            $table->decimal('profit_pct', 8, 4)->default(0);

            $table->enum('status', ['open', 'closed', 'pending'])->default('pending');
            $table->boolean('is_win')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['session_id', 'status']);
        });

        // Seed admin-configurable auto-trade settings into platform_settings
        $settings = [
            'autotrade_enabled'           => '1',
            'autotrade_daily_profit_pct'  => '2.5',
            'autotrade_win_rate'          => '75',
            'autotrade_min_capital'       => '50',
            'autotrade_max_capital'       => '50000',
            'autotrade_trades_per_day'    => '8',
            'autotrade_trade_interval_min'=> '45',
            'autotrade_profit_variance'  => '20',
            'autotrade_max_concurrent'    => '5',
            'autotrade_pairs_crypto'      => json_encode(['BTC/USDT', 'ETH/USDT', 'BNB/USDT', 'SOL/USDT', 'XRP/USDT']),
            'autotrade_pairs_forex'       => json_encode(['EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD']),
            'autotrade_pairs_indices'     => json_encode(['SPX', 'NDX', 'DJI']),
            'autotrade_stop_loss_pct'     => '5',
            'autotrade_take_profit_pct'   => '3',
            'autotrade_auto_compound'    => '0',
        ];

        foreach ($settings as $key => $value) {
            DB::table('platform_settings')->insertOrIgnore([
                'key'   => $key,
                'value' => $value,
                'group' => 'autotrade',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add auto-trade feature flag
        DB::table('feature_settings')->insertOrIgnore([
            'key'         => 'auto_trading',
            'label'       => 'Auto Trading',
            'is_enabled'  => true,
            'description' => 'Enable/disable the auto-trading module for users.',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_trades');
        Schema::dropIfExists('auto_trade_sessions');

        DB::table('platform_settings')->where('group', 'autotrade')->delete();
        DB::table('feature_settings')->where('key', 'auto_trading')->delete();
    }
};
