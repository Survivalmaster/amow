<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $options = json_encode([
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
        ], JSON_THROW_ON_ERROR);

        $existingAmowpray = DB::table('discord_commands')->where('command_name', 'amowpray')->first();
        $existingPray = DB::table('discord_commands')->where('command_name', 'pray')->first();

        if ($existingAmowpray) {
            DB::table('discord_commands')
                ->where('id', $existingAmowpray->id)
                ->update([
                    'discord_webhook_id' => null,
                    'name' => 'Pray to Marble or Obsidian',
                    'command_description' => 'Ask Marble or Obsidian to bless or smite you.',
                    'handler_key' => 'pray_to_deity',
                    'access_mode' => 'anyone',
                    'role_id' => null,
                    'allow_any_channel' => true,
                    'command_options' => $options,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        } elseif ($existingPray) {
            DB::table('discord_commands')
                ->where('id', $existingPray->id)
                ->update([
                    'discord_webhook_id' => null,
                    'name' => 'Pray to Marble or Obsidian',
                    'command_name' => 'amowpray',
                    'command_description' => 'Ask Marble or Obsidian to bless or smite you.',
                    'handler_key' => 'pray_to_deity',
                    'access_mode' => 'anyone',
                    'role_id' => null,
                    'allow_any_channel' => true,
                    'command_options' => $options,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('discord_commands')->insert([
                'discord_webhook_id' => null,
                'name' => 'Pray to Marble or Obsidian',
                'command_name' => 'amowpray',
                'command_description' => 'Ask Marble or Obsidian to bless or smite you.',
                'handler_key' => 'pray_to_deity',
                'access_mode' => 'anyone',
                'role_id' => null,
                'allow_any_channel' => true,
                'command_options' => $options,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('discord_commands')
            ->where('command_name', 'pray')
            ->where('handler_key', 'pray_to_deity')
            ->delete();
    }

    public function down(): void
    {
        DB::table('discord_commands')
            ->where('command_name', 'amowpray')
            ->where('handler_key', 'pray_to_deity')
            ->update([
                'command_name' => 'pray',
                'updated_at' => now(),
            ]);
    }
};
