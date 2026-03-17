<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_jobs', function (Blueprint $table) {
            $table->unsignedInteger('stamina_decrease')->default(0)->after('work_cooldown_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('game_jobs', function (Blueprint $table) {
            $table->dropColumn('stamina_decrease');
        });
    }
};
