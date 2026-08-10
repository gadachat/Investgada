<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'interest', 'commission', 'bonus', 'withdrawal'])
                ->comment('deposit=main, interest=earnings, commission=referral, bonus=matching, withdrawal=holding');
            $table->string('currency', 10)->default('USD');
            $table->decimal('balance', 18, 8)->default(0);
            $table->decimal('locked_balance', 18, 8)->default(0)->comment('frozen for pending withdrawals/investments');
            $table->timestamps();

            $table->unique(['user_id', 'type', 'currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
