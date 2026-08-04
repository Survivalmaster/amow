<?php

use App\Models\Character;
use App\Models\Company;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\StockHolding;
use App\Models\StockMarketSetting;
use App\Models\User;
use Database\Seeders\CompanySeeder;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        GameJobSeeder::class,
        CompanySeeder::class,
        PermissionSeeder::class,
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

test('admins can add companies to the stock market', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $this->actingAs($admin)
        ->post(route('admin.stock-market.companies.store'), [
            'name' => 'Blue Ocean Salvage',
            'current_price' => 28.75,
            'description' => 'Recovery crews trading in washed-up plastic and machine scrap.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('companies', [
        'name' => 'Blue Ocean Salvage',
        'slug' => 'blue-ocean-salvage',
        'current_price' => 28.75,
    ]);
});

test('admins can manually hard crash a listed company', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);
    $company = Company::query()->firstOrFail();
    $company->update(['current_price' => 1000, 'last_price_updated_at' => now()]);

    $this->actingAs($admin)
        ->post(route('admin.stock-market.companies.crash', $company))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect((float) $company->fresh()->current_price)->toBe(10.0);
});

test('manual crashes can push a low stock below five credits', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);
    $company = Company::query()->firstOrFail();
    $company->update(['current_price' => 100, 'last_price_updated_at' => now()]);

    $this->actingAs($admin)
        ->post(route('admin.stock-market.companies.crash', $company))
        ->assertRedirect();

    expect((float) $company->fresh()->current_price)->toBe(1.0);
});

test('buying shares raises the company price', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    $character = createMarketCharacter($user);
    $character->update(['plastic_credits' => 100000]);
    $company = Company::query()->firstOrFail();
    $company->update(['current_price' => 100, 'last_price_updated_at' => now()]);

    $this->actingAs($user)
        ->post(route('market.buy', $company), ['shares' => 100])
        ->assertRedirect();

    expect((float) $company->fresh()->current_price)->toBeGreaterThan(100.0);
    expect($character->transactions()->where('type', 'stock_buy')->first()?->metadata)
        ->toMatchArray([
            'shares' => 100,
            'impact_percent' => 1.0,
            'crash_applied' => false,
        ]);
});

test('buying shares cannot raise a company by more than ten percent at once', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'buy_impact_percent_per_100_shares' => 99,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    $character = createMarketCharacter($user);
    $character->update(['plastic_credits' => 100000]);
    $company = Company::query()->firstOrFail();
    $company->update(['current_price' => 100, 'last_price_updated_at' => now()]);

    $this->actingAs($user)
        ->post(route('market.buy', $company), ['shares' => 1000])
        ->assertRedirect();

    expect((float) $company->fresh()->current_price)->toBe(110.0);
});

test('players cannot buy more than a company share limit allows', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    $character = createMarketCharacter($user);
    $character->update(['plastic_credits' => 100000]);
    $company = Company::query()->firstOrFail();
    $company->update([
        'current_price' => 100,
        'max_shares_per_character' => 50,
        'last_price_updated_at' => now(),
    ]);

    StockHolding::query()->create([
        'character_id' => $character->id,
        'company_id' => $company->id,
        'shares' => 45,
        'average_buy_price' => 80,
    ]);

    $this->actingAs($user)
        ->from(route('market.index'))
        ->post(route('market.buy', $company), ['shares' => 10])
        ->assertRedirect(route('market.index'))
        ->assertSessionHasErrors('stocks');

    expect((int) StockHolding::query()->where('character_id', $character->id)->where('company_id', $company->id)->firstOrFail()->shares)->toBe(45);
    expect((float) $company->fresh()->current_price)->toBe(100.0);
    expect($character->transactions()->where('type', 'stock_buy')->exists())->toBeFalse();
});

test('random ticker cannot raise a company by more than ten percent at once', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 99,
        'max_change_percent' => 99,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    createMarketCharacter($user);
    Company::query()->update([
        'current_price' => 100,
        'last_price_updated_at' => now()->subMinutes(2),
    ]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk();

    expect((float) Company::query()->firstOrFail()->current_price)->toBe(110.0);
});

test('random ticker can move a five credit stock below the old floor', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => -7,
        'max_change_percent' => -7,
        'passive_growth_bias_percent' => 0,
        'low_price_recovery_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    createMarketCharacter($user);
    Company::query()->update([
        'current_price' => 5,
        'last_price_updated_at' => now()->subMinutes(2),
    ]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk();

    expect((float) Company::query()->firstOrFail()->current_price)->toBe(4.65);
});

test('low price recovery can lift crashed stocks without player buying', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => -7,
        'max_change_percent' => -7,
        'passive_growth_bias_percent' => 2,
        'low_price_recovery_percent' => 30,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    createMarketCharacter($user);
    Company::query()->update([
        'current_price' => 1,
        'last_price_updated_at' => now()->subMinutes(2),
    ]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk();

    expect((float) Company::query()->firstOrFail()->current_price)->toBeGreaterThan(1.0);
});

test('penny stocks visibly recover by at least one cent when growth is positive', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'passive_growth_bias_percent' => 10,
        'low_price_recovery_percent' => 5,
        'low_price_minimum_lift' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    createMarketCharacter($user);
    Company::query()->update([
        'current_price' => 0.01,
        'last_price_updated_at' => now()->subMinutes(2),
    ]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk();

    expect((float) Company::query()->firstOrFail()->current_price)->toBe(0.02);
});

test('low price recovery uses a minimum lift so penny stocks do not crawl', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'passive_growth_bias_percent' => 0,
        'low_price_recovery_percent' => 5,
        'low_price_minimum_lift' => 0.25,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    createMarketCharacter($user);
    Company::query()->update([
        'current_price' => 0.01,
        'last_price_updated_at' => now()->subMinutes(2),
    ]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk();

    expect((float) Company::query()->firstOrFail()->current_price)->toBe(0.26);
});

test('one freshly updated company does not block due companies from ticking', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'passive_growth_bias_percent' => 10,
        'low_price_recovery_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    createMarketCharacter($user);
    $companies = Company::query()->orderBy('id')->take(2)->get();
    $companies[0]->update(['current_price' => 10, 'last_price_updated_at' => now()]);
    $companies[1]->update(['current_price' => 10, 'last_price_updated_at' => now()->subMinutes(2)]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk();

    expect((float) $companies[0]->fresh()->current_price)->toBe(10.0);
    expect((float) $companies[1]->fresh()->current_price)->toBe(11.0);
});

test('future stock timestamps are corrected instead of freezing the ticker', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'passive_growth_bias_percent' => 10,
        'low_price_recovery_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    createMarketCharacter($user);
    Company::query()->update([
        'current_price' => 10,
        'last_price_updated_at' => now()->addMinutes(5),
    ]);

    $this->actingAs($user)
        ->getJson(route('market.state'))
        ->assertOk();

    expect((float) Company::query()->firstOrFail()->current_price)->toBe(11.0);
});

test('selling one hundred shares can hard crash a company price', function () {
    StockMarketSetting::query()->updateOrCreate(['id' => 1], [
        'min_change_percent' => 0,
        'max_change_percent' => 0,
        'buy_impact_percent_per_100_shares' => 1,
        'sell_impact_percent_per_100_shares' => 1,
        'max_trade_impact_percent' => 99,
        'crash_trade_threshold_shares' => 100,
        'crash_extra_percent' => 99,
    ]);

    $user = User::factory()->create();
    $character = createMarketCharacter($user);
    $company = Company::query()->firstOrFail();
    $company->update(['current_price' => 1000, 'last_price_updated_at' => now()]);

    StockHolding::query()->create([
        'character_id' => $character->id,
        'company_id' => $company->id,
        'shares' => 100,
        'average_buy_price' => 75,
    ]);

    $this->actingAs($user)
        ->post(route('market.sell', $company), ['shares' => 100])
        ->assertRedirect();

    expect((float) $company->fresh()->current_price)->toBe(10.0);
    expect($character->transactions()->where('type', 'stock_sell')->first()?->metadata)
        ->toMatchArray([
            'shares' => 100,
            'impact_percent' => -99.0,
            'crash_applied' => true,
        ]);
});
