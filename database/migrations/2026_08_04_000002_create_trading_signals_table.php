<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_signals', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 30);
            $table->enum('direction', ['buy', 'sell']);
            $table->decimal('entry_price', 18, 8);
            $table->decimal('stop_loss', 18, 8);
            $table->decimal('take_profit', 18, 8);
            $table->decimal('take_profit_2', 18, 8)->nullable();
            $table->enum('category', ['crypto', 'forex', 'indices'])->default('crypto');
            $table->string('timeframe', 10)->default('1h');
            $table->integer('confidence')->default(0);
            $table->text('analysis')->nullable();
            $table->enum('status', ['active', 'closed', 'expired'])->default('active');
            $table->enum('result', ['win', 'loss', 'breakeven'])->nullable();
            $table->decimal('close_price', 18, 8)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_signals');
    }
};
