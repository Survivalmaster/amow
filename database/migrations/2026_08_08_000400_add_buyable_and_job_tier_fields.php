<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_buyable')->default(true)->after('is_building');
        });

        Schema::table('game_jobs', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_tier')->default(20)->after('experience_reward');
            $table->unsignedInteger('tier_xp_required')->default(100)->after('max_tier');
            $table->unsignedInteger('tier_pay_bonus_percent')->default(5)->after('tier_xp_required');
            $table->unsignedInteger('tier_xp_bonus_percent')->default(5)->after('tier_pay_bonus_percent');
        });
    }

    public function down(): void
    {
        Schema::table('game_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'max_tier',
                'tier_xp_required',
                'tier_pay_bonus_percent',
                'tier_xp_bonus_percent',
            ]);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('is_buyable');
        });
    }
};
