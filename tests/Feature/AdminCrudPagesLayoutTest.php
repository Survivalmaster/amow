<?php

use App\Models\Character;
use App\Models\City;
use App\Models\Faction;
use App\Models\GameEvent;
use App\Models\GameJob;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        PermissionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
    ]);
});

test('main admin crud pages render with compact management controls', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $faction = Faction::query()->create([
        'name' => 'Green',
        'slug' => 'green',
        'short_description' => 'Green nation.',
    ]);

    $city = City::query()->create([
        'faction_id' => $faction->id,
        'name' => 'Green City',
        'slug' => 'green-city',
        'description' => 'A city.',
        'map_x' => 50,
        'map_y' => 50,
    ]);

    Location::query()->create([
        'city_id' => $city->id,
        'name' => 'Parade Ground',
        'slug' => 'parade-ground',
        'description' => 'Training space.',
        'is_public' => true,
    ]);

    $job = GameJob::query()->create([
        'name' => 'Begger',
        'slug' => 'begger',
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
    ]);

    Character::query()->create([
        'user_id' => User::factory()->create()->id,
        'faction_id' => $faction->id,
        'name' => 'Searchable Character',
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

    GameEvent::query()->create([
        'created_by_user_id' => $admin->id,
        'title' => 'Community Week',
        'body' => 'A test event.',
        'is_enabled' => true,
        'ends_at' => now()->addDay(),
        'xp_multiplier_enabled' => true,
        'xp_multiplier' => 1.5,
        'credit_multiplier_enabled' => true,
        'credit_multiplier' => 1.5,
    ]);

    foreach ([
        route('admin.characters.index') => 'Character Logs',
        route('admin.users.index') => 'Search',
        route('admin.factions.index') => 'Create Faction',
        route('admin.cities.index') => 'Create City',
        route('admin.locations.index') => 'Create Location',
        route('admin.items.index') => 'Create Item',
        route('admin.skirmishes.index') => 'Create Skirmish',
        route('admin.changelogs.index') => 'Create Changelog',
        route('admin.units.index') => 'Create Unit',
        route('admin.permissions.index') => 'Create Permission',
        route('admin.server-tools.index') => 'Artisan',
        route('admin.game-master.index') => 'Create Event',
    ] as $route => $expectedText) {
        $this
            ->actingAs($admin)
            ->get($route)
            ->assertOk()
            ->assertSee('Search')
            ->assertSee($expectedText);
    }
});

test('admin permissions can save multiple admin sections', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $permission = Permission::query()->create([
        'name' => 'Section Manager',
        'slug' => 'section-manager',
        'description' => 'Limited admin access.',
        'grants_admin_access' => true,
        'admin_sections' => ['users'],
        'sort_order' => 50,
    ]);

    $this
        ->actingAs($admin)
        ->patch(route('admin.permissions.update', $permission), [
            'name' => 'Section Manager',
            'slug' => 'section-manager',
            'description' => 'Limited admin access.',
            'icon_value' => null,
            'icon_color' => null,
            'icon_tooltip' => null,
            'grants_admin_access' => '1',
            'admin_sections' => ['users', 'characters', 'jobs'],
            'sort_order' => 50,
        ])
        ->assertRedirect();

    expect($permission->fresh()->admin_sections)->toBe(['users', 'characters', 'jobs']);
});

test('game master events show work participation stats', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $faction = Faction::query()->create([
        'name' => 'Red',
        'slug' => 'red',
        'short_description' => 'Red nation.',
    ]);

    $job = GameJob::query()->create([
        'name' => 'Mechanic',
        'slug' => 'mechanic',
        'description' => 'Fixes things.',
        'min_pay' => 10,
        'max_pay' => 10,
        'required_level' => 0,
        'work_cooldown_minutes' => 5,
        'stamina_decrease' => 5,
        'experience_reward' => 8,
        'working_display_message' => 'Fixing things.',
        'is_active' => true,
    ]);

    $character = Character::query()->create([
        'user_id' => User::factory()->create()->id,
        'faction_id' => $faction->id,
        'name' => 'Event Worker',
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

    $event = GameEvent::query()->create([
        'created_by_user_id' => $admin->id,
        'title' => 'Community Week',
        'body' => 'A test event.',
        'is_enabled' => true,
        'ends_at' => now()->subDay(),
        'xp_multiplier_enabled' => true,
        'xp_multiplier' => 1.5,
        'credit_multiplier_enabled' => true,
        'credit_multiplier' => 1.5,
    ]);

    $character->transactions()->create([
        'type' => 'work',
        'amount' => 15,
        'description' => 'Completed a Mechanic shift.',
        'metadata' => [
            'xp_earned' => 12,
            'credit_multiplier_events' => [['id' => $event->id, 'name' => 'Community Week', 'multiplier' => 1.5]],
        ],
    ]);
    $character->transactions()->create([
        'type' => 'work',
        'amount' => 20,
        'description' => 'Completed another Mechanic shift.',
        'metadata' => [
            'xp_earned' => 16,
            'xp_multiplier_events' => [['name' => 'Community Week', 'multiplier' => 1.5]],
        ],
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.game-master.index'))
        ->assertOk()
        ->assertSee('Community Week')
        ->assertSee('Players:</span> 1', false)
        ->assertSee('Shifts:</span> 2', false)
        ->assertSee('Credits:</span> 35', false)
        ->assertSee('XP:</span> 28', false)
        ->assertSee('Event Worker x2');
});

test('server tools reject unavailable actions', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $this
        ->actingAs($admin)
        ->post(route('admin.server-tools.run'), [
            'section' => 'github',
            'action' => 'reset-hard',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('tools');
});
