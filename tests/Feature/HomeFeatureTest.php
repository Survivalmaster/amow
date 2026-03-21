<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Item;
use App\Models\Licence;
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

function createHomeCharacter(User $user): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Shelter Tester',
        'age' => 27,
        'biography' => 'Testing land access.',
        'starting_occupation' => 'Begger',
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
        'plastic_credits' => 500,
        'rank_id' => \App\Models\Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => 0,
        'experience_points' => 0,
    ]);
}

test('land page is unavailable without the land licence', function () {
    $user = User::factory()->create();
    createHomeCharacter($user);

    $this->actingAs($user)
        ->get(route('home.index'))
        ->assertNotFound();
});

test('land page is available when character owns the land licence', function () {
    $user = User::factory()->create();
    $character = createHomeCharacter($user);
    $land = Licence::query()->where('slug', 'land')->firstOrFail();

    $character->licences()->attach($land->id);

    $this->actingAs($user)
        ->get(route('home.index'))
        ->assertOk()
        ->assertSee('Personal Plot')
        ->assertSee('10 x 10');
});

test('placing a building consumes the item and adds it to land construction', function () {
    $user = User::factory()->create();
    $character = createHomeCharacter($user);
    $land = Licence::query()->where('slug', 'land')->firstOrFail();
    $tent = Item::query()->where('slug', 'salvaged-tent')->firstOrFail();

    $character->licences()->attach($land->id);
    $character->inventory()->attach($tent->id, ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('home.buildings.place'), [
            'item_id' => $tent->id,
            'grid_x' => 3,
            'grid_y' => 3,
        ])
        ->assertRedirect();

    expect($character->fresh()->landBuildings()->count())->toBe(1);
    expect($character->fresh()->inventory()->where('items.id', $tent->id)->exists())->toBeFalse();
});

test('sleeping on land restores stamina to full once a building is complete', function () {
    $user = User::factory()->create();
    $character = createHomeCharacter($user);
    $land = Licence::query()->where('slug', 'land')->firstOrFail();
    $tent = Item::query()->where('slug', 'salvaged-tent')->firstOrFail();

    $character->licences()->attach($land->id);
    $character->landBuildings()->create([
        'item_id' => $tent->id,
        'grid_x' => 1,
        'grid_y' => 1,
        'build_started_at' => now()->subMinutes(30),
        'build_complete_at' => now()->subMinutes(15),
    ]);
    $character->update(['stamina_points' => 42]);

    $this->actingAs($user)
        ->post(route('home.sleep'))
        ->assertRedirect();

    expect($character->fresh()->stamina_points)->toBe(100);
});

test('completed buildings can be moved and returned to inventory', function () {
    $user = User::factory()->create();
    $character = createHomeCharacter($user);
    $land = Licence::query()->where('slug', 'land')->firstOrFail();
    $tent = Item::query()->where('slug', 'salvaged-tent')->firstOrFail();

    $character->licences()->attach($land->id);
    $building = $character->landBuildings()->create([
        'item_id' => $tent->id,
        'grid_x' => 1,
        'grid_y' => 1,
        'build_started_at' => now()->subMinutes(30),
        'build_complete_at' => now()->subMinutes(15),
    ]);

    $this->actingAs($user)
        ->post(route('home.tiles.clear'), [
            'grid_x' => 4,
            'grid_y' => 5,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->patch(route('home.buildings.move', $building), [
            'grid_x' => 4,
            'grid_y' => 5,
        ])
        ->assertRedirect();

    $building->refresh();

    expect($building->grid_x)->toBe(4);
    expect($building->grid_y)->toBe(5);
    expect($building->build_complete_at?->isFuture())->toBeTrue();

    $this->actingAs($user)
        ->delete(route('home.buildings.destroy', $building))
        ->assertRedirect();

    expect($character->fresh()->landBuildings()->count())->toBe(0);
    expect((int) optional($character->fresh()->inventory()->where('items.id', $tent->id)->first())->pivot?->quantity)->toBe(1);
});

test('clearing blocked land tiles costs credits and price increases each time', function () {
    $user = User::factory()->create();
    $character = createHomeCharacter($user);
    $land = Licence::query()->where('slug', 'land')->firstOrFail();

    $character->licences()->attach($land->id);
    $character->update(['plastic_credits' => 1000]);

    $this->actingAs($user)
        ->get(route('home.index'))
        ->assertOk()
        ->assertSee('Next Clear: 75');

    $this->actingAs($user)
        ->post(route('home.tiles.clear'), [
            'grid_x' => 4,
            'grid_y' => 1,
        ])
        ->assertRedirect();

    expect($character->fresh()->plastic_credits)->toBe(925);

    $this->actingAs($user)
        ->get(route('home.index'))
        ->assertOk()
        ->assertSee('Next Clear: 110');

    $this->actingAs($user)
        ->post(route('home.tiles.clear'), [
            'grid_x' => 4,
            'grid_y' => 2,
        ])
        ->assertRedirect();

    expect($character->fresh()->plastic_credits)->toBe(815);
});

test('inventory page shows slot based inventory management', function () {
    $user = User::factory()->create();
    $character = createHomeCharacter($user);
    $backpack = Item::query()->where('slug', 'canvas-backpack')->firstOrFail();
    $building = Item::query()->where('slug', 'salvaged-tent')->firstOrFail();

    $character->inventory()->attach($backpack->id, ['quantity' => 1]);
    $character->inventory()->attach($building->id, ['quantity' => 1]);

    $this->actingAs($user)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSee('Inventory Grid')
        ->assertSee('20')
        ->assertSee($backpack->name)
        ->assertSee('Building');
});
