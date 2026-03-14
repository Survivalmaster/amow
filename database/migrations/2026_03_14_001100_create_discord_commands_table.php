<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discord_webhook_id')->constrained('discord_webhooks')->cascadeOnDelete();
            $table->string('name');
            $table->string('command_name')->unique();
            $table->string('command_description')->nullable();
            $table->string('access_mode')->default('anyone');
            $table->string('role_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('discord_webhooks')) {
            $webhooks = DB::table('discord_webhooks')
                ->whereNotNull('command_name')
                ->get();

            foreach ($webhooks as $webhook) {
                DB::table('discord_commands')->insert([
                    'discord_webhook_id' => $webhook->id,
                    'name' => $webhook->name,
                    'command_name' => $webhook->command_name,
                    'command_description' => $webhook->command_description,
                    'access_mode' => $webhook->access_mode ?: 'anyone',
                    'role_id' => $webhook->role_id,
                    'is_active' => (bool) $webhook->is_active,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_commands');
    }
};
