<?php

use App\Models\DiscordSetting;
use Illuminate\Support\Facades\Http;

test('discord wpnn api posts to configured webhook', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    DiscordSetting::current()->update([
        'wpnn_webhook_url' => 'https://discord.com/api/webhooks/test/example',
        'wpnn_message_prefix' => '@everyone',
        'wpnn_author_name' => 'WPNN Central',
        'wpnn_author_icon_url' => 'https://example.com/author.png',
        'wpnn_thumbnail_url' => 'https://example.com/thumb.png',
        'wpnn_embed_color' => '#112233',
        'wpnn_footer_text' => 'Frontline desk',
        'wpnn_show_timestamp' => true,
        'wpnn_enabled' => true,
    ]);

    Http::fake([
        'https://discord.com/api/webhooks/*' => Http::response([], 204),
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/wpnn', [
            'headline' => 'Plastic Front Update',
            'announcement' => 'Blue squads pushed forward at dawn.',
            'author_name' => 'Control Room',
            'image_url' => 'https://example.com/news.jpg',
        ]);

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'WPNN announcement posted.',
        ]);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->url() === 'https://discord.com/api/webhooks/test/example'
            && $data['content'] === '@everyone'
            && $data['embeds'][0]['title'] === 'Plastic Front Update'
            && $data['embeds'][0]['color'] === hexdec('112233')
            && $data['embeds'][0]['author']['name'] === 'WPNN Central'
            && $data['embeds'][0]['author']['icon_url'] === 'https://example.com/author.png'
            && $data['embeds'][0]['thumbnail']['url'] === 'https://example.com/thumb.png'
            && isset($data['embeds'][0]['timestamp']);
    });
});

test('discord wpnn api rejects when wpnn is disabled', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    DiscordSetting::current()->update([
        'wpnn_enabled' => false,
        'wpnn_webhook_url' => 'https://discord.com/api/webhooks/test/example',
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/wpnn', [
            'headline' => 'Plastic Front Update',
            'announcement' => 'Blue squads pushed forward at dawn.',
            'author_name' => 'Control Room',
        ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'message' => 'WPNN announcements are disabled.',
        ]);
});
