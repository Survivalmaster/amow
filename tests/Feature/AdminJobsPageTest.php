<?php

use App\Models\GameJob;
use App\Models\Item;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

test('admin can view the redesigned jobs management page', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    GameJob::query()->create([
        'name' => 'Royal Advisor',
        'slug' => 'royal-advisor',
        'description' => 'Advises the crown.',
        'min_pay' => 50,
        'max_pay' => 120,
        'required_level' => 2,
        'work_cooldown_minutes' => 10,
        'stamina_decrease' => 15,
        'experience_reward' => 8,
        'working_display_message' => 'Advising the crown.',
        'is_starter' => false,
        'is_active' => true,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.jobs.index'))
        ->assertOk()
        ->assertSee('Admin: Jobs')
        ->assertSee('Old Jobs')
        ->assertSee('New Jobs')
        ->assertSee('Royal Advisor')
        ->assertSee('Create Job')
        ->assertSee('Avg 85')
        ->assertSee('data-job-row', false);
});

test('admin can create job drops with visual rule fields', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    $item = Item::query()->create([
        'name' => 'Fresh Log',
        'slug' => 'fresh-log',
        'description' => 'A job reward material.',
        'type' => 'material',
        'icon_class' => 'fa-solid fa-tree',
        'is_buyable' => false,
        'price' => 1,
    ]);

    $this
        ->actingAs($admin)
        ->post(route('admin.jobs.store'), [
            'name' => 'Log Chopper',
            'slug' => 'log-chopper',
            'description' => 'Cuts timber for builders.',
            'min_pay' => 10,
            'max_pay' => 20,
            'required_level' => 0,
            'work_cooldown_minutes' => 5,
            'stamina_decrease' => 10,
            'experience_reward' => 6,
            'max_tier' => 20,
            'tier_xp_required' => 100,
            'tier_pay_bonus_percent' => 5,
            'tier_xp_bonus_percent' => 5,
            'is_active' => 1,
            'is_new' => 1,
            'drop_rules' => [
                [
                    'item_id' => $item->id,
                    'min_tier' => 1,
                    'max_tier' => 8,
                    'min_quantity' => 1,
                    'max_quantity' => 3,
                    'drop_chance_percent' => 100,
                ],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $job = GameJob::query()->where('slug', 'log-chopper')->firstOrFail();

    expect($job->drops()->count())->toBe(1);
    expect($job->drops()->first()->only(['item_id', 'min_tier', 'max_tier', 'min_quantity', 'max_quantity']))
        ->toMatchArray([
            'item_id' => $item->id,
            'min_tier' => 1,
            'max_tier' => 8,
            'min_quantity' => 1,
            'max_quantity' => 3,
        ]);
});
