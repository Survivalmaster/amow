<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->timestamp('ends_at')->nullable()->after('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->dropColumn('ends_at');
        });
    }
};
