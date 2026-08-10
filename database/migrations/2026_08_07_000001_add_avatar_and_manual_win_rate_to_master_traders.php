<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_traders', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('description');
            $table->decimal('manual_win_rate', 5, 2)->nullable()->after('win_rate');
            $table->boolean('use_manual_win_rate')->default(false)->after('manual_win_rate');
            $table->string('strategy_type', 50)->nullable()->after('description');
            $table->decimal('monthly_return', 5, 2)->nullable()->after('manual_win_rate');
            $table->decimal('total_profit', 18, 2)->default(0)->after('monthly_return');
        });
    }

    public function down(): void
    {
        Schema::table('master_traders', function (Blueprint $table) {
            $table->dropColumn([
                'avatar', 'manual_win_rate', 'use_manual_win_rate',
                'strategy_type', 'monthly_return', 'total_profit'
            ]);
        });
    }
};
