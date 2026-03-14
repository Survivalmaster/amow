<?php

use App\Models\DiscordWebhook;

test('discord command config api returns active webhook commands', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    DiscordWebhook::query()->create([
        'name' => 'WPNN',
        'command_name' => 'amowwpnn',
        'command_description' => 'Post WPNN news',
        'channel_id' => '123',
        'webhook_url' => 'https://discord.com/api/webhooks/test/example',
        'embed_color' => '#112233',
        'access_mode' => 'role',
        'role_id' => '805824212060078142',
        'is_active' => true,
    ]);

    DiscordWebhook::query()->create([
        'name' => 'Dormant',
        'command_name' => 'amowdormant',
        'command_description' => 'Unused',
        'channel_id' => '456',
        'webhook_url' => 'https://discord.com/api/webhooks/test/dormant',
        'embed_color' => '#334455',
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
                'name' => 'WPNN',
                'command_name' => 'amowwpnn',
                'command_description' => 'Post WPNN news',
                'channel_id' => '123',
                'access_mode' => 'role',
                'role_id' => '805824212060078142',
            ]],
        ]);
});
