<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_polygons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('faction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stroke_color', 20)->default('#c2a84f');
            $table->string('fill_color', 20)->default('#7ead59');
            $table->decimal('fill_opacity', 3, 2)->default(0.25);
            $table->unsignedSmallInteger('stroke_weight')->default(2);
            $table->json('coordinates');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_polygons');
    }
};
