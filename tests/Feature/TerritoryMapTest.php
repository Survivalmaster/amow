<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\MapHex;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        PermissionSeeder::class,
        RankSeeder::class,
    ]);
});

test('the territory map page loads', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $user->permissions()->attach([
        Permission::query()->where('slug', 'admin')->firstOrFail()->id,
        Permission::query()->where('slug', 'developer')->firstOrFail()->id,
    ]);
    createTerritoryCharacter($user);

    $this
        ->actingAs($user)
        ->get(route('territory-map.index'))
        ->assertOk()
        ->assertSee('World of Plastica')
        ->assertSee('territory-map-app');
});

test('non developers see world of plastica as coming soon and cannot access it directly', function () {
    $user = User::factory()->create();
    createTerritoryCharacter($user);

    $this
        ->actingAs($user)
        ->get(route('lobby'))
        ->assertOk()
        ->assertSee('World of Plastica')
        ->assertSee('Coming Soon')
        ->assertDontSee(route('territory-map.index'), false);

    $this
        ->actingAs($user)
        ->get(route('territory-map.index'))
        ->assertForbidden();
});

test('the hex api returns valid map data', function () {
    $user = User::factory()->create();
    createTerritoryCharacter($user);
    $hex = MapHex::factory()->create();

    $this
        ->actingAs($user)
        ->getJson(route('api.map.hexes.index'))
        ->assertOk()
        ->assertJsonPath('data.0.id', $hex->id)
        ->assertJsonPath('data.0.polygon_coordinates.0.x', 100);
});

test('an unauthorised user cannot update a tile', function () {
    $user = User::factory()->create();
    createTerritoryCharacter($user);
    $hex = MapHex::factory()->create();

    $this
        ->actingAs($user)
        ->patchJson(route('api.map.hexes.update', $hex), ['tile_type' => MapHex::TYPE_WATER])
        ->assertForbidden();
});

test('an authorised user can update a tile', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    createTerritoryCharacter($admin);
    $hex = MapHex::factory()->create(['tile_type' => MapHex::TYPE_INACTIVE]);

    $this
        ->actingAs($admin)
        ->patchJson(route('api.map.hexes.update', $hex), [
            'tile_type' => MapHex::TYPE_CLAIMABLE,
            'terrain_type' => 'forest',
            'is_visible' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.tile_type', MapHex::TYPE_CLAIMABLE)
        ->assertJsonPath('data.terrain_type', 'forest');
});

test('an authorised user can update a tile through the post fallback', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    createTerritoryCharacter($admin);
    $faction = Faction::query()->firstOrCreate(['slug' => 'post-update-faction'], ['name' => 'Post Update Faction', 'short_description' => 'Updates.', 'color' => '#3478c5']);
    $hex = MapHex::factory()->create(['tile_type' => MapHex::TYPE_CLAIMABLE, 'is_visible' => true]);

    $this
        ->actingAs($admin)
        ->postJson(route('api.map.hexes.update.post', $hex), [
            'tile_type' => MapHex::TYPE_CLAIMABLE,
            'terrain_type' => 'coastal city',
            'faction_id' => $faction->id,
            'claim_strength' => 42,
            'is_visible' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.terrain_type', 'coastal city')
        ->assertJsonPath('data.faction.id', $faction->id)
        ->assertJsonPath('data.claim_strength', 42);

    expect($hex->fresh())
        ->terrain_type->toBe('coastal city')
        ->faction_id->toBe($faction->id)
        ->claim_strength->toBe(42);
});

test('a claim can be removed through the post fallback', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    createTerritoryCharacter($admin);
    $faction = Faction::query()->firstOrCreate(['slug' => 'post-remove-faction'], ['name' => 'Post Remove Faction', 'short_description' => 'Removes.', 'color' => '#3478c5']);
    $hex = MapHex::factory()->create([
        'tile_type' => MapHex::TYPE_CLAIMABLE,
        'is_visible' => true,
        'faction_id' => $faction->id,
        'claim_strength' => 10,
        'claimed_at' => now(),
    ]);

    $this
        ->actingAs($admin)
        ->postJson(route('api.map.hexes.claim.destroy.post', $hex))
        ->assertOk()
        ->assertJsonPath('data.faction', null)
        ->assertJsonPath('data.claim_strength', 0);

    expect($hex->fresh())
        ->faction_id->toBeNull()
        ->claim_strength->toBe(0)
        ->claimed_at->toBeNull();
});

test('a claimable tile can be claimed and removed', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    createTerritoryCharacter($admin);
    $faction = Faction::query()->firstOrCreate(['slug' => 'claim-faction'], ['name' => 'Claim Faction', 'short_description' => 'Claims.', 'color' => '#3478c5']);
    $hex = MapHex::factory()->create(['tile_type' => MapHex::TYPE_CLAIMABLE, 'is_visible' => true]);

    $this
        ->actingAs($admin)
        ->postJson(route('api.map.hexes.claim', $hex), ['faction_id' => $faction->id])
        ->assertOk()
        ->assertJsonPath('data.faction.id', $faction->id);

    expect($hex->fresh()->faction_id)->toBe($faction->id);

    $this
        ->actingAs($admin)
        ->deleteJson(route('api.map.hexes.claim.destroy', $hex))
        ->assertOk()
        ->assertJsonPath('data.faction', null);
});

test('water and blocked tiles cannot be claimed', function (string $tileType) {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    createTerritoryCharacter($admin);
    $faction = Faction::query()->firstOrCreate(['slug' => 'blocked-claim-faction'], ['name' => 'Blocked Claim Faction', 'short_description' => 'Claims.', 'color' => '#3478c5']);
    $hex = MapHex::factory()->create(['tile_type' => $tileType, 'is_visible' => true]);

    $this
        ->actingAs($admin)
        ->postJson(route('api.map.hexes.claim', $hex), ['faction_id' => $faction->id])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Only visible claimable land can be claimed.');
})->with([MapHex::TYPE_WATER, MapHex::TYPE_BLOCKED]);

test('faction deletion is prevented while it owns tiles', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $faction = Faction::query()->firstOrCreate(['slug' => 'owned-faction'], ['name' => 'Owned Faction', 'short_description' => 'Owns.', 'color' => '#3478c5']);
    MapHex::factory()->create(['faction_id' => $faction->id]);

    $this
        ->actingAs($admin)
        ->delete(route('admin.factions.destroy', $faction))
        ->assertSessionHasErrors();

    expect($faction->fresh())->not->toBeNull();
});

test('polygon coordinates are cast and returned', function () {
    $user = User::factory()->create();
    createTerritoryCharacter($user);
    $hex = MapHex::factory()->create([
        'polygon_coordinates' => [['x' => 10.5, 'y' => 20.25]],
    ]);

    expect($hex->fresh()->polygon_coordinates)->toBe([['x' => 10.5, 'y' => 20.25]]);

    $this
        ->actingAs($user)
        ->getJson(route('api.map.hexes.show', $hex))
        ->assertOk()
        ->assertJsonPath('data.polygon_coordinates.0.x', 10.5);
});

test('regenerating the grid preserves edited tile data', function () {
    $this
        ->artisan('map:generate-hexes', [
            '--width' => 120,
            '--height' => 120,
            '--radius' => 12,
            '--fresh' => true,
        ])
        ->assertSuccessful();

    $hex = MapHex::query()->firstOrFail();
    $faction = Faction::query()->firstOrCreate(
        ['slug' => 'preserved-faction'],
        ['name' => 'Preserved Faction', 'short_description' => 'Preserved.', 'color' => '#3478c5']
    );

    $hex->update([
        'tile_type' => MapHex::TYPE_BLOCKED,
        'terrain_type' => 'mountain pass',
        'faction_id' => $faction->id,
        'claim_strength' => 77,
        'is_visible' => false,
        'claimed_at' => now(),
    ]);

    $this
        ->artisan('map:generate-hexes', [
            '--width' => 120,
            '--height' => 120,
            '--radius' => 12,
        ])
        ->assertSuccessful();

    expect($hex->fresh())
        ->tile_type->toBe(MapHex::TYPE_BLOCKED)
        ->terrain_type->toBe('mountain pass')
        ->faction_id->toBe($faction->id)
        ->claim_strength->toBe(77)
        ->is_visible->toBeFalse()
        ->claimed_at->not->toBeNull();
});

function createTerritoryCharacter(User $user): Character
{
    $faction = Faction::query()->firstOrCreate(
        ['slug' => 'territory-green'],
        ['name' => 'Territory Green', 'short_description' => 'Territory test.', 'color' => '#3d8b4e']
    );

    $job = GameJob::query()->firstOrCreate(
        ['slug' => 'territory-begger'],
        [
            'name' => 'Territory Begger',
            'description' => 'Starter job.',
            'min_pay' => 10,
            'max_pay' => 30,
            'required_level' => 0,
            'work_cooldown_minutes' => 5,
            'stamina_decrease' => 10,
            'experience_reward' => 5,
            'working_display_message' => 'Begging in the city.',
            'is_starter' => true,
            'is_active' => true,
        ]
    );

    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => $faction->id,
        'name' => 'Territory Tester',
        'age' => 30,
        'biography' => 'A test character.',
        'starting_occupation' => $job->name,
        'current_job_id' => $job->id,
        'plastic_credits' => 100,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'level' => 0,
        'experience_points' => 0,
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
    ]);
}
