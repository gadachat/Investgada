<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User investments in packages
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('investment_packages')->cascadeOnDelete();

            $table->decimal('amount', 18, 2);
            $table->decimal('expected_return', 18, 2)->default(0);
            $table->decimal('earned_so_far', 18, 2)->default(0);

            $table->enum('status', ['pending', 'active', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('matures_at')->nullable();
            $table->timestamp('last_payout_at')->nullable();
            $table->timestamp('next_payout_at')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['status', 'next_payout_at']);
        });

        // Each payout cycle for an investment
        Schema::create('investment_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 18, 8);
            $table->integer('cycle_number');
            $table->timestamp('payout_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_payouts');
        Schema::dropIfExists('investments');
    }
};
