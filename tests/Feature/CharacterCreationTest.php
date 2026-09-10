<?php

use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\RankSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake();
    $this->seed([FactionSeeder::class, GameJobSeeder::class, RankSeeder::class]);
    $this->actingAs(User::factory()->create());
    $this->withSession(['selected_faction_id' => Faction::query()->firstOrFail()->id]);
});

test('new players can create a character with the starter package', function () {
    $this->post(route('characters.store'), [
        'name' => 'New Player', 'age' => 18, 'biography' => 'Ready to join Plastica.',
    ])->assertRedirect(route('lobby'))->assertSessionMissing('selected_faction_id');

    $this->assertDatabaseHas('characters', [
        'name' => 'New Player',
        'plastic_credits' => 100,
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
        'rank_id' => Rank::query()->where('name', 'Civilian')->value('id'),
    ]);
    $this->assertDatabaseCount('characters', 1);
});

test('missing signup configuration returns the form and preserves player details', function (string $missing) {
    match ($missing) {
        'starter' => GameJob::query()->update(['is_starter' => false]),
        'active' => GameJob::query()->update(['is_active' => false]),
        'rank' => Rank::query()->where('name', 'Civilian')->delete(),
    };

    $details = ['name' => 'New Player', 'age' => 18, 'biography' => 'Ready to join Plastica.'];
    $this->post(route('characters.store'), $details)
        ->assertRedirect(route('characters.create'))
        ->assertSessionHasErrors('character')
        ->assertSessionHasInput('name', $details['name'])
        ->assertSessionHasInput('age', $details['age'])
        ->assertSessionHasInput('biography', $details['biography']);

    $this->get(route('characters.create'))
        ->assertOk()
        ->assertSee('Character creation is temporarily unavailable')
        ->assertSee($details['biography']);
    $this->assertDatabaseCount('characters', 0);
})->with(['starter', 'active', 'rank']);

test('a missing or stale faction selection returns to faction selection', function (?int $factionId) {
    $this->withSession(['selected_faction_id' => $factionId])
        ->post(route('characters.store'), [
            'name' => 'New Player', 'age' => 18, 'biography' => 'Ready to join Plastica.',
        ])
        ->assertRedirect(route('factions.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseCount('characters', 0);
})->with([null, 999999]);
