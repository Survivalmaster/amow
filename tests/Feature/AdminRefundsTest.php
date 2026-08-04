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

test('admin can issue audited xp and currency refunds', function () {
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Refund Admin']);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $character = createRefundTargetCharacter();

    $this
        ->actingAs($admin)
        ->post(route('admin.refunds.store'), [
            'character_id' => $character->id,
            'plastic_credits' => 250,
            'experience_points' => 500,
            'reason' => 'Rollback for missed event rewards.',
        ])
        ->assertRedirect(route('admin.refunds.index'));

    $character->refresh();

    expect($character->plastic_credits)->toBe(350);
    expect($character->level)->toBe(3);
    expect($character->experience_points)->toBe(50);

    $this->assertDatabaseHas('transactions', [
        'character_id' => $character->id,
        'type' => 'refund',
        'amount' => 250,
    ]);

    $transaction = $character->transactions()->where('type', 'refund')->firstOrFail();

    expect($transaction->metadata['refund_xp'])->toBe(500);
    expect($transaction->metadata['level_before'])->toBe(0);
    expect($transaction->metadata['level_after'])->toBe(3);
    expect($transaction->metadata['levels_gained'])->toBe(3);
    expect($transaction->metadata['reason'])->toBe('Rollback for missed event rewards.');
});

test('admin can view refunds page under core', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $character = createRefundTargetCharacter();

    $this
        ->actingAs($admin)
        ->get(route('admin.refunds.index'))
        ->assertOk()
        ->assertSee('Refunds')
        ->assertSee($character->name)
        ->assertSee('Issue Refund');
});

test('refunds are visible in admin character logs and player profile', function () {
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Refund Admin']);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $character = createRefundTargetCharacter();

    $this->actingAs($admin)->post(route('admin.refunds.store'), [
        'character_id' => $character->id,
        'plastic_credits' => 0,
        'experience_points' => 120,
        'reason' => 'Compensation for a bugged job payout.',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.character-logs.index', ['character_id' => $character->id]))
        ->assertOk()
        ->assertSee('Refund')
        ->assertSee('Compensation for a bugged job payout.')
        ->assertSee('XP +120')
        ->assertSee('Issued by Refund Admin');

    $this
        ->actingAs($character->user)
        ->get(route('characters.show'))
        ->assertOk()
        ->assertSee('refund')
        ->assertSee('Compensation for a bugged job payout.')
        ->assertSee('XP +120');
});

test('refunds require xp or credits', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $character = createRefundTargetCharacter();

    $this
        ->actingAs($admin)
        ->post(route('admin.refunds.store'), [
            'character_id' => $character->id,
            'plastic_credits' => 0,
            'experience_points' => 0,
            'reason' => 'No amount.',
        ])
        ->assertSessionHasErrors('refund');
});

function createRefundTargetCharacter(): Character
{
    $faction = Faction::query()->firstOrCreate(
        ['slug' => 'refund-green'],
        ['name' => 'Refund Green', 'short_description' => 'Refund test nation.']
    );

    $job = GameJob::query()->firstOrCreate(
        ['slug' => 'refund-begger'],
        [
            'name' => 'Refund Begger',
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
        'user_id' => User::factory()->create()->id,
        'faction_id' => $faction->id,
        'name' => 'Refundable',
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
