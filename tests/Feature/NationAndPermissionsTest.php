<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\NationRequisition;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        GameJobSeeder::class,
        PermissionSeeder::class,
    ]);
});

function createNationCharacter(User $user, array $overrides = []): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Nation Tester',
        'age' => 27,
        'biography' => 'Testing nation systems.',
        'starting_occupation' => 'Laborer',
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
        'plastic_credits' => 500,
        'rank_id' => Rank::query()->where('name', 'Recruit')->firstOrFail()->id,
        'role_type' => 'military',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => 1,
        'experience_points' => 0,
        'is_nation_leader' => false,
        ...$overrides,
    ]);
}

test('developer permission can access permissions admin section without overview access', function () {
    $user = User::factory()->create();
    $developerPermission = Permission::query()->where('slug', 'developer')->firstOrFail();
    $user->permissions()->attach($developerPermission);

    $this->actingAs($user)
        ->get(route('admin.permissions.index'))
        ->assertOk()
        ->assertSee('Admin: Permissions');

    $this->actingAs($user)
        ->get(route('admin.overview.state'))
        ->assertForbidden();
});

test('nation leader can only submit one outstanding requisition at a time', function () {
    $user = User::factory()->create();
    $character = createNationCharacter($user, ['is_nation_leader' => true]);
    $user->permissions()->attach(Permission::query()->where('slug', 'nation-leader')->firstOrFail());

    $this->actingAs($user)
        ->post(route('nation.requisitions.store'), [
            'title' => 'Supply Crate',
            'details' => 'Requesting supplies for the front.',
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('nation.requisitions.store'), [
            'title' => 'Second Request',
            'details' => 'This should be blocked.',
        ])
        ->assertSessionHasErrors('requisition');

    expect(NationRequisition::query()->where('faction_id', $character->faction_id)->count())->toBe(1);
});
