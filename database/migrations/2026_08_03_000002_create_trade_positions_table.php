<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Trading positions — open and closed trades
        Schema::create('trade_positions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Market info
            $table->string('symbol', 20);       // BTC, ETH, EUR/USD, SPX, etc.
            $table->string('market_type', 10);  // crypto, forex, indices
            $table->string('direction', 5);     // buy (long) or sell (short)

            // Position details
            $table->decimal('entry_price', 18, 8);
            $table->decimal('volume', 18, 4);       // lot size / units
            $table->decimal('amount', 18, 2);       // USD invested (margin)
            $table->integer('leverage', false, true)->default(1); // 1x, 2x, 5x, 10x, 20x, 50x, 100x
            $table->decimal('contract_value', 18, 2); // amount * leverage

            // Risk management
            $table->decimal('take_profit', 18, 8)->nullable();
            $table->decimal('stop_loss', 18, 8)->nullable();

            // P&L tracking
            $table->decimal('current_price', 18, 8)->nullable();
            $table->decimal('pnl', 18, 2)->default(0);         // unrealized P&L
            $table->decimal('pnl_percent', 10, 2)->default(0); // % return on margin
            $table->decimal('fees', 18, 2)->default(0);        // spread + overnight fees

            // Status
            $table->enum('status', ['open', 'closed', 'liquidated', 'tp_hit', 'sl_hit'])->default('open');
            $table->decimal('close_price', 18, 8)->nullable();
            $table->decimal('close_pnl', 18, 2)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('close_reason', 20)->nullable(); // manual, tp, sl, liquidation

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('symbol');
        });

        // Admin-configurable trading settings
        Schema::create('trade_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $settings = [
            'trading_enabled'       => 'true',
            'max_leverage'          => '100',
            'min_trade_amount'      => '10',
            'max_trade_amount'      => '50000',
            'spread_percent'        => '0.05',  // 0.05% spread
            'overnight_fee_percent'=> '0.01',  // 0.01% per night
            'margin_call_percent'   => '50',    // warn at 50% margin
            'liquidation_percent'   => '20',    // liquidate at 20% margin
            'tp_sl_enabled'         => 'true',
            'allow_short_selling'   => 'true',
        ];

        foreach ($settings as $key => $value) {
            DB::table('trade_settings')->insert([
                'key'   => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_settings');
        Schema::dropIfExists('trade_positions');
    }
};
