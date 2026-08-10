<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_traders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->decimal('win_rate', 5, 2)->default(0);
            $table->integer('total_trades')->default(0);
            $table->integer('winning_trades')->default(0);
            $table->integer('followers_count')->default(0);
            $table->integer('max_followers')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('copy_trading_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('master_trader_id')->constrained('master_traders')->onDelete('cascade');
            $table->decimal('allocation_amount', 18, 2);
            $table->decimal('allocation_percent', 5, 2)->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('stopped_at')->nullable();
            $table->integer('total_copied')->default(0);
            $table->decimal('total_pnl', 18, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'master_trader_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copy_trading_subscriptions');
        Schema::dropIfExists('master_traders');
    }
};
