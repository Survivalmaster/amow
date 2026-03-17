<?php

use App\Models\DiscordCommand;

test('discord pray api returns a blessing or smiting message for the chosen deity', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    DiscordCommand::query()->where('command_name', 'pray')->update([
        'is_active' => true,
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/pray', [
            'command_name' => 'pray',
            'deity' => 'Marble',
            'user_mention' => '<@123456789>',
        ]);

    $response->assertOk();

    expect($response->json('deity'))->toBe('Marble');
    expect($response->json('message'))->toStartWith('<@123456789> Marble has ');
    expect($response->json('outcome'))->toBeIn(['blessed', 'smited']);
});

test('discord command config api includes pray command interaction metadata', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    DiscordCommand::query()->where('command_name', 'pray')->update([
        'is_active' => true,
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->getJson('/api/discord/commands');

    $response
        ->assertOk()
        ->assertJsonFragment([
            'command_name' => 'pray',
            'handler_key' => 'pray_to_deity',
            'allow_any_channel' => true,
            'access_mode' => 'anyone',
        ]);

    expect($response->json('commands.0.command_options.0.choices'))->toBe([
        ['name' => 'Marble', 'value' => 'Marble'],
        ['name' => 'Obsidian', 'value' => 'Obsidian'],
    ]);
});
