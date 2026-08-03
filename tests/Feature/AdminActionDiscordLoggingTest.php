<?php

use App\Models\Faction;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed([
        PermissionSeeder::class,
    ]);
});

test('admin create update and delete actions are logged to discord', function () {
    config()->set('services.discord.bot_token', 'test-token');
    config()->set('services.discord.admin_audit_enabled', true);

    Http::fake([
        'https://discord.com/api/v10/channels/1483335218944282685/messages' => Http::response(['id' => '1'], 200),
    ]);

    $admin = User::factory()->create([
        'is_admin' => true,
        'discord_user_id' => '123456',
        'discord_avatar' => 'avatarhash',
    ]);
    $permission = Permission::query()->where('slug', 'admin')->firstOrFail();
    $admin->permissions()->attach($permission);

    $this->actingAs($admin)->post(route('admin.factions.store'), [
        'name' => 'Blue Guard',
        'slug' => 'blue-guard',
        'short_description' => 'Holding the line.',
        'flag_image' => 'blue.png',
        'color' => '#112233',
        'lore' => 'A test faction.',
    ])->assertRedirect();

    $faction = Faction::query()->where('slug', 'blue-guard')->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.factions.update', $faction), [
        'name' => 'Blue Guard Prime',
        'slug' => 'blue-guard',
        'short_description' => 'Holding the line harder.',
        'flag_image' => 'blue.png',
        'color' => '#334455',
        'lore' => 'An updated test faction.',
    ])->assertRedirect();

    $this->actingAs($admin)->delete(route('admin.factions.destroy', $faction))->assertRedirect();

    Http::assertSentCount(3);

    Http::assertSent(function ($request) {
        $embed = ($request->data()['embeds'][0] ?? []);

        return ($embed['title'] ?? null) === 'Faction Created'
            && str_contains($embed['description'] ?? '', 'Blue Guard');
    });

    Http::assertSent(function ($request) {
        $embed = ($request->data()['embeds'][0] ?? []);

        return ($embed['title'] ?? null) === 'Faction Updated'
            && str_contains($embed['description'] ?? '', 'Blue Guard Prime')
            && str_contains($embed['fields'][0]['value'] ?? '', 'Blue Guard Prime');
    });

    Http::assertSent(function ($request) {
        $embed = ($request->data()['embeds'][0] ?? []);

        return ($embed['title'] ?? null) === 'Faction Deleted'
            && str_contains($embed['description'] ?? '', 'Blue Guard Prime');
    });
});

test('admin action discord audit logging is disabled by default', function () {
    config()->set('services.discord.bot_token', 'test-token');

    Http::fake([
        'https://discord.com/api/v10/channels/1483335218944282685/messages' => Http::response(['id' => '1'], 200),
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $permission = Permission::query()->where('slug', 'admin')->firstOrFail();
    $admin->permissions()->attach($permission);

    $this->actingAs($admin)->post(route('admin.factions.store'), [
        'name' => 'Quiet Guard',
        'slug' => 'quiet-guard',
        'short_description' => 'No broadcast needed.',
        'flag_image' => 'quiet.png',
        'color' => '#112233',
        'lore' => 'A test faction.',
    ])->assertRedirect();

    Http::assertSentCount(0);
});
