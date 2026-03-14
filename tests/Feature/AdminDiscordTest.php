<?php

use App\Models\DiscordWebhook;
use App\Models\User;

test('admin can view discord settings page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->get('/admin/discord');

    $response->assertOk();
    $response->assertSee('Create Webhook');
});

test('admin can create and update discord webhooks', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->post('/admin/discord', [
            'name' => 'WPNN',
            'command_name' => 'amowwpnn',
            'command_description' => 'Post WPNN news',
            'channel_id' => '805822469012586497',
            'webhook_url' => 'https://discord.com/api/webhooks/test/example',
            'embed_color' => '#112233',
            'access_mode' => 'role',
            'role_id' => '805824212060078142',
            'is_active' => '1',
        ]);

    $response->assertRedirect();

    $webhook = DiscordWebhook::query()->firstOrFail();

    expect($webhook->name)->toBe('WPNN');
    expect($webhook->command_name)->toBe('amowwpnn');
    expect($webhook->command_description)->toBe('Post WPNN news');
    expect($webhook->channel_id)->toBe('805822469012586497');
    expect($webhook->webhook_url)->toBe('https://discord.com/api/webhooks/test/example');
    expect($webhook->embed_color)->toBe('#112233');
    expect($webhook->access_mode)->toBe('role');
    expect($webhook->role_id)->toBe('805824212060078142');
    expect($webhook->is_active)->toBeTrue();

    $response = $this
        ->actingAs($admin)
        ->patch("/admin/discord/{$webhook->id}", [
            'name' => 'Faction News',
            'command_name' => 'amowfactionnews',
            'command_description' => 'Post faction news',
            'channel_id' => '1482397590933864608',
            'webhook_url' => 'https://discord.com/api/webhooks/test/updated',
            'embed_color' => '#334455',
            'access_mode' => 'anyone',
        ]);

    $response->assertRedirect();

    $webhook->refresh();

    expect($webhook->name)->toBe('Faction News');
    expect($webhook->command_name)->toBe('amowfactionnews');
    expect($webhook->command_description)->toBe('Post faction news');
    expect($webhook->channel_id)->toBe('1482397590933864608');
    expect($webhook->webhook_url)->toBe('https://discord.com/api/webhooks/test/updated');
    expect($webhook->embed_color)->toBe('#334455');
    expect($webhook->access_mode)->toBe('anyone');
    expect($webhook->role_id)->toBeNull();
    expect($webhook->is_active)->toBeFalse();
});
