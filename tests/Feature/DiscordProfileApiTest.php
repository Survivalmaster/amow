<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\Rank;
use App\Models\User;

test('discord profile api returns linked character data', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    $faction = Faction::query()->create([
        'name' => 'Tan Army',
        'slug' => 'tan-army',
        'short_description' => 'Plastic infantry command.',
    ]);
    $rank = Rank::query()->create([
        'name' => 'Recruit',
        'order_index' => 1,
        'is_military' => true,
    ]);
    $user = User::factory()->create([
        'discord_user_id' => '123456789012345678',
        'discord_username' => 'TestUser#1234',
    ]);

    Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => $faction->id,
        'rank_id' => $rank->id,
        'name' => 'Sarge',
        'age' => 24,
        'biography' => 'Plastic veteran.',
        'starting_occupation' => 'Mechanic',
        'plastic_credits' => 250,
        'health_points' => 100,
        'armor_points' => 15,
        'role_type' => 'military',
        'is_business_owner' => false,
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->getJson('/api/discord/profile/123456789012345678');

    $response
        ->assertOk()
        ->assertJson([
            'linked' => true,
            'user' => [
                'name' => $user->name,
                'discord_username' => 'TestUser#1234',
            ],
            'character' => [
                'name' => 'Sarge',
                'faction' => 'Tan Army',
                'rank' => 'Recruit',
                'credits' => 250,
            ],
        ]);
});

test('discord profile api returns not found for unlinked user', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->getJson('/api/discord/profile/000000000000000000');

    $response
        ->assertNotFound()
        ->assertJson([
            'linked' => false,
        ]);
});
