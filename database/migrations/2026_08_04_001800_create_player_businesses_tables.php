<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('faction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('licence_id')->nullable()->constrained('licences')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon_class')->default('fa-solid fa-store');
            $table->string('business_type')->default('sells_items');
            $table->text('description')->nullable();
            $table->unsignedInteger('bank_credits')->default(0);
            $table->timestamps();

            $table->index(['owner_character_id', 'faction_id']);
        });

        Schema::create('player_business_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_business_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('mode')->default('sells_items');
            $table->unsignedInteger('price')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['player_business_id', 'is_active']);
        });

        Schema::create('player_business_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('hourly_wage')->default(0);
            $table->timestamps();
        });

        Schema::create('player_business_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_business_role_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invited_by_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('status')->default('invited');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamps();

            $table->unique(['player_business_id', 'character_id']);
            $table->index(['character_id', 'status']);
        });

        Schema::create('player_business_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_character_id')->nullable()->constrained('characters')->nullOnDelete();
            $table->string('type');
            $table->integer('amount')->default(0);
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['player_business_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_business_logs');
        Schema::dropIfExists('player_business_members');
        Schema::dropIfExists('player_business_roles');
        Schema::dropIfExists('player_business_menu_items');
        Schema::dropIfExists('player_businesses');
    }
};
