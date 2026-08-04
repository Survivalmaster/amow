<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\ItemSeeder;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
        ItemSeeder::class,
    ]);
});

test('marketplace navigation splits store and license center', function () {
    $user = User::factory()->create();

    Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Market Tester',
        'age' => 25,
        'biography' => 'Testing marketplace navigation.',
        'starting_occupation' => 'Laborer',
        'plastic_credits' => 1000,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'level' => 0,
        'experience_points' => 0,
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('store.index'))
        ->assertOk()
        ->assertSee('Marketplace')
        ->assertSee('Store')
        ->assertSee('License Center')
        ->assertSee('Player Businesses')
        ->assertSee('Skirmishes')
        ->assertSee('Coming Soon')
        ->assertSee('Purchase Item')
        ->assertDontSee('Purchase Licence');

    $this->actingAs($user)
        ->get(route('store.licences'))
        ->assertOk()
        ->assertSee('License Center')
        ->assertSee('Purchase Licence')
        ->assertDontSee('Purchase Item');
});
