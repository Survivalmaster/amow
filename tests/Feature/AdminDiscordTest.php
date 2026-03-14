<?php

use App\Models\DiscordSetting;
use App\Models\User;

test('admin can view discord settings page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->get('/admin/discord');

    $response->assertOk();
    $response->assertSee('WPNN Webhook');
});

test('admin can update discord settings', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->patch('/admin/discord', [
            'wpnn_webhook_url' => 'https://discord.com/api/webhooks/test/example',
            'wpnn_embed_color' => '#112233',
            'wpnn_footer_text' => 'Frontline desk',
            'wpnn_enabled' => '1',
        ]);

    $response->assertRedirect();

    $settings = DiscordSetting::current();

    expect($settings->wpnn_webhook_url)->toBe('https://discord.com/api/webhooks/test/example');
    expect($settings->wpnn_embed_color)->toBe('#112233');
    expect($settings->wpnn_footer_text)->toBe('Frontline desk');
    expect($settings->wpnn_enabled)->toBeTrue();
});
