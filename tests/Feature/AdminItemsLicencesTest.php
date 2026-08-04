<?php

use App\Models\Item;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed([
        PermissionSeeder::class,
    ]);
});

test('admin can create a licence from the items admin area', function () {
    $admin = User::factory()->create();
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $this->actingAs($admin)
        ->post(route('admin.licences.store'), [
            'name' => 'Harbour Permit',
            'slug' => 'harbour-permit',
            'description' => 'Allows harbour ownership.',
            'cost' => 300,
            'required_rank_id' => null,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('licences', [
        'slug' => 'harbour-permit',
        'name' => 'Harbour Permit',
    ]);
});

test('admin can link an item to a producing building', function () {
    $admin = User::factory()->create();
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $building = Item::query()->create([
        'name' => 'Workshop',
        'slug' => 'workshop',
        'description' => 'Produces basic goods.',
        'type' => 'building',
        'is_building' => true,
        'price' => 500,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.items.store'), [
            'name' => 'Worked Timber',
            'slug' => 'worked-timber',
            'description' => 'Processed building timber.',
            'type' => 'material',
            'price' => 25,
            'produced_by_building_item_id' => $building->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('items', [
        'slug' => 'worked-timber',
        'produced_by_building_item_id' => $building->id,
    ]);
});

test('admin can create categorized item types', function () {
    $admin = User::factory()->create();
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $this->actingAs($admin)
        ->post(route('admin.items.store'), [
            'name' => 'Foam Chestplate',
            'slug' => 'foam-chestplate',
            'description' => 'A light armour plate for patrols.',
            'type' => 'armor',
            'price' => 175,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('items', [
        'slug' => 'foam-chestplate',
        'type' => 'armor',
    ]);
});

test('admin cannot create an unknown item type', function () {
    $admin = User::factory()->create();
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $this->actingAs($admin)
        ->post(route('admin.items.store'), [
            'name' => 'Strange Object',
            'slug' => 'strange-object',
            'description' => 'Unknown equipment.',
            'type' => 'banana',
            'price' => 10,
        ])
        ->assertSessionHasErrors('type');
});
