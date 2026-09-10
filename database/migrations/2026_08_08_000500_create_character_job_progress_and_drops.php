<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_job_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_job_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('tier')->default(1);
            $table->unsignedInteger('tier_experience')->default(0);
            $table->timestamps();

            $table->unique(['character_id', 'game_job_id']);
        });

        Schema::create('game_job_drops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('min_tier')->default(1);
            $table->unsignedTinyInteger('max_tier')->default(20);
            $table->unsignedInteger('min_quantity')->default(1);
            $table->unsignedInteger('max_quantity')->default(1);
            $table->decimal('drop_chance_percent', 5, 2)->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_job_drops');
        Schema::dropIfExists('character_job_progress');
    }
};
