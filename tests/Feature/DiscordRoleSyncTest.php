<?php

use App\Models\DiscordRole;
use App\Models\DiscordRoleCategory;
use App\Models\Permission;
use App\Models\User;

test('discord bot can sync roles and role members', function () {
    config()->set('services.discord.bot_sync_secret', 'sync-secret');

    $response = $this
        ->withHeader('X-Discord-Sync-Secret', 'sync-secret')
        ->postJson('/api/discord/roles/sync', [
            'guild_id' => '805822469012586497',
            'roles' => [
                [
                    'id' => '100',
                    'name' => 'Nation Leader',
                    'color' => '#7EAD59',
                    'position' => 12,
                    'managed' => false,
                    'members' => [
                        [
                            'id' => '200',
                            'username' => 'leader#0001',
                            'display_name' => 'Leader',
                            'avatar_url' => 'https://cdn.discordapp.com/avatars/200/avatar.png',
                            'joined_at' => '2026-07-17T12:00:00.000Z',
                        ],
                    ],
                ],
            ],
        ]);

    $response
        ->assertOk()
        ->assertJson([
            'synced' => true,
            'role_count' => 1,
            'member_assignment_count' => 1,
        ]);

    $role = DiscordRole::query()->with('members')->firstOrFail();

    expect($role->discord_id)->toBe('100');
    expect($role->name)->toBe('Nation Leader');
    expect($role->member_count)->toBe(1);
    expect($role->members)->toHaveCount(1);
    expect($role->members->first()->display_name)->toBe('Leader');
});

test('admin can view synced discord management page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $permission = Permission::query()->where('slug', 'admin')->firstOrFail();
    $admin->permissions()->attach($permission);

    $role = DiscordRole::query()->create([
        'discord_id' => '100',
        'name' => 'Nation Leader',
        'color' => '#7EAD59',
        'position' => 12,
        'is_managed' => false,
        'member_count' => 1,
        'synced_at' => now(),
    ]);

    $role->members()->create([
        'discord_user_id' => '200',
        'username' => 'leader#0001',
        'display_name' => 'Leader',
        'synced_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get('/admin/discord-management');

    $response->assertOk();
    $response->assertSee('Discord Management');
    $response->assertSee('Nations &amp; Ranks', false);
    $response->assertSee('Nation Leader');
    $response->assertSee('Leader');
});

test('admin can manually change a discord role category', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $permission = Permission::query()->where('slug', 'admin')->firstOrFail();
    $admin->permissions()->attach($permission);

    $role = DiscordRole::query()->create([
        'discord_id' => '100',
        'name' => 'Nation Leader',
        'color' => '#7EAD59',
        'position' => 12,
        'is_managed' => false,
        'member_count' => 1,
        'synced_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch("/admin/discord-management/roles/{$role->id}/category", [
            'category' => 'staff',
        ]);

    $response->assertRedirect();

    expect($role->fresh()->category)->toBe('staff');

    config()->set('services.discord.bot_sync_secret', 'sync-secret');

    $this
        ->withHeader('X-Discord-Sync-Secret', 'sync-secret')
        ->postJson('/api/discord/roles/sync', [
            'guild_id' => '805822469012586497',
            'roles' => [
                [
                    'id' => '100',
                    'name' => 'Nation Leader',
                    'color' => '#7EAD59',
                    'position' => 12,
                    'managed' => false,
                    'members' => [],
                ],
            ],
        ])
        ->assertOk();

    expect($role->fresh()->category)->toBe('staff');
});

test('admin can create update and delete discord role categories', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $permission = Permission::query()->where('slug', 'admin')->firstOrFail();
    $admin->permissions()->attach($permission);

    $response = $this
        ->actingAs($admin)
        ->post('/admin/discord-management/categories', [
            'name' => 'Leadership Roles',
            'description' => 'Roles used by command staff.',
            'sort_order' => 15,
        ]);

    $response->assertRedirect();

    $category = DiscordRoleCategory::query()->where('slug', 'leadership-roles')->firstOrFail();

    expect($category->name)->toBe('Leadership Roles');
    expect($category->description)->toBe('Roles used by command staff.');
    expect($category->sort_order)->toBe(15);

    $role = DiscordRole::query()->create([
        'discord_id' => '100',
        'name' => 'General',
        'color' => '#7EAD59',
        'position' => 12,
        'is_managed' => false,
        'category' => $category->slug,
        'member_count' => 1,
        'synced_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->patch("/admin/discord-management/categories/{$category->id}", [
            'name' => 'Command Roles',
            'description' => 'Ranks used by command staff.',
            'sort_order' => 18,
        ]);

    $response->assertRedirect();

    $category->refresh();

    expect($category->slug)->toBe('leadership-roles');
    expect($category->name)->toBe('Command Roles');
    expect($category->description)->toBe('Ranks used by command staff.');
    expect($category->sort_order)->toBe(18);

    $response = $this
        ->actingAs($admin)
        ->delete("/admin/discord-management/categories/{$category->id}");

    $response->assertRedirect();

    expect(DiscordRoleCategory::query()->whereKey($category->id)->exists())->toBeFalse();
    expect($role->fresh()->category)->toBeNull();
});

test('admin can view nation roster ordered by rank roles', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $permission = Permission::query()->where('slug', 'admin')->firstOrFail();
    $admin->permissions()->attach($permission);

    DiscordRoleCategory::query()->create([
        'name' => 'Nation Roles',
        'slug' => 'nation-roles',
        'description' => 'Nation membership roles.',
        'sort_order' => 10,
    ]);

    DiscordRoleCategory::query()->create([
        'name' => 'Rank Roles',
        'slug' => 'rank-roles',
        'description' => 'Nation rank roles.',
        'sort_order' => 20,
    ]);

    $green = DiscordRole::query()->create([
        'discord_id' => 'green',
        'name' => 'Green',
        'color' => '#00ff66',
        'position' => 43,
        'is_managed' => false,
        'category' => 'nation-roles',
        'member_count' => 2,
        'synced_at' => now(),
    ]);

    $greenLeadership = DiscordRole::query()->create([
        'discord_id' => 'green-leadership',
        'name' => 'Green Nation Leadership',
        'color' => '#00ff66',
        'position' => 44,
        'is_managed' => false,
        'category' => 'nation-roles',
        'member_count' => 1,
        'synced_at' => now(),
    ]);

    $general = DiscordRole::query()->create([
        'discord_id' => 'general',
        'name' => 'General',
        'color' => '#00ff66',
        'position' => 60,
        'is_managed' => false,
        'category' => 'rank-roles',
        'member_count' => 1,
        'synced_at' => now(),
    ]);

    $private = DiscordRole::query()->create([
        'discord_id' => 'private',
        'name' => 'Private',
        'color' => '#00ff66',
        'position' => 20,
        'is_managed' => false,
        'category' => 'rank-roles',
        'member_count' => 1,
        'synced_at' => now(),
    ]);

    $green->members()->create([
        'discord_user_id' => '200',
        'username' => 'general_user',
        'display_name' => 'General User',
        'synced_at' => now(),
    ]);

    $green->members()->create([
        'discord_user_id' => '300',
        'username' => 'private_user',
        'display_name' => 'Private User',
        'synced_at' => now(),
    ]);

    $greenLeadership->members()->create([
        'discord_user_id' => '400',
        'username' => 'leader_user',
        'display_name' => 'Leader User',
        'synced_at' => now(),
    ]);

    $general->members()->create([
        'discord_user_id' => '200',
        'username' => 'general_user',
        'display_name' => 'General User',
        'synced_at' => now(),
    ]);

    $private->members()->create([
        'discord_user_id' => '300',
        'username' => 'private_user',
        'display_name' => 'Private User',
        'synced_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->get('/admin/discord-management/roster');

    $response->assertOk();
    $response->assertSee('Discord Roster');
    $response->assertSee('Pick one nation');
    $response->assertSee('Green');
    $response->assertDontSee('Green Nation Leadership');
    $response->assertSee('Leader User');
    $response->assertSeeInOrder(['General', 'General User', 'Private', 'Private User']);
});
