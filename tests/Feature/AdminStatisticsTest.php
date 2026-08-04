<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\MapHex;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        PermissionSeeder::class,
    ]);
});

test('admins can view the live statistics page', function () {
    $admin = statisticsAdmin();
    $character = statisticsCharacter(User::factory()->create(), [
        'plastic_credits' => 750,
    ]);

    Transaction::query()->create([
        'character_id' => $character->id,
        'type' => 'work',
        'amount' => 125,
        'description' => 'Worked a shift.',
    ]);

    MapHex::factory()->create([
        'faction_id' => $character->faction_id,
        'tile_type' => MapHex::TYPE_CLAIMABLE,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.statistics.index'))
        ->assertOk()
        ->assertSee('Statistics')
        ->assertSee('Updates every 5 seconds')
        ->assertSee('data-admin-statistics', false);
});

test('the statistics state endpoint returns dashboard metrics', function () {
    $admin = statisticsAdmin();
    $character = statisticsCharacter(User::factory()->create(), [
        'plastic_credits' => 400,
    ]);

    Transaction::query()->create([
        'character_id' => $character->id,
        'type' => 'player_transfer_received',
        'amount' => 50,
        'description' => 'Received credits.',
    ]);

    $this
        ->actingAs($admin)
        ->getJson(route('admin.statistics.state'))
        ->assertOk()
        ->assertJsonStructure([
            'generated_at',
            'summary' => [['label', 'value', 'icon', 'tone']],
            'activity' => [['label', 'transactions', 'users', 'characters', 'messages']],
            'economy' => ['earned', 'spent', 'work_earned', 'refunds', 'marketplace_spend', 'stock_volume', 'bank_transfers', 'nation_donations'],
            'world' => [['label', 'value', 'icon']],
            'factions' => [['label', 'color', 'characters', 'bank', 'territory', 'credits']],
            'territory' => ['total', 'claimed', 'claimed_percent', 'types'],
            'content' => [['label', 'value', 'icon']],
        ])
        ->assertJsonPath('economy.earned', 50);
});

function statisticsAdmin(): User
{
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    return $admin;
}

function statisticsCharacter(User $user, array $overrides = []): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Stats Tester',
        'age' => 25,
        'biography' => 'Testing statistics.',
        'starting_occupation' => 'Laborer',
        'plastic_credits' => 100,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'level' => 0,
        'experience_points' => 0,
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        ...$overrides,
    ]);
}
