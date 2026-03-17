<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('map_markers', function (Blueprint $table) {
            $table->decimal('map_x', 7, 4)->change();
            $table->decimal('map_y', 7, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('map_markers', function (Blueprint $table) {
            $table->unsignedTinyInteger('map_x')->change();
            $table->unsignedTinyInteger('map_y')->change();
        });
    }
};
