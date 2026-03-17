<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discord_commands', function (Blueprint $table) {
            $table->foreignId('discord_webhook_id')->nullable()->change();
            $table->string('handler_key')->default('webhook_post')->after('command_description');
            $table->boolean('allow_any_channel')->default(false)->after('role_id');
            $table->json('command_options')->nullable()->after('allow_any_channel');
        });

        DB::table('discord_commands')
            ->whereNull('handler_key')
            ->update([
                'handler_key' => 'webhook_post',
                'allow_any_channel' => false,
            ]);

        if (! DB::table('discord_commands')->where('command_name', 'pray')->exists()) {
            DB::table('discord_commands')->insert([
                'discord_webhook_id' => null,
                'name' => 'Pray to Marble or Obsidian',
                'command_name' => 'pray',
                'command_description' => 'Ask Marble or Obsidian to bless or smite you.',
                'handler_key' => 'pray_to_deity',
                'access_mode' => 'anyone',
                'role_id' => null,
                'allow_any_channel' => true,
                'command_options' => json_encode([
                    [
                        'name' => 'deity',
                        'description' => 'Choose the god you want to pray to.',
                        'type' => 'string',
                        'required' => true,
                        'choices' => [
                            ['name' => 'Marble', 'value' => 'Marble'],
                            ['name' => 'Obsidian', 'value' => 'Obsidian'],
                        ],
                    ],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('discord_commands')->where('command_name', 'pray')->delete();

        Schema::table('discord_commands', function (Blueprint $table) {
            $table->dropColumn(['handler_key', 'allow_any_channel', 'command_options']);
        });
    }
};
