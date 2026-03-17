<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Item;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\ItemSeeder;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
        GameJobSeeder::class,
        ItemSeeder::class,
    ]);
});

function createInventoryCharacter(User $user): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Inventory Tester',
        'age' => 23,
        'biography' => 'Testing inventory capacity.',
        'starting_occupation' => 'Begger',
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
        'plastic_credits' => 10000,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => 0,
        'experience_points' => 0,
    ]);
}

test('store blocks buying a new item when inventory is full', function () {
    $user = User::factory()->create();
    $character = createInventoryCharacter($user);

    $items = collect(range(1, 13))->map(function (int $index) {
        return Item::query()->create([
            'name' => "Capacity Test {$index}",
            'slug' => "capacity-test-{$index}",
            'description' => 'Inventory capacity test item.',
            'type' => 'utility',
            'icon_class' => 'fa-solid fa-cube',
            'inventory_slot_bonus' => 0,
            'price' => 10,
        ]);
    });

    $items->take(12)->each(function (Item $item) use ($character) {
        $character->inventory()->attach($item->id, ['quantity' => 1]);
    });

    $this->actingAs($user)
        ->from(route('store.index'))
        ->post(route('store.purchase'), [
            'purchase_type' => 'item',
            'id' => $items->last()->id,
        ])
        ->assertSessionHasErrors('purchase')
        ->assertRedirect(route('store.index'));
});
