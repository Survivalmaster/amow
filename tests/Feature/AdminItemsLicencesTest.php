<?php

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
