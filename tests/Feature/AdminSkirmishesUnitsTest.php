<?php

use App\Models\Permission;
use App\Models\Skirmish;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function combatAdmin(): User
{
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    return $admin;
}

test('admin can manage skirmishes', function () {
    $admin = combatAdmin();

    $this
        ->actingAs($admin)
        ->get(route('admin.skirmishes.index'))
        ->assertOk()
        ->assertSee('Admin: Skirmishes')
        ->assertSee('Create Skirmish');

    $this
        ->actingAs($admin)
        ->post(route('admin.skirmishes.store'), [
            'title' => 'Bridge Clash',
            'slug' => 'bridge-clash',
            'description' => 'A fight for a river crossing.',
            'status' => 'open',
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHours(3)->format('Y-m-d H:i:s'),
        ])
        ->assertRedirect();

    $skirmish = Skirmish::query()->where('slug', 'bridge-clash')->firstOrFail();

    $this
        ->actingAs($admin)
        ->patch(route('admin.skirmishes.update', $skirmish), [
            'title' => 'Bridge Clash Prime',
            'slug' => 'bridge-clash',
            'description' => 'The crossing is contested.',
            'status' => 'active',
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHours(3)->format('Y-m-d H:i:s'),
        ])
        ->assertRedirect();

    expect($skirmish->fresh())
        ->title->toBe('Bridge Clash Prime')
        ->status->toBe('active');

    $this
        ->actingAs($admin)
        ->delete(route('admin.skirmishes.destroy', $skirmish))
        ->assertRedirect();

    $this->assertDatabaseMissing('skirmishes', ['slug' => 'bridge-clash']);
});

test('admin can manage units', function () {
    $admin = combatAdmin();

    $this
        ->actingAs($admin)
        ->get(route('admin.units.index'))
        ->assertOk()
        ->assertSee('Admin: Units')
        ->assertSee('Create Unit');

    $this
        ->actingAs($admin)
        ->post(route('admin.units.store'), [
            'name' => 'Infantry Squad',
            'slug' => 'infantry-squad',
            'description' => 'Basic frontline troops.',
            'category' => 'infantry',
            'firepower' => 12,
            'cost' => 250,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $unit = Unit::query()->where('slug', 'infantry-squad')->firstOrFail();

    $this
        ->actingAs($admin)
        ->patch(route('admin.units.update', $unit), [
            'name' => 'Infantry Squad',
            'slug' => 'infantry-squad',
            'description' => 'Updated frontline troops.',
            'category' => 'infantry',
            'firepower' => 15,
            'cost' => 300,
        ])
        ->assertRedirect();

    expect($unit->fresh())
        ->firepower->toBe(15)
        ->cost->toBe(300)
        ->is_active->toBeFalse();

    $this
        ->actingAs($admin)
        ->delete(route('admin.units.destroy', $unit))
        ->assertRedirect();

    $this->assertDatabaseMissing('units', ['slug' => 'infantry-squad']);
});
