<?php

use App\Models\Character;
use App\Models\Company;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        GameJobSeeder::class,
        CompanySeeder::class,
    ]);
});

function createMarketCharacter(User $user): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Trader',
        'age' => 30,
        'biography' => 'Testing stock prices.',
        'starting_occupation' => 'Begger',
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
        'plastic_credits' => 1000,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => 0,
        'experience_points' => 0,
    ]);
}

test('market state fluctuates prices when they are due', function () {
    $user = User::factory()->create();
    createMarketCharacter($user);

    Company::query()->update([
        'current_price' => 50,
        'last_price_updated_at' => now()->subMinutes(2),
    ]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk()
        ->assertJsonCount(4, 'companies');

    expect(Company::query()->where('current_price', '!=', 50)->count())->toBeGreaterThan(0);
});
