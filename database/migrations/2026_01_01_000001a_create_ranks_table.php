<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('badge_color', 7)->default('#6366f1'); // hex color
            $table->string('icon', 50)->nullable();

            // Qualification criteria
            $table->decimal('min_investment', 18, 2)->default(0);
            $table->integer('min_direct_referrals')->default(0);
            $table->decimal('min_team_volume', 18, 2)->default(0);
            $table->decimal('min_left_volume', 18, 2)->default(0);
            $table->decimal('min_right_volume', 18, 2)->default(0);

            // Rewards
            $table->decimal('matching_bonus_percent', 5, 2)->default(0);
            $table->decimal('direct_referral_percent', 5, 2)->default(0);
            $table->decimal('profit_share_percent', 5, 2)->default(0);
            $table->decimal('salary_bonus', 18, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
