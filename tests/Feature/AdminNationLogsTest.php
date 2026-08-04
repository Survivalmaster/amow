<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\NationRequisition;
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

test('admin can view nation donation logs as money in', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $character = createNationLogCharacter(User::factory()->create(['name' => 'Donor Player']));

    $this
        ->actingAs($character->user)
        ->post(route('nation.donate'), ['amount' => 125])
        ->assertRedirect();

    $this
        ->actingAs($admin)
        ->get(route('admin.nation-logs.index', ['faction_id' => $character->faction_id]))
        ->assertOk()
        ->assertSee('Nation Logs')
        ->assertSee($character->faction->name)
        ->assertSee('Donation')
        ->assertSee($character->name)
        ->assertSee('+125');
});

test('nation logs show withdrawal style transactions as money out', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $character = createNationLogCharacter();

    $character->transactions()->create([
        'type' => 'nation_withdrawal',
        'amount' => 75,
        'description' => 'Withdrew 75 Plastic Credits from the nation bank.',
        'metadata' => [
            'reason' => 'Approved supply payout.',
        ],
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.nation-logs.index', ['faction_id' => $character->faction_id]))
        ->assertOk()
        ->assertSee('Nation Withdrawal')
        ->assertSee('Approved supply payout.')
        ->assertSee('-75');
});

test('nation logs include requisition history', function () {
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Review Admin']);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $character = createNationLogCharacter();

    NationRequisition::query()->create([
        'faction_id' => $character->faction_id,
        'submitted_by_character_id' => $character->id,
        'title' => 'Medical Supplies',
        'details' => 'Requesting supplies.',
        'status' => NationRequisition::STATUS_ACCEPTED,
        'admin_reason' => 'Approved for field hospital.',
        'reviewed_by_user_id' => $admin->id,
        'reviewed_at' => now(),
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.nation-logs.index', ['faction_id' => $character->faction_id]))
        ->assertOk()
        ->assertSee('Requisition')
        ->assertSee('Medical Supplies')
        ->assertSee('Approved for field hospital.')
        ->assertSee('Reviewed by Review Admin');
});

test('non developer admins cannot see player emails in nation logs', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $player = User::factory()->create([
        'name' => 'Visible Nation Player',
        'email' => 'hidden-nation-player@example.com',
    ]);
    $character = createNationLogCharacter($player);

    $character->transactions()->create([
        'type' => 'nation_donation',
        'amount' => -50,
        'description' => 'Donated 50 Plastic Credits to the nation.',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.nation-logs.index', ['faction_id' => $character->faction_id]))
        ->assertOk()
        ->assertSee('Visible Nation Player')
        ->assertDontSee('hidden-nation-player@example.com');
});

function createNationLogCharacter(?User $user = null): Character
{
    $faction = Faction::query()->firstOrCreate(
        ['slug' => 'nation-log-green'],
        [
            'name' => 'Nation Log Green',
            'short_description' => 'Nation log test nation.',
            'nation_bank_credits' => 250,
        ]
    );

    $job = GameJob::query()->firstOrCreate(
        ['slug' => 'nation-log-begger'],
        [
            'name' => 'Nation Log Begger',
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
        'user_id' => ($user ?? User::factory()->create())->id,
        'faction_id' => $faction->id,
        'name' => 'Nation Logger',
        'age' => 30,
        'biography' => 'A test character.',
        'starting_occupation' => $job->name,
        'current_job_id' => $job->id,
        'plastic_credits' => 500,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'level' => 0,
        'experience_points' => 0,
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
    ]);
}
