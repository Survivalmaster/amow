<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
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

test('non developer admins cannot see player emails in admin character areas', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $player = User::factory()->create([
        'name' => 'Visible Player',
        'email' => 'hidden-player@example.com',
    ]);
    $character = createEmailPrivacyCharacter($player);

    $this
        ->actingAs($admin)
        ->get(route('admin.characters.index'))
        ->assertOk()
        ->assertSee('Visible Player')
        ->assertDontSee('hidden-player@example.com');

    $this
        ->actingAs($admin)
        ->get(route('admin.character-logs.index', ['character_id' => $character->id]))
        ->assertOk()
        ->assertSee('Visible Player')
        ->assertDontSee('hidden-player@example.com');

    $this
        ->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('User #'.$player->id)
        ->assertDontSee('hidden-player@example.com');
});

test('developers can see player emails in admin character areas', function () {
    $developer = User::factory()->create(['is_admin' => true]);
    $developer->permissions()->attach([
        Permission::query()->where('slug', 'admin')->firstOrFail()->id,
        Permission::query()->where('slug', 'developer')->firstOrFail()->id,
    ]);

    $player = User::factory()->create([
        'email' => 'visible-player@example.com',
    ]);
    $character = createEmailPrivacyCharacter($player);

    $this
        ->actingAs($developer)
        ->get(route('admin.characters.index'))
        ->assertOk()
        ->assertSee('visible-player@example.com');

    $this
        ->actingAs($developer)
        ->get(route('admin.character-logs.index', ['character_id' => $character->id]))
        ->assertOk()
        ->assertSee('visible-player@example.com');

    $this
        ->actingAs($developer)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('visible-player@example.com');
});

test('non developer admins cannot change player emails', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $player = User::factory()->create([
        'name' => 'Editable Player',
        'email' => 'original@example.com',
    ]);

    $this
        ->actingAs($admin)
        ->patch(route('admin.users.update', $player), [
            'name' => 'Edited Player',
            'email' => 'changed@example.com',
        ])
        ->assertRedirect();

    expect($player->fresh()->email)->toBe('original@example.com');
    expect($player->fresh()->name)->toBe('Edited Player');
});

function createEmailPrivacyCharacter(User $user): Character
{
    $faction = Faction::query()->firstOrCreate(
        ['slug' => 'green'],
        ['name' => 'Green', 'short_description' => 'Green nation.']
    );

    $job = GameJob::query()->firstOrCreate(
        ['slug' => 'begger'],
        [
            'name' => 'Begger',
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
        'name' => 'Privacy Character',
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
