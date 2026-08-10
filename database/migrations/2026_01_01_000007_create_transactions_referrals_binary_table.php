<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Universal transaction ledger — every money movement gets a row
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', [
                'deposit', 'withdrawal', 'investment', 'payout',
                'direct_referral', 'matching_bonus', 'profit_share',
                'rank_bonus', 'transfer_in', 'transfer_out', 'fee', 'adjustment',
                'admin_fund', 'admin_deduction', 'leadership_bonus',
                'referral_commission', 'direct_referral_bonus',
                'rank_promotion_bonus', 'auto_trade', 'daily_profit',
                'earning', 'investment_profit', 'principal_return',
                'profit', 'bonus', 'kyc', 'security', 'account_status', 'system'
            ]);
            $table->enum('direction', ['credit', 'debit']);
            $table->decimal('amount', 18, 8);
            $table->decimal('balance_after', 18, 8)->nullable();
            $table->string('currency', 10)->default('USD');

            $table->text('description')->nullable();
            $table->json('metadata')->nullable()->comment('extra context: package_id, from_user, etc.');

            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            $table->timestamps();
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'direction']);
            $table->index('reference');
        });

        // Referral relationships & commission tracking
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code', 20);
            $table->enum('status', ['pending', 'active', 'inactive'])->default('active');
            $table->decimal('commission_earned', 18, 2)->default(0);
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique('referred_id'); // one referrer per user
            $table->index('referrer_id');
        });

        // Binary MLM tree structure & volume tracking
        Schema::create('binary_tree', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('position', ['left', 'right']);
            $table->foreignId('left_child_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('right_child_id')->nullable()->constrained('users')->nullOnDelete();

            // Volume tracking
            $table->decimal('left_volume', 18, 2)->default(0);
            $table->decimal('right_volume', 18, 2)->default(0);
            $table->decimal('left_carry_forward', 18, 2)->default(0);
            $table->decimal('right_carry_forward', 18, 2)->default(0);

            // Matching
            $table->decimal('total_matching_bonus', 18, 2)->default(0);
            $table->timestamp('last_matched_at')->nullable();

            $table->integer('left_count')->default(0);
            $table->integer('right_count')->default(0);
            $table->integer('level')->default(0);

            $table->timestamps();

            $table->unique('user_id');
            $table->index('parent_id');
        });

        // Binary matching bonus history
        Schema::create('matching_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('left_volume', 18, 2);
            $table->decimal('right_volume', 18, 2);
            $table->decimal('matched_volume', 18, 2)->comment('the smaller of left/right');
            $table->decimal('bonus_percent', 5, 2);
            $table->decimal('bonus_amount', 18, 2);
            $table->decimal('carry_forward_left', 18, 2);
            $table->decimal('carry_forward_right', 18, 2);
            $table->enum('status', ['pending', 'paid', 'flushed'])->default('paid');
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_bonuses');
        Schema::dropIfExists('binary_tree');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('transactions');
    }
};
