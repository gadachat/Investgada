<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->enum('category', ['crypto', 'forex', 'stocks', 'bonds', 'binary', 'mixed'])->default('mixed');
            $table->enum('type', ['fixed', 'variable', 'profit_share'])->default('fixed');

            // Investment range
            $table->decimal('min_amount', 18, 2)->default(0);
            $table->decimal('max_amount', 18, 2)->nullable();

            // Returns
            $table->decimal('return_rate', 5, 2)->default(0)->comment('percentage per cycle');
            $table->enum('return_type', ['daily', 'weekly', 'monthly', 'maturity'])->default('daily');
            $table->integer('duration_days')->default(30)->comment('total investment period');
            $table->integer('cycle_days')->default(1)->comment('how often returns are credited');
            $table->decimal('total_return_cap', 5, 2)->default(0)->comment('total % cap (e.g. 150 means 150% of principal)');

            // Features
            $table->boolean('principal_return')->default(true)->comment('return principal at end');
            $table->boolean('compounding')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);

            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_packages');
    }
};
