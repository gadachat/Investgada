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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 50)->nullable()->unique()->after('status');
            }
            if (!Schema::hasColumn('users', 'referred_by_code')) {
                $table->string('referred_by_code', 50)->nullable()->after('referral_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referred_by_code')) {
                $table->dropColumn('referred_by_code');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
        });
    }
};
