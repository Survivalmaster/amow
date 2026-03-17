<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'current_activity_text')) {
                $table->string('current_activity_text', 255)->nullable()->after('current_page_name');
            }
        });

        Schema::table('game_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('game_jobs', 'working_display_message')) {
                $table->string('working_display_message', 255)->nullable()->after('stamina_decrease');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'current_activity_text')) {
                $table->dropColumn('current_activity_text');
            }
        });

        Schema::table('game_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('game_jobs', 'working_display_message')) {
                $table->dropColumn('working_display_message');
            }
        });
    }
};
