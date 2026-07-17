<?php

use App\Models\DiscordRole;
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
    $response->assertSee('Nation Leader');
    $response->assertSee('Leader');
});
