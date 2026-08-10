<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The activity_logs table is initially created in migration
     * 2026_01_01_000008_create_kyc_support_notifications_table.php.
     * This migration adds the additional columns required by the
     * ActivityLog model (admin_id, subject_type, subject_id, properties).
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('subject_type', 200)->nullable()->after('description');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->json('properties')->nullable()->after('user_agent');

            $table->index('action');
            $table->index('created_at');
            $table->index(['user_id', 'action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'action', 'created_at']);
            $table->dropIndex('activity_logs_created_at_index');
            $table->dropIndex('activity_logs_action_index');
            $table->dropColumn(['admin_id', 'subject_type', 'subject_id', 'user_agent', 'properties']);
        });
    }
};
