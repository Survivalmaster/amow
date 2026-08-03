<?php

use App\Models\Character;
use App\Models\DiscordRole;
use App\Models\DiscordRoleCategory;
use App\Models\Faction;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\RankSeeder;

test('admin characters sync linked character ranks from discord roles', function () {
    $this->seed(RankSeeder::class);

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $faction = Faction::query()->create([
        'name' => 'Green',
        'slug' => 'green',
        'short_description' => 'Green nation.',
    ]);

    DiscordRoleCategory::query()->create([
        'name' => 'Rank Roles',
        'slug' => 'rank-roles',
        'description' => 'Nation rank roles.',
        'sort_order' => 20,
    ]);

    $generalRole = DiscordRole::query()->create([
        'discord_id' => 'general',
        'name' => 'General',
        'color' => '#00ff66',
        'position' => 60,
        'is_managed' => false,
        'category' => 'rank-roles',
        'member_count' => 1,
        'synced_at' => now(),
    ]);

    $linkedGeneralUser = User::factory()->create([
        'discord_user_id' => '200',
        'discord_linked_at' => now(),
    ]);

    $linkedUnrankedUser = User::factory()->create([
        'discord_user_id' => '300',
        'discord_linked_at' => now(),
    ]);

    $unlinkedUser = User::factory()->create();

    $linkedGeneral = createAdminCharacter($linkedGeneralUser, $faction, Rank::query()->where('name', 'Civilian')->firstOrFail());
    $linkedUnranked = createAdminCharacter($linkedUnrankedUser, $faction, Rank::query()->where('name', 'Private')->firstOrFail());
    $unlinked = createAdminCharacter($unlinkedUser, $faction, Rank::query()->where('name', 'Major')->firstOrFail());

    $generalRole->members()->create([
        'discord_user_id' => '200',
        'username' => 'general_user',
        'display_name' => 'General User',
        'synced_at' => now(),
    ]);

    $this
        ->actingAs($admin)
        ->get('/admin/characters')
        ->assertOk()
        ->assertSee('Admin: Characters');

    expect($linkedGeneral->fresh()->rank->name)->toBe('General');
    expect($linkedGeneral->fresh()->role_type)->toBe('military');
    expect($linkedUnranked->fresh()->rank->name)->toBe('Civilian');
    expect($linkedUnranked->fresh()->role_type)->toBe('civilian');
    expect($unlinked->fresh()->rank->name)->toBe('Major');
});

function createAdminCharacter(User $user, Faction $faction, Rank $rank): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => $faction->id,
        'name' => $user->name,
        'age' => 30,
        'biography' => 'A test character.',
        'starting_occupation' => 'Laborer',
        'plastic_credits' => 100,
        'rank_id' => $rank->id,
        'role_type' => $rank->is_military ? 'military' : 'civilian',
    ]);
}
