<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Security / Audit Logs — every admin action
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();          // login, logout, deposit_approve, withdrawal_complete, settings_update, etc.
            $table->string('module', 50)->nullable();         // deposits, withdrawals, kyc, settings, users, auth
            $table->string('description')->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->enum('severity', ['info', 'warning', 'critical', 'danger'])->default('info');
            $table->json('metadata')->nullable();            // before/after values, affected record IDs, etc.
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['severity', 'created_at']);
        });

        // 2. Login Attempts — track every login try (success + failure)
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->index();
            $table->string('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->string('failure_reason')->nullable();    // wrong_password, account_suspended, etc.
            $table->timestamps();

            $table->index(['ip_address', 'successful', 'created_at']);
            $table->index(['email', 'successful', 'created_at']);
        });

        // 3. Blocked / Whitelisted IPs
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->enum('type', ['blocked', 'whitelisted'])->default('blocked');
            $table->string('reason')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();      // null = permanent
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Active Sessions — track who's logged in (parallel session management)
        Schema::create('active_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 100)->unique();
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('device_type', 50)->nullable();    // desktop, mobile, tablet
            $table->timestamp('last_activity');
            $table->timestamps();

            $table->index(['user_id', 'last_activity']);
        });

        // 5. Security Settings (platform-wide security configuration)
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });

        // Seed default security settings
        $defaults = [
            ['key' => 'max_login_attempts', 'value' => '5', 'group' => 'auth'],
            ['key' => 'lockout_duration_minutes', 'value' => '30', 'group' => 'auth'],
            ['key' => 'require_2fa_admin', 'value' => '0', 'group' => 'auth'],
            ['key' => 'session_timeout_minutes', 'value' => '120', 'group' => 'session'],
            ['key' => 'allow_multiple_sessions', 'value' => '1', 'group' => 'session'],
            ['key' => 'ip_whitelist_enabled', 'value' => '0', 'group' => 'network'],
            ['key' => 'ip_blacklist_enabled', 'value' => '1', 'group' => 'network'],
            ['key' => 'auto_block_failed_logins', 'value' => '1', 'group' => 'network'],
            ['key' => 'auto_block_threshold', 'value' => '20', 'group' => 'network'],
            ['key' => 'log_retention_days', 'value' => '90', 'group' => 'logging'],
            ['key' => 'enable_audit_trail', 'value' => '1', 'group' => 'logging'],
            ['key' => 'notify_critical_actions', 'value' => '1', 'group' => 'notifications'],
            ['key' => 'withdrawal_requires_2fa', 'value' => '0', 'group' => 'transactions'],
            ['key' => 'large_withdrawal_threshold', 'value' => '10000', 'group' => 'transactions'],
            ['key' => 'large_withdrawal_requires_approval', 'value' => '1', 'group' => 'transactions'],
        ];

        foreach ($defaults as $d) {
            DB::table('security_settings')->insertOrIgnore(array_merge($d, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('active_sessions');
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('security_logs');
    }
};
