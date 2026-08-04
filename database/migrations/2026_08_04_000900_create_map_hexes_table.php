<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_hexes', function (Blueprint $table) {
            $table->id();
            $table->integer('grid_column');
            $table->integer('grid_row');
            $table->decimal('centre_x', 10, 3);
            $table->decimal('centre_y', 10, 3);
            $table->json('polygon_coordinates');
            $table->string('tile_type')->default('inactive');
            $table->foreignId('faction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('terrain_type')->nullable();
            $table->unsignedInteger('claim_strength')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->unique(['grid_column', 'grid_row']);
            $table->index(['tile_type', 'is_visible']);
            $table->index('faction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_hexes');
    }
};
