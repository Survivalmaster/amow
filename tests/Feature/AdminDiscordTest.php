<?php

use App\Models\DiscordCommand;
use App\Models\DiscordWebhook;
use App\Models\User;

test('admin can view discord settings page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->get('/admin/discord');

    $response->assertOk();
    $response->assertSee('Create Webhook');
    $response->assertSee('Create Discord Command');
});

test('admin can create and update discord webhooks and commands', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->post('/admin/discord', [
            'name' => 'WPNN',
            'channel_id' => '805822469012586497',
            'webhook_url' => 'https://discord.com/api/webhooks/test/example',
            'embed_color' => '#112233',
            'is_active' => '1',
        ]);

    $response->assertRedirect();

    $webhook = DiscordWebhook::query()->firstOrFail();

    expect($webhook->name)->toBe('WPNN');
    expect($webhook->channel_id)->toBe('805822469012586497');
    expect($webhook->webhook_url)->toBe('https://discord.com/api/webhooks/test/example');
    expect($webhook->embed_color)->toBe('#112233');
    expect($webhook->is_active)->toBeTrue();

    $response = $this
        ->actingAs($admin)
        ->post('/admin/discord/commands', [
            'discord_webhook_id' => $webhook->id,
            'name' => 'WPNN News Command',
            'command_name' => 'amowwpnn',
            'command_description' => 'Post WPNN news',
            'access_mode' => 'role',
            'role_id' => '805824212060078142',
            'is_active' => '1',
        ]);

    $response->assertRedirect();

    $command = DiscordCommand::query()->firstOrFail();

    expect($command->name)->toBe('WPNN News Command');
    expect($command->command_name)->toBe('amowwpnn');
    expect($command->command_description)->toBe('Post WPNN news');
    expect($command->discord_webhook_id)->toBe($webhook->id);
    expect($command->access_mode)->toBe('role');
    expect($command->role_id)->toBe('805824212060078142');
    expect($command->is_active)->toBeTrue();

    $response = $this
        ->actingAs($admin)
        ->patch("/admin/discord/{$webhook->id}", [
            'name' => 'Faction News',
            'channel_id' => '1482397590933864608',
            'webhook_url' => 'https://discord.com/api/webhooks/test/updated',
            'embed_color' => '#334455',
        ]);

    $response->assertRedirect();

    $webhook->refresh();

    expect($webhook->name)->toBe('Faction News');
    expect($webhook->channel_id)->toBe('1482397590933864608');
    expect($webhook->webhook_url)->toBe('https://discord.com/api/webhooks/test/updated');
    expect($webhook->embed_color)->toBe('#334455');
    expect($webhook->is_active)->toBeFalse();

    $response = $this
        ->actingAs($admin)
        ->patch("/admin/discord/commands/{$command->id}", [
            'discord_webhook_id' => $webhook->id,
            'name' => 'Faction News Command',
            'command_name' => 'amowfactionnews',
            'command_description' => 'Post faction news',
            'access_mode' => 'anyone',
        ]);

    $response->assertRedirect();

    $command->refresh();

    expect($command->name)->toBe('Faction News Command');
    expect($command->command_name)->toBe('amowfactionnews');
    expect($command->command_description)->toBe('Post faction news');
    expect($command->discord_webhook_id)->toBe($webhook->id);
    expect($command->access_mode)->toBe('anyone');
    expect($command->role_id)->toBeNull();
    expect($command->is_active)->toBeFalse();
});
