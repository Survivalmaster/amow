<?php

use App\Models\GameJob;
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
        ->assertSee('Royal Advisor')
        ->assertSee('Create Job')
        ->assertSee('Avg 85')
        ->assertSee('data-job-row', false);
});
