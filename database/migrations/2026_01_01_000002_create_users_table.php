<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('country', 80)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->enum('role', ['user', 'admin', 'super_admin'])->default('user');
            $table->boolean('is_admin')->default(false);
            $table->enum('status', ['active', 'inactive', 'suspended', 'banned'])->default('active');

            // Referral & MLM
            $table->foreignId('sponsor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete(); // Binary tree parent
            $table->enum('binary_position', ['left', 'right'])->nullable(); // Position under parent
            $table->string('referral_code', 20)->unique();

            // Rank
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();

            // KYC
            $table->enum('kyc_status', ['pending', 'verified', 'rejected', 'not_submitted'])->default('not_submitted');

            // Totals
            $table->decimal('total_invested', 18, 2)->default(0);
            $table->decimal('total_earned', 18, 2)->default(0);
            $table->decimal('total_withdrawn', 18, 2)->default(0);
            $table->decimal('total_referral_earnings', 18, 2)->default(0);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
