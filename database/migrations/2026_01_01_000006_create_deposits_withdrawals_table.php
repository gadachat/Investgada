<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deposit requests
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('method', ['bank_transfer', 'crypto', 'card', 'wallet', 'manual'])
                ->default('manual');
            $table->string('currency', 10)->default('USD');
            $table->decimal('amount', 18, 8);
            $table->decimal('fee', 18, 8)->default(0);
            $table->decimal('net_amount', 18, 8)->default(0);

            // Crypto details
            $table->string('tx_hash')->nullable();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->string('network', 20)->nullable()->comment('TRC20, ERC20, BEP20, etc.');

            // Bank/manual details
            $table->string('bank_reference')->nullable();
            $table->text('admin_note')->nullable();
            $table->json('payment_proof')->nullable()->comment('uploaded file paths');

            $table->enum('status', ['pending', 'confirmed', 'rejected', 'expired'])
                ->default('pending');
            $table->boolean('commission_paid')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('status');
        });

        // Withdrawal requests
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('method', ['bank_transfer', 'crypto', 'wallet', 'manual'])
                ->default('manual');
            $table->string('currency', 10)->default('USD');
            $table->decimal('amount', 18, 8);
            $table->decimal('fee', 18, 8)->default(0);
            $table->decimal('net_amount', 18, 8)->default(0);

            // Destination details
            $table->string('wallet_address')->nullable();
            $table->string('network', 20)->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_routing')->nullable();
            $table->string('bank_country', 80)->nullable();

            $table->enum('status', ['pending', 'processing', 'completed', 'rejected', 'cancelled'])
                ->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('deposits');
    }
};
