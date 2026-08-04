<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\Item;
use App\Models\Licence;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
    ]);
});

function createMarketplaceLevelCharacter(User $user, int $level): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Level Buyer '.$level,
        'age' => 24,
        'biography' => 'Testing level gates.',
        'starting_occupation' => 'Buyer',
        'plastic_credits' => 2000,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => $level,
        'experience_points' => 0,
    ]);
}

test('item purchases are blocked until the character meets the required level', function () {
    $user = User::factory()->create();
    $character = createMarketplaceLevelCharacter($user, 1);
    $item = Item::query()->create([
        'name' => 'Veteran Toolkit',
        'slug' => 'veteran-toolkit',
        'description' => 'Requires more experience.',
        'type' => 'tool',
        'price' => 100,
        'required_level' => 3,
    ]);

    $this->actingAs($user)
        ->post(route('store.purchase'), [
            'purchase_type' => 'item',
            'id' => $item->id,
        ])
        ->assertSessionHasErrors('purchase');

    expect($character->fresh()->inventory()->where('items.id', $item->id)->exists())->toBeFalse();
});

test('licence purchases are blocked until the character meets the required level', function () {
    $user = User::factory()->create();
    $character = createMarketplaceLevelCharacter($user, 1);
    $licence = Licence::query()->create([
        'name' => 'Advanced Merchant',
        'slug' => 'advanced-merchant',
        'description' => 'A higher level trading permit.',
        'cost' => 100,
        'required_level' => 4,
    ]);

    $this->actingAs($user)
        ->post(route('store.purchase'), [
            'purchase_type' => 'licence',
            'id' => $licence->id,
        ])
        ->assertSessionHasErrors('purchase');

    expect($character->fresh()->licences()->where('licences.id', $licence->id)->exists())->toBeFalse();
});
