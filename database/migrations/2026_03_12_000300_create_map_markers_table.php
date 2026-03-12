<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('map_markers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('faction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('icon_class');
            $table->unsignedTinyInteger('map_x');
            $table->unsignedTinyInteger('map_y');
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_markers');
    }
};
