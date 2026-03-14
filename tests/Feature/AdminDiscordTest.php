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
            'wpnn_message_prefix' => '@everyone Breaking update',
            'wpnn_author_name' => 'WPNN Central',
            'wpnn_author_icon_url' => 'https://example.com/author.png',
            'wpnn_thumbnail_url' => 'https://example.com/thumb.png',
            'wpnn_embed_color' => '#112233',
            'wpnn_footer_text' => 'Frontline desk',
            'wpnn_show_timestamp' => '1',
            'wpnn_enabled' => '1',
        ]);

    $response->assertRedirect();

    $settings = DiscordSetting::current();

    expect($settings->wpnn_webhook_url)->toBe('https://discord.com/api/webhooks/test/example');
    expect($settings->wpnn_message_prefix)->toBe('@everyone Breaking update');
    expect($settings->wpnn_author_name)->toBe('WPNN Central');
    expect($settings->wpnn_author_icon_url)->toBe('https://example.com/author.png');
    expect($settings->wpnn_thumbnail_url)->toBe('https://example.com/thumb.png');
    expect($settings->wpnn_embed_color)->toBe('#112233');
    expect($settings->wpnn_footer_text)->toBe('Frontline desk');
    expect($settings->wpnn_show_timestamp)->toBeTrue();
    expect($settings->wpnn_enabled)->toBeTrue();
});
