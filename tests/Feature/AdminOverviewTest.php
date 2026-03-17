<?php

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed([
        PermissionSeeder::class,
    ]);
});

test('admin overview state shows online users and their current page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $viewer = User::factory()->create();
    $permission = Permission::query()->where('slug', 'admin')->firstOrFail();

    $admin->permissions()->attach($permission);

    $viewer->update([
        'last_seen_at' => now(),
        'current_path' => 'stocks',
        'current_page_name' => 'Market Index',
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.overview.state'))
        ->assertOk()
        ->assertJsonPath('online_count', 1)
        ->assertJsonPath('online_users.0.account_name', $viewer->name)
        ->assertJsonPath('online_users.0.current_page_name', 'Market Index')
        ->assertJsonPath('online_users.0.current_path', 'stocks');
});

test('presence ping stores the current page for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('presence.store'), [
            'current_path' => 'leaderboards',
            'current_page_name' => 'Leaderboards Index',
        ])
        ->assertOk()
        ->assertJsonPath('ok', true);

    $user->refresh();

    expect($user->current_path)->toBe('leaderboards');
    expect($user->current_page_name)->toBe('Leaderboards Index');
    expect($user->last_seen_at)->not->toBeNull();
});
