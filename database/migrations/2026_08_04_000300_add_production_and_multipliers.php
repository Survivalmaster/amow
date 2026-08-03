<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('produced_by_building_item_id')
                ->nullable()
                ->after('build_time_minutes')
                ->constrained('items')
                ->nullOnDelete();
        });

        Schema::table('game_events', function (Blueprint $table) {
            $table->boolean('xp_multiplier_enabled')->default(false)->after('is_enabled');
            $table->unsignedTinyInteger('xp_multiplier')->nullable()->after('xp_multiplier_enabled');
            $table->boolean('credit_multiplier_enabled')->default(false)->after('xp_multiplier');
            $table->unsignedTinyInteger('credit_multiplier')->nullable()->after('credit_multiplier_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->dropColumn([
                'xp_multiplier_enabled',
                'xp_multiplier',
                'credit_multiplier_enabled',
                'credit_multiplier',
            ]);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produced_by_building_item_id');
        });
    }
};
