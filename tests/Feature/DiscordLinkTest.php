<?php

use App\Models\User;

test('authenticated user can generate a discord link token', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/profile/discord-link');

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->discord_link_token)->not->toBeNull();
    expect($user->discord_link_token_expires_at)->not->toBeNull();
});

test('discord bot endpoint can complete a link with a valid secret and token', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    $user = User::factory()->create([
        'discord_link_token' => 'ABCDEF123456',
        'discord_link_token_expires_at' => now()->addMinutes(10),
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/link/complete', [
            'token' => 'abcdef123456',
            'discord_user_id' => '123456789012345678',
            'discord_username' => 'TestUser#1234',
            'discord_avatar' => 'avatar-hash',
        ]);

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Discord account linked.',
            'user_id' => $user->id,
        ]);

    $user->refresh();

    expect($user->discord_user_id)->toBe('123456789012345678');
    expect($user->discord_username)->toBe('TestUser#1234');
    expect($user->discord_avatar)->toBe('avatar-hash');
    expect($user->discord_linked_at)->not->toBeNull();
    expect($user->discord_link_token)->toBeNull();
});

test('authenticated user can unlink discord', function () {
    $user = User::factory()->create([
        'discord_user_id' => '123456789012345678',
        'discord_username' => 'TestUser#1234',
        'discord_linked_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->delete('/profile/discord-link');

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->discord_user_id)->toBeNull();
    expect($user->discord_username)->toBeNull();
    expect($user->discord_linked_at)->toBeNull();
});
