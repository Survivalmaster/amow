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

test('non buyable items are hidden from the store and cannot be purchased directly', function () {
    $user = User::factory()->create();
    $character = createInventoryCharacter($user);
    $item = Item::query()->create([
        'name' => 'Fresh Log',
        'slug' => 'fresh-log',
        'description' => 'A job-only material.',
        'type' => 'material',
        'icon_class' => 'fa-solid fa-tree',
        'is_buyable' => false,
        'price' => 10,
    ]);

    $this->actingAs($user)
        ->get(route('store.index'))
        ->assertOk()
        ->assertDontSee('Fresh Log');

    $this->actingAs($user)
        ->from(route('store.index'))
        ->post(route('store.purchase'), [
            'purchase_type' => 'item',
            'id' => $item->id,
        ])
        ->assertSessionHasErrors('purchase')
        ->assertRedirect(route('store.index'));

    expect($character->fresh()->inventory()->whereKey($item->id)->exists())->toBeFalse();
});

test('item quantities consume slots based on max stack per slot', function () {
    $user = User::factory()->create();
    $character = createInventoryCharacter($user);
    $item = Item::query()->create([
        'name' => 'Stacked Log',
        'slug' => 'stacked-log',
        'description' => 'A stackable building material.',
        'type' => 'material',
        'icon_class' => 'fa-solid fa-tree',
        'max_stack_per_slot' => 10,
        'price' => 10,
    ]);

    $character->inventory()->attach($item->id, ['quantity' => 25]);

    expect($character->fresh('inventory')->inventorySlotsUsed())->toBe(3);

    $this->actingAs($user)
        ->get(route('inventory.index'))
        ->assertOk()
        ->assertSee('x10')
        ->assertSee('x5')
        ->assertSee('Max 10');
});

test('store blocks purchases that would create an extra stack beyond capacity', function () {
    $user = User::factory()->create();
    $character = createInventoryCharacter($user);
    $logs = Item::query()->create([
        'name' => 'Heavy Logs',
        'slug' => 'heavy-logs',
        'description' => 'A stackable material.',
        'type' => 'material',
        'icon_class' => 'fa-solid fa-tree',
        'max_stack_per_slot' => 10,
        'price' => 10,
    ]);

    $character->inventory()->attach($logs->id, ['quantity' => 120]);

    $this->actingAs($user)
        ->from(route('store.index'))
        ->post(route('store.purchase'), [
            'purchase_type' => 'item',
            'id' => $logs->id,
        ])
        ->assertSessionHasErrors('purchase')
        ->assertRedirect(route('store.index'));

    expect((int) $character->fresh('inventory')->inventory->firstWhere('id', $logs->id)->pivot->quantity)->toBe(120);
});
