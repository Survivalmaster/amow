<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('short_description');
            $table->string('flag_image')->nullable();
            $table->text('lore')->nullable();
            $table->timestamps();
        });

        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('order_index')->unique();
            $table->boolean('is_military')->default(false);
            $table->timestamps();
        });

        Schema::create('licences', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description');
            $table->unsignedInteger('cost');
            $table->foreignId('required_rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('faction_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('age');
            $table->text('biography');
            $table->string('starting_occupation');
            $table->unsignedInteger('plastic_credits')->default(0);
            $table->foreignId('rank_id')->constrained('ranks')->restrictOnDelete();
            $table->unsignedInteger('influence_score')->default(0);
            $table->unsignedInteger('military_score')->default(0);
            $table->unsignedInteger('economic_score')->default(0);
            $table->enum('role_type', ['civilian', 'military']);
            $table->boolean('is_business_owner')->default(false);
            $table->timestamp('last_worked_at')->nullable();
            $table->timestamp('last_business_payout_at')->nullable();
            $table->timestamps();
        });

        Schema::create('character_licences', function (Blueprint $table) {
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('licence_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['character_id', 'licence_id']);
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faction_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->foreignId('required_rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->foreignId('required_licence_id')->nullable()->constrained('licences')->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->unique(['city_id', 'slug']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('amount');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('type');
            $table->unsignedInteger('price');
            $table->foreignId('required_rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->enum('required_role_type', ['civilian', 'military'])->nullable();
            $table->foreignId('required_licence_id')->nullable()->constrained('licences')->nullOnDelete();
            $table->unsignedInteger('stock')->nullable();
            $table->timestamps();
        });

        Schema::create('character_items', function (Blueprint $table) {
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->primary(['character_id', 'item_id']);
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->decimal('current_price', 10, 2);
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('stock_holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('shares')->default(0);
            $table->decimal('average_buy_price', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['character_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_holdings');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('character_items');
        Schema::dropIfExists('items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('character_licences');
        Schema::dropIfExists('characters');
        Schema::dropIfExists('licences');
        Schema::dropIfExists('ranks');
        Schema::dropIfExists('factions');
    }
};
