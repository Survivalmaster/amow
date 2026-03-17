<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_markers', function (Blueprint $table) {
            $table->string('icon_type')->default('fontawesome')->after('faction_id');
        });

        DB::table('map_markers')->update([
            'icon_type' => 'fontawesome',
        ]);
    }

    public function down(): void
    {
        Schema::table('map_markers', function (Blueprint $table) {
            $table->dropColumn('icon_type');
        });
    }
};
