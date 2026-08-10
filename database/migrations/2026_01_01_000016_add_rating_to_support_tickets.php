<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->tinyInteger('rating')->nullable()->after('closed_at')->comment('1-5 stars');
            $table->text('rating_comment')->nullable()->after('rating');
            $table->timestamp('rated_at')->nullable()->after('rating_comment');
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_comment', 'rated_at']);
        });
    }
};
