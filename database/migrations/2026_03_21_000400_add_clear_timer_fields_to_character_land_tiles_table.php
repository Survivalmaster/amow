<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('character_land_tiles', function (Blueprint $table) {
            $table->timestamp('clear_started_at')->nullable()->after('obstacle_type');
            $table->timestamp('clear_complete_at')->nullable()->after('clear_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('character_land_tiles', function (Blueprint $table) {
            $table->dropColumn(['clear_started_at', 'clear_complete_at']);
        });
    }
};
