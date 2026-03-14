<?php

use App\Models\DiscordCommand;
use App\Models\DiscordWebhook;

test('discord command config api returns active webhook commands', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    $webhook = DiscordWebhook::query()->create([
        'name' => 'WPNN',
        'channel_id' => '123',
        'webhook_url' => 'https://discord.com/api/webhooks/test/example',
        'embed_color' => '#112233',
        'is_active' => true,
    ]);

    DiscordCommand::query()->create([
        'discord_webhook_id' => $webhook->id,
        'name' => 'WPNN News Command',
        'command_name' => 'amowwpnn',
        'command_description' => 'Post WPNN news',
        'access_mode' => 'role',
        'role_id' => '805824212060078142',
        'is_active' => true,
    ]);

    $dormantWebhook = DiscordWebhook::query()->create([
        'name' => 'Dormant Webhook',
        'channel_id' => '456',
        'webhook_url' => 'https://discord.com/api/webhooks/test/dormant',
        'embed_color' => '#334455',
        'is_active' => true,
    ]);

    DiscordCommand::query()->create([
        'discord_webhook_id' => $dormantWebhook->id,
        'name' => 'Dormant Command',
        'command_name' => 'amowdormant',
        'command_description' => 'Unused',
        'access_mode' => 'anyone',
        'is_active' => false,
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->getJson('/api/discord/commands');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'commands')
        ->assertJson([
            'commands' => [[
                'name' => 'WPNN News Command',
                'command_name' => 'amowwpnn',
                'command_description' => 'Post WPNN news',
                'channel_id' => '123',
                'access_mode' => 'role',
                'role_id' => '805824212060078142',
            ]],
        ]);
});
