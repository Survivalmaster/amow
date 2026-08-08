<?php

use App\Models\Changelog;
use App\Models\Character;
use App\Models\Faction;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        PermissionSeeder::class,
        FactionSeeder::class,
        RankSeeder::class,
    ]);
});

function changelogPlayer(): User
{
    $user = User::factory()->create();

    Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Changelog Reader',
        'age' => 25,
        'biography' => 'Reads update notes.',
        'starting_occupation' => 'Laborer',
        'plastic_credits' => 100,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'level' => 0,
        'experience_points' => 0,
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
    ]);

    return $user;
}

test('players can only read released changelogs', function () {
    $released = Changelog::query()->create([
        'version' => 'v1.2.0',
        'title' => 'Transcript Tools',
        'summary' => 'A major Discord utility release.',
        'added_features' => ['HTML transcript exports'],
        'edited_features' => ['Admin changelog layout'],
        'removed_features' => ['Old feature textarea'],
        'body' => 'Staff can now export channel history.',
        'status' => 'released',
        'released_at' => now(),
    ]);

    Changelog::query()->create([
        'version' => 'v1.3.0',
        'title' => 'Hidden Draft',
        'status' => 'draft',
    ]);

    $this
        ->actingAs(changelogPlayer())
        ->get(route('changelogs.index'))
        ->assertOk()
        ->assertSee($released->title)
        ->assertSee('Added')
        ->assertSee('Edited')
        ->assertSee('Removed')
        ->assertSee('HTML transcript exports')
        ->assertSee('Admin changelog layout')
        ->assertSee('Old feature textarea')
        ->assertDontSee('Hidden Draft')
        ->assertDontSee('Create Changelog')
        ->assertDontSee('Delete');
});

test('admin releasing a changelog posts one discord embed', function () {
    config()->set('services.discord.bot_sync_secret', 'test-sync-secret');

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $this
        ->actingAs($admin)
        ->post(route('admin.changelogs.store'), [
            'discord_channel_id' => '123456789012345678',
            'version' => 'v2.0.0',
            'title' => 'Big Update',
            'summary' => 'A large release.',
            'added_features_text' => "Admin changelog manager\nDiscord release embeds",
            'edited_features_text' => "Cleaner changelog form",
            'removed_features_text' => "Single feature textarea",
            'body' => 'More supporting notes.',
            'released_at' => '2026-08-08T18:30',
        ])
        ->assertRedirect();

    $changelog = Changelog::query()->where('version', 'v2.0.0')->firstOrFail();

    expect($changelog->status)->toBe('draft');
    expect($changelog->released_at?->format('Y-m-d H:i'))->toBe('2026-08-08 18:30');
    expect($changelog->discord_message_sent_at)->toBeNull();

    $this
        ->actingAs($admin)
        ->post(route('admin.changelogs.publish', $changelog))
        ->assertRedirect();

    $changelog->refresh();

    expect($changelog->status)->toBe('released');
    expect($changelog->discord_message_sent_at)->toBeNull();

    $pending = $this
        ->withHeader('X-Discord-Sync-Secret', 'test-sync-secret')
        ->getJson(route('api.discord.changelogs.pending'))
        ->assertOk()
        ->json('changelogs.0');

    $embed = $pending['embed'];

    expect($pending['channel_id'])->toBe('123456789012345678');
    expect($embed['title'])->toBe('Big Update - Version v2.0.0');
    expect($embed['description'])->toContain('Added: 2');
    expect($embed['description'])->toContain('Edited: 1');
    expect($embed['description'])->toContain('Removed: 1');
    expect($embed['description'])->toContain(route('changelogs.index').'#changelog-'.$changelog->id);
    expect($embed['fields'][0]['name'])->toBe('✨ Added');
    expect($embed['fields'][0]['value'])->toContain('Admin changelog manager');
    expect($embed['fields'][0]['value'])->toContain('Discord release embeds');
    expect($embed['fields'][1]['name'])->toBe('🛠️ Edited');
    expect($embed['fields'][1]['value'])->toContain('Cleaner changelog form');
    expect($embed['fields'][2]['name'])->toBe('🗑️ Removed');
    expect($embed['fields'][2]['value'])->toContain('Single feature textarea');
    expect(collect($embed['fields'])->pluck('name'))->not->toContain('Summary');
    expect($embed)->not->toHaveKey('thumbnail');

    $this
        ->withHeader('X-Discord-Sync-Secret', 'test-sync-secret')
        ->postJson(route('api.discord.changelogs.sent', $changelog))
        ->assertOk();

    expect($changelog->fresh()->discord_message_sent_at)->not->toBeNull();

    $this
        ->actingAs($admin)
        ->patch(route('admin.changelogs.update', $changelog), [
            'discord_channel_id' => '123456789012345678',
            'version' => 'v2.0.0',
            'title' => 'Big Update Edited',
            'summary' => 'A large release.',
            'added_features_text' => "Admin changelog manager\nDiscord release embeds",
            'edited_features_text' => "Cleaner changelog form",
            'removed_features_text' => "Single feature textarea",
            'body' => 'More supporting notes.',
        ])
        ->assertRedirect();
});

test('next changelog version increments the last numeric segment', function (string $latest, string $expected) {
    expect(Changelog::nextVersion($latest))->toBe($expected);
})->with([
    ['0.0.1', '0.0.2'],
    ['0.1', '0.2'],
    ['1', '2'],
    ['v2', '3'],
    ['', '0.0.1'],
]);

test('new changelogs default to the latest used discord channel id', function () {
    Changelog::query()->create([
        'version' => '1',
        'title' => 'First',
        'discord_channel_id' => '111111111111111111',
    ]);

    Changelog::query()->create([
        'version' => '2',
        'title' => 'Second',
        'discord_channel_id' => '222222222222222222',
    ]);

    expect(Changelog::latestDiscordChannelId())->toBe('222222222222222222');
});
