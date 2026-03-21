<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_land_tiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('grid_x');
            $table->unsignedTinyInteger('grid_y');
            $table->string('state')->default('blocked');
            $table->string('obstacle_type')->nullable();
            $table->timestamp('cleared_at')->nullable();
            $table->timestamps();

            $table->unique(['character_id', 'grid_x', 'grid_y']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_land_tiles');
    }
};
