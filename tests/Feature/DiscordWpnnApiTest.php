<?php

use App\Models\DiscordWebhook;
use Illuminate\Support\Facades\Http;

test('discord wpnn api posts to configured webhook', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    DiscordWebhook::query()->create([
        'name' => 'WPNN',
        'command_name' => 'amowwpnn',
        'command_description' => 'Post WPNN news',
        'channel_id' => '1482397590933864608',
        'webhook_url' => 'https://discord.com/api/webhooks/test/example',
        'embed_color' => '#112233',
        'access_mode' => 'role',
        'role_id' => '805824212060078142',
        'is_active' => true,
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
            'command_name' => 'amowwpnn',
            'image_url' => 'https://example.com/news.jpg',
        ]);

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Discord announcement posted.',
        ]);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->url() === 'https://discord.com/api/webhooks/test/example'
            && $data['embeds'][0]['title'] === 'Plastic Front Update'
            && $data['embeds'][0]['color'] === hexdec('112233')
            && $data['embeds'][0]['footer']['text'] === 'WPNN | Posted by Control Room'
            && isset($data['embeds'][0]['timestamp']);
    });
});

test('discord wpnn api rejects when wpnn is disabled', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    DiscordWebhook::query()->create([
        'name' => 'WPNN',
        'command_name' => 'amowwpnn',
        'command_description' => 'Post WPNN news',
        'channel_id' => '1482397590933864608',
        'webhook_url' => 'https://discord.com/api/webhooks/test/example',
        'embed_color' => '#112233',
        'access_mode' => 'anyone',
        'is_active' => false,
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/wpnn', [
            'headline' => 'Plastic Front Update',
            'announcement' => 'Blue squads pushed forward at dawn.',
            'author_name' => 'Control Room',
            'command_name' => 'amowwpnn',
        ]);

    $response
        ->assertStatus(422)
        ->assertJson([
            'message' => 'This Discord webhook is disabled.',
        ]);
});
