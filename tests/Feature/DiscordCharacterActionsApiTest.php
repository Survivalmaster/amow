<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Item;
use App\Models\Rank;
use App\Models\User;

function createLinkedDiscordCharacter(array $characterAttributes = []): Character
{
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

    return Character::query()->create(array_merge([
        'user_id' => $user->id,
        'faction_id' => $faction->id,
        'rank_id' => $rank->id,
        'name' => 'Sarge',
        'age' => 24,
        'biography' => 'Plastic veteran.',
        'starting_occupation' => 'Mechanic',
        'plastic_credits' => 250,
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 15,
        'level' => 1,
        'experience_points' => 0,
        'role_type' => 'military',
        'is_business_owner' => false,
    ], $characterAttributes));
}

test('discord bank api returns linked character balance', function () {
    config()->set('services.discord.linking_secret', 'test-secret');
    createLinkedDiscordCharacter(['plastic_credits' => 725]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->getJson('/api/discord/bank/123456789012345678');

    $response
        ->assertOk()
        ->assertJsonPath('linked', true)
        ->assertJsonPath('character.name', 'Sarge')
        ->assertJsonPath('character.credits', 725);
});

test('discord work api uses website work cooldown and rewards', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    $job = GameJob::query()->create([
        'name' => 'Scrap Runner',
        'slug' => 'scrap-runner',
        'description' => 'Collect useful scraps.',
        'min_pay' => 10,
        'max_pay' => 10,
        'required_level' => 0,
        'work_cooldown_minutes' => 5,
        'stamina_decrease' => 3,
        'is_starter' => true,
        'is_active' => true,
    ]);

    $character = createLinkedDiscordCharacter([
        'current_job_id' => $job->id,
        'plastic_credits' => 100,
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/work', [
            'discord_user_id' => '123456789012345678',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('worked', true)
        ->assertJsonPath('earnings', 10)
        ->assertJsonPath('character.credits', 110)
        ->assertJsonPath('character.stamina_points', 97);

    expect($character->fresh()->last_worked_at)->not->toBeNull();

    $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/work', [
            'discord_user_id' => '123456789012345678',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('worked', false);
});

test('discord jobs api lists and changes linked character job with cooldown', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    $starterJob = GameJob::query()->create([
        'name' => 'Scrap Runner',
        'slug' => 'scrap-runner',
        'description' => 'Collect useful scraps.',
        'min_pay' => 10,
        'max_pay' => 10,
        'required_level' => 0,
        'work_cooldown_minutes' => 5,
        'is_starter' => true,
        'is_active' => true,
    ]);
    $newJob = GameJob::query()->create([
        'name' => 'Factory Hand',
        'slug' => 'factory-hand',
        'description' => 'Assembly line labour.',
        'min_pay' => 25,
        'max_pay' => 40,
        'required_level' => 0,
        'work_cooldown_minutes' => 10,
        'is_active' => true,
    ]);
    $thirdJob = GameJob::query()->create([
        'name' => 'Courier',
        'slug' => 'courier',
        'description' => 'Fast deliveries.',
        'min_pay' => 22,
        'max_pay' => 40,
        'required_level' => 0,
        'work_cooldown_minutes' => 8,
        'is_active' => true,
    ]);
    GameJob::query()->create([
        'name' => 'Quartermaster',
        'slug' => 'quartermaster',
        'description' => 'Manage supplies.',
        'min_pay' => 80,
        'max_pay' => 120,
        'required_level' => 5,
        'work_cooldown_minutes' => 15,
        'is_active' => true,
    ]);

    $character = createLinkedDiscordCharacter([
        'current_job_id' => $starterJob->id,
        'level' => 1,
    ]);

    $jobsResponse = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->getJson('/api/discord/jobs/123456789012345678');

    $jobsResponse
        ->assertOk()
        ->assertJsonPath('character.current_job', 'Scrap Runner');

    $jobs = collect($jobsResponse->json('jobs'))->keyBy('id');
    expect($jobs[$starterJob->id]['is_current'])->toBeTrue();
    expect($jobs[$newJob->id]['can_choose'])->toBeTrue();
    expect($jobs->firstWhere('name', 'Quartermaster')['can_choose'])->toBeFalse();

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/jobs/change', [
            'discord_user_id' => '123456789012345678',
            'job_id' => $newJob->id,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('changed', true)
        ->assertJsonPath('character.current_job', 'Factory Hand');

    expect($character->fresh()->current_job_id)->toBe($newJob->id);
    expect($character->fresh()->job_changed_at)->not->toBeNull();

    $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/jobs/change', [
            'discord_user_id' => '123456789012345678',
            'job_id' => $thirdJob->id,
        ])
        ->assertUnprocessable()
        ->assertJsonPath('changed', false);
});

test('discord store purchase api buys item for linked character', function () {
    config()->set('services.discord.linking_secret', 'test-secret');

    $character = createLinkedDiscordCharacter(['plastic_credits' => 250]);

    $item = Item::query()->create([
        'name' => 'Field Pack',
        'slug' => 'field-pack',
        'description' => 'Carry more useful gear.',
        'type' => 'backpack',
        'price' => 75,
        'inventory_slot_bonus' => 4,
    ]);

    $response = $this
        ->withHeader('X-Discord-Link-Secret', 'test-secret')
        ->postJson('/api/discord/store/purchase', [
            'discord_user_id' => '123456789012345678',
            'purchase_type' => 'item',
            'id' => $item->id,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('purchased', true)
        ->assertJsonPath('purchase.name', 'Field Pack')
        ->assertJsonPath('character.credits', 175);

    expect($character->fresh()->inventory()->whereKey($item->id)->exists())->toBeTrue();
});
