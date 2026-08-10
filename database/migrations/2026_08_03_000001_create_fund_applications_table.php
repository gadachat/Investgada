<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('applicant_type', 20)->default('marketer'); // marketer, leader
            $table->decimal('requested_amount', 18, 2);
            $table->decimal('approved_amount', 18, 2)->nullable();
            $table->text('purpose')->nullable();
            $table->text('admin_note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'revoked'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('funded_at')->nullable();
            // Team production tracking
            $table->decimal('team_production', 18, 2)->default(0); // Total volume produced by team
            $table->decimal('target_production', 18, 2)->default(0); // = approved_amount (100%)
            $table->decimal('production_percent', 8, 4)->default(0); // team_production / target_production * 100
            $table->boolean('target_met')->default(false);
            $table->timestamp('target_met_at')->nullable();
            // Withdrawal tracking
            $table->decimal('capital_withdrawn', 18, 2)->default(0);
            $table->decimal('profit_withdrawn', 18, 2)->default(0);
            $table->timestamps();
        });

        // Fund program settings — admin-configurable rules
        Schema::create('fund_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        $settings = [
            'fund_program_enabled'        => 'true',
            'min_fund_amount'             => '100',
            'max_fund_amount'              => '100000',
            'team_target_percent'          => '100', // team must produce X% of capital
            'allow_commission_withdrawal'  => 'true', // can withdraw commissions before target met
            'allow_profit_withdrawal'      => 'false', // cannot withdraw profit before target met
            'allow_capital_withdrawal'     => 'false', // cannot withdraw capital before target met
            'auto_calculate_production'    => 'true', // auto-track team volume
        ];

        foreach ($settings as $key => $value) {
            DB::table('fund_settings')->insert([
                'key'   => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add fund-related fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('applicant_type', ['user', 'marketer', 'leader'])->default('user')->after('role');
            $table->boolean('is_fund_recipient')->default(false)->after('applicant_type');
            $table->foreignId('active_fund_id')->nullable()->after('is_fund_recipient'); // links to fund_applications
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['applicant_type', 'is_fund_recipient', 'active_fund_id']);
        });
        Schema::dropIfExists('fund_settings');
        Schema::dropIfExists('fund_applications');
    }
};
