<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_role_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('discord_role_categories')->insert([
            [
                'name' => 'Staff & Community Team',
                'slug' => 'staff',
                'description' => 'Admins, moderators, managers, creators, and other server team roles.',
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Nations & Ranks',
                'slug' => 'nations',
                'description' => 'Nation, faction, command, rank, and leadership roles.',
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Departments & Teams',
                'slug' => 'departments',
                'description' => 'Department, company, unit, job, and operational team roles.',
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Managed & Bot Roles',
                'slug' => 'managed',
                'description' => 'Discord-managed integration roles and bot-created roles.',
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Community Roles',
                'slug' => 'community',
                'description' => 'Personal, social, and general community roles.',
                'sort_order' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Empty Roles',
                'slug' => 'empty',
                'description' => 'Roles that currently have no members assigned.',
                'sort_order' => 60,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_role_categories');
    }
};
