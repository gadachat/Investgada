<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Personal info
            $table->string('document_type');       // passport, driver_license, national_id, voter_card
            $table->string('document_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('nationality');
            $table->string('phone_number');

            // Address
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postal_code')->nullable();
            $table->string('country');

            // File paths (stored on private disk)
            $table->string('id_front_path');
            $table->string('id_back_path');
            $table->string('proof_of_address_path');
            $table->string('selfie_path');

            // Status / workflow
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
