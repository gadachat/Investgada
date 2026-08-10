<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Profit distributions table
        if (!Schema::hasTable('profit_distributions')) {
            Schema::create('profit_distributions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('investment_id')->nullable()->constrained()->nullOnDelete();
                $table->string('cycle_id', 30);
                $table->decimal('amount', 18, 2);
                $table->decimal('pool_amount', 18, 2);
                $table->decimal('weighted_capital', 18, 2)->default(0);
                $table->decimal('total_weighted_capital', 18, 2)->default(0);
                $table->decimal('share_percentage', 10, 4)->default(0);
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
                $table->text('note')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'status']);
                $table->index('cycle_id');
            });
        }

        // 2. Crypto tickers table (for live market data on landing page)
        if (!Schema::hasTable('crypto_tickers')) {
            Schema::create('crypto_tickers', function (Blueprint $table) {
                $table->id();
                $table->string('symbol', 20)->unique();
                $table->string('name', 50);
                $table->decimal('price', 18, 8);
                $table->decimal('change_24h', 10, 4)->default(0);
                $table->decimal('volume_24h', 18, 2)->nullable();
                $table->string('image_url')->nullable();
                $table->timestamps();
            });
        }

        // 3. Notification templates table (for admin notification management)
        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('title', 200);
                $table->text('message');
                $table->string('type', 50)->default('general');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('crypto_tickers');
        Schema::dropIfExists('profit_distributions');
    }
};
