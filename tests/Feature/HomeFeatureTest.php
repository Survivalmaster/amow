<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Item;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\ItemSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        GameJobSeeder::class,
        ItemSeeder::class,
    ]);
});

function createHomeCharacter(User $user): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Shelter Tester',
        'age' => 27,
        'biography' => 'Testing home access.',
        'starting_occupation' => 'Begger',
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
        'plastic_credits' => 500,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => 0,
        'experience_points' => 0,
    ]);
}

test('home page is unavailable without a home item', function () {
    $user = User::factory()->create();
    createHomeCharacter($user);

    $this->actingAs($user)
        ->get(route('home.index'))
        ->assertNotFound();
});

test('home page is available when character owns a home item', function () {
    $user = User::factory()->create();
    $character = createHomeCharacter($user);
    $homeItem = Item::query()->where('is_home', true)->firstOrFail();

    $character->inventory()->attach($homeItem->id, ['quantity' => 1]);

    $this->actingAs($user)
        ->get(route('home.index'))
        ->assertOk()
        ->assertSee('Home Base')
        ->assertSee($homeItem->name);
});
