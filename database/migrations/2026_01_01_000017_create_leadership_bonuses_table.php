<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leadership_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rank_id')->constrained('ranks')->cascadeOnDelete();
            
            // Pool distribution info
            $table->string('pool_name');
            $table->enum('pool_type', ['monthly'])->default('monthly');
            $table->decimal('total_pool_amount', 18, 2);
            $table->decimal('eligible_rank_count', 8, 0)->default(0);
            
            // User's share
            $table->decimal('user_share_percent', 5, 2)->default(0);
            $table->decimal('bonus_amount', 18, 2);
            
            // Qualification snapshot
            $table->decimal('team_volume', 18, 2)->default(0);
            $table->integer('direct_referrals')->default(0);
            $table->integer('total_downline')->default(0);
            
            // Payment status
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('cycle_id', 30)->nullable();
            $table->text('note')->nullable();
            
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('cycle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leadership_bonuses');
    }
};
