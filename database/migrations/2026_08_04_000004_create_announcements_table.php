<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'danger', 'maintenance'])->default('info');
            $table->enum('target', ['all', 'verified', 'investors', 'traders', 'specific'])->default('all');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_dismissible')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['is_active', 'target']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
