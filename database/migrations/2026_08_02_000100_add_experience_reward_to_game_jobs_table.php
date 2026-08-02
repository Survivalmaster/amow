<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_jobs', function (Blueprint $table) {
            $table->unsignedInteger('experience_reward')->default(5)->after('stamina_decrease');
        });
    }

    public function down(): void
    {
        Schema::table('game_jobs', function (Blueprint $table) {
            $table->dropColumn('experience_reward');
        });
    }
};
