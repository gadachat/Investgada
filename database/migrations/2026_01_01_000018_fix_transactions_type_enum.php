<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change type column from enum to varchar to support all transaction types
        // without needing a migration every time we add a new type
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type VARCHAR(50) NOT NULL");
        } else {
            // SQLite / PostgreSQL: use Schema builder
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('type', 50)->change();
            });
        }
    }

    public function down(): void
    {
        // For safety, revert to VARCHAR (not back to ENUM, which could truncate data)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type VARCHAR(50) NOT NULL");
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('type', 50)->change();
            });
        }
    }
};
