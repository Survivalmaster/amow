<?php

use App\Actions\Characters\ChangeCharacterJob;
use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

test('admin can view a selected character activity timeline', function () {
    $this->seed([
        PermissionSeeder::class,
        RankSeeder::class,
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $character = createLoggedCharacter();
    $character->transactions()->create([
        'type' => 'work',
        'amount' => 75,
        'description' => 'Completed a Royal Advisor shift and earned 75 Plastic Credits.',
        'metadata' => [
            'job' => 'Royal Advisor',
            'xp_earned' => 8,
        ],
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.character-logs.index', ['character_id' => $character->id]))
        ->assertOk()
        ->assertSee('Character Logs')
        ->assertSee('Audit Table')
        ->assertSee($character->name)
        ->assertSee('Completed a Royal Advisor shift')
        ->assertSee('XP +8')
        ->assertSee('+75');
});

test('legacy work logs do not show unknown placeholders', function () {
    $this->seed([
        PermissionSeeder::class,
        RankSeeder::class,
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $character = createLoggedCharacter();
    $character->transactions()->create([
        'type' => 'work',
        'amount' => 50,
        'description' => 'Completed a work shift.',
        'metadata' => null,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.character-logs.index', ['character_id' => $character->id]))
        ->assertOk()
        ->assertSee('Legacy work log')
        ->assertDontSee('Lv ?')
        ->assertDontSee('Stamina ?');
});

test('admin character logs are available from singular and plural routes', function () {
    $this->seed([
        PermissionSeeder::class,
        RankSeeder::class,
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    createLoggedCharacter();

    $this
        ->actingAs($admin)
        ->get('/admin/character-log')
        ->assertOk()
        ->assertSee('Character Logs');

    $this
        ->actingAs($admin)
        ->get('/admin/character-logs')
        ->assertOk()
        ->assertSee('Character Logs');
});

test('changing jobs writes a character log entry', function () {
    $this->seed(RankSeeder::class);

    $character = createLoggedCharacter();
    $newJob = GameJob::query()->create([
        'name' => 'Royal Advisor',
        'slug' => 'royal-advisor',
        'description' => 'Advises the crown.',
        'min_pay' => 50,
        'max_pay' => 120,
        'required_level' => 0,
        'work_cooldown_minutes' => 10,
        'stamina_decrease' => 15,
        'experience_reward' => 8,
        'working_display_message' => 'Advising the crown.',
        'is_starter' => false,
        'is_active' => true,
    ]);

    app(ChangeCharacterJob::class)->execute($character, $newJob);

    $this->assertDatabaseHas('transactions', [
        'character_id' => $character->id,
        'type' => 'job_change',
        'amount' => 0,
        'description' => 'Changed job from Begger to Royal Advisor.',
    ]);
});

function createLoggedCharacter(): Character
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
        'user_id' => User::factory()->create()->id,
        'faction_id' => $faction->id,
        'name' => 'Loggable',
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
