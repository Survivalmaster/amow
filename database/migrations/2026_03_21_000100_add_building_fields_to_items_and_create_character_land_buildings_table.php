<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_building')->default(false)->after('is_home');
            $table->unsignedTinyInteger('footprint_width')->default(1)->after('is_building');
            $table->unsignedTinyInteger('footprint_height')->default(1)->after('footprint_width');
            $table->unsignedInteger('build_time_minutes')->default(0)->after('footprint_height');
        });

        Schema::create('character_land_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('grid_x');
            $table->unsignedTinyInteger('grid_y');
            $table->timestamp('build_started_at');
            $table->timestamp('build_complete_at')->nullable();
            $table->timestamps();

            $table->unique(['character_id', 'grid_x', 'grid_y']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_land_buildings');

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'is_building',
                'footprint_width',
                'footprint_height',
                'build_time_minutes',
            ]);
        });
    }
};
