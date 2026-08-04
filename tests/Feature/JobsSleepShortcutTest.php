<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Item;
use App\Models\Licence;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\ItemSeeder;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
        GameJobSeeder::class,
        ItemSeeder::class,
    ]);
});

function createJobsCharacter(User $user): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Jobs Sleeper',
        'age' => 28,
        'biography' => 'Testing sleep from work.',
        'starting_occupation' => 'Worker',
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
        'plastic_credits' => 500,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 45,
        'armor_points' => 0,
        'level' => 0,
        'experience_points' => 0,
    ]);
}

test('jobs page shows sleep shortcut when a sleep capable building is complete', function () {
    $user = User::factory()->create();
    $character = createJobsCharacter($user);
    $land = Licence::query()->where('slug', 'land')->firstOrFail();
    $tent = Item::query()->where('slug', 'salvaged-tent')->firstOrFail();

    $character->licences()->attach($land->id);
    $character->landBuildings()->create([
        'item_id' => $tent->id,
        'grid_x' => 1,
        'grid_y' => 1,
        'build_started_at' => now()->subHour(),
        'build_complete_at' => now()->subMinutes(30),
    ]);

    $this->actingAs($user)
        ->get(route('jobs.index'))
        ->assertOk()
        ->assertSee('Rest Shortcut')
        ->assertSee('Sleep at your Salvaged Tent');
});

test('sleeping requires a sleep capable completed building', function () {
    $user = User::factory()->create();
    $character = createJobsCharacter($user);
    $land = Licence::query()->where('slug', 'land')->firstOrFail();
    $building = Item::query()->create([
        'name' => 'Storage Shed',
        'slug' => 'storage-shed',
        'description' => 'Useful, but not restful.',
        'type' => 'building',
        'is_building' => true,
        'is_home' => false,
        'price' => 100,
    ]);

    $character->licences()->attach($land->id);
    $character->landBuildings()->create([
        'item_id' => $building->id,
        'grid_x' => 1,
        'grid_y' => 1,
        'build_started_at' => now()->subHour(),
        'build_complete_at' => now()->subMinutes(30),
    ]);

    $this->actingAs($user)
        ->post(route('home.sleep'))
        ->assertSessionHasErrors('home');

    expect($character->fresh()->stamina_points)->toBe(45);
});
