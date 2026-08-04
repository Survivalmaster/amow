<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\Licence;
use App\Models\Permission;
use App\Models\PlayerBusiness;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
        PermissionSeeder::class,
    ]);
});

function createBusinessUser(bool $developer = true): array
{
    $user = User::factory()->create();

    if ($developer) {
        $user->permissions()->attach(Permission::query()->where('slug', 'developer')->firstOrFail());
    }

    $character = Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Business Tester '.substr((string) str()->uuid(), 0, 6),
        'age' => 30,
        'biography' => 'Testing commerce.',
        'starting_occupation' => 'Merchant',
        'plastic_credits' => 2000,
        'rank_id' => Rank::query()->where('name', 'Captain')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => 0,
        'experience_points' => 0,
    ]);

    return [$user, $character];
}

test('non developers cannot access player businesses yet', function () {
    [$user] = createBusinessUser(false);

    $this->actingAs($user)
        ->get(route('businesses.index'))
        ->assertForbidden();
});

test('developer with business licence can create a player business', function () {
    [$user, $character] = createBusinessUser();
    $licence = Licence::query()->where('slug', 'business-owner')->firstOrFail();
    $character->licences()->attach($licence->id);

    $this->actingAs($user)
        ->post(route('businesses.store'), [
            'name' => 'Plastic Works',
            'icon_class' => 'fa-solid fa-hammer',
            'business_type' => 'creates_items_on_order',
            'description' => 'Makes odd little requests.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('player_businesses', [
        'name' => 'Plastic Works',
        'owner_character_id' => $character->id,
        'licence_id' => $licence->id,
    ]);
    $this->assertDatabaseHas('player_business_logs', [
        'type' => 'business_created',
    ]);
});

test('owner can add menu entries and move business bank credits', function () {
    [$user, $character] = createBusinessUser();
    $licence = Licence::query()->where('slug', 'business-owner')->firstOrFail();
    $character->licences()->attach($licence->id);
    $business = PlayerBusiness::query()->create([
        'owner_character_id' => $character->id,
        'faction_id' => $character->faction_id,
        'licence_id' => $licence->id,
        'name' => 'Menu Works',
        'slug' => 'menu-works',
        'icon_class' => 'fa-solid fa-store',
        'business_type' => 'sells_items',
    ]);

    $this->actingAs($user)
        ->post(route('businesses.menu.store', $business), [
            'title' => 'Repair Order',
            'mode' => 'services',
            'price' => 45,
            'description' => 'Quick repairs.',
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('businesses.deposit', $business), ['amount' => 300])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('businesses.withdraw', $business), ['amount' => 120])
        ->assertRedirect();

    $this->assertDatabaseHas('player_business_menu_items', [
        'player_business_id' => $business->id,
        'title' => 'Repair Order',
    ]);
    expect($business->fresh()->bank_credits)->toBe(180);
    expect($character->fresh()->plastic_credits)->toBe(1820);
});

test('invited same nation character can join a business role', function () {
    [$ownerUser, $owner] = createBusinessUser();
    [$workerUser, $worker] = createBusinessUser();
    $licence = Licence::query()->where('slug', 'business-owner')->firstOrFail();
    $owner->licences()->attach($licence->id);
    $business = PlayerBusiness::query()->create([
        'owner_character_id' => $owner->id,
        'faction_id' => $owner->faction_id,
        'licence_id' => $licence->id,
        'name' => 'Invite Works',
        'slug' => 'invite-works',
        'icon_class' => 'fa-solid fa-store',
        'business_type' => 'sells_items',
    ]);
    $role = $business->roles()->create(['name' => 'Clerk', 'hourly_wage' => 12]);

    $this->actingAs($ownerUser)
        ->post(route('businesses.invites.store', $business), [
            'character_name' => $worker->name,
            'player_business_role_id' => $role->id,
        ])
        ->assertRedirect();

    $this->actingAs($workerUser)
        ->post(route('businesses.join', $business))
        ->assertRedirect();

    $this->assertDatabaseHas('player_business_members', [
        'player_business_id' => $business->id,
        'character_id' => $worker->id,
        'status' => 'active',
    ]);
});
