<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('min_pay')->default(1);
            $table->unsignedInteger('max_pay')->default(1);
            $table->unsignedInteger('required_level')->default(0);
            $table->unsignedInteger('work_cooldown_minutes')->default(5);
            $table->boolean('is_starter')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_jobs');
    }
};
