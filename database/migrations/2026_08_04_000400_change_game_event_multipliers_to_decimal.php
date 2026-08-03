<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->decimal('xp_multiplier', 4, 2)->nullable()->change();
            $table->decimal('credit_multiplier', 4, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->unsignedTinyInteger('xp_multiplier')->nullable()->change();
            $table->unsignedTinyInteger('credit_multiplier')->nullable()->change();
        });
    }
};
