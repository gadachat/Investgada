<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('platform_settings', 'type')) {
            Schema::table('platform_settings', function (Blueprint $table) {
                $table->string('type', 20)->default('string')->after('value')->comment('string, boolean, integer, json');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('platform_settings', 'type')) {
            Schema::table('platform_settings', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
