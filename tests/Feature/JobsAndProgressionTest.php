<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Location;
use App\Models\Rank;
use App\Models\User;
use App\Jobs\SendDiscordChannelMessage;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\RankSeeder;
use Database\Seeders\WorldSeeder;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
        GameJobSeeder::class,
        WorldSeeder::class,
    ]);
});

function createCharacterForUser(User $user): Character
{
    $starterJobId = GameJob::query()->where('is_starter', true)->value('id');

    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Tester',
        'age' => 25,
        'biography' => 'Testing progression systems.',
        'starting_occupation' => 'Laborer',
        'current_job_id' => $starterJobId,
        'plastic_credits' => 100,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        'level' => 0,
        'experience_points' => 0,
    ]);
}

test('work awards credits and experience based on the active job', function () {
    Bus::fake();

    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $character->currentJob()->update([
        'stamina_decrease' => 12,
        'working_display_message' => 'Is begging in the city.',
    ]);
    $location = Location::query()->where('slug', 'go-to-work')->firstOrFail();

    $response = $this
        ->actingAs($user)
        ->post(route('work.store', $location));

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $character->refresh();

    expect($character->plastic_credits)->toBeGreaterThan(100);
    expect($character->experience_points)->toBe(5);
    expect($character->stamina_points)->toBe(88);
    expect($character->last_worked_at)->not->toBeNull();

    Bus::assertDispatched(SendDiscordChannelMessage::class, function (SendDiscordChannelMessage $job) use ($character) {
        return $job->channelId === '1483329516796379136'
            && str_contains($job->content, $character->name.' Is begging in the city.')
            && str_contains($job->content, 'their total now is '.number_format($character->plastic_credits).'.');
    });
});

test('character state endpoint returns live job and progression data', function () {
    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $character->update([
        'level' => 2,
        'experience_points' => 40,
        'plastic_credits' => 1234,
    ]);

    $response = $this
        ->actingAs($user)
        ->getJson(route('characters.state'));

    $response
        ->assertOk()
        ->assertJsonPath('displayed_job_name', 'Begger')
        ->assertJsonPath('level', 2)
        ->assertJsonPath('experience_points', 40)
        ->assertJsonPath('next_level_experience', 200)
        ->assertJsonPath('formatted_credits', '1,234');
});

test('job changes obey the 24 hour cooldown', function () {
    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $secondJob = GameJob::query()->create([
        'name' => 'Factory Hand',
        'slug' => 'factory-hand',
        'description' => 'Assembly line labour.',
        'min_pay' => 20,
        'max_pay' => 35,
        'required_level' => 0,
        'work_cooldown_minutes' => 10,
        'is_active' => true,
    ]);
    $thirdJob = GameJob::query()->create([
        'name' => 'Courier',
        'slug' => 'courier',
        'description' => 'Fast deliveries.',
        'min_pay' => 22,
        'max_pay' => 40,
        'required_level' => 0,
        'work_cooldown_minutes' => 8,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('jobs.store', $secondJob))
        ->assertSessionHasNoErrors();

    $character->refresh();
    expect($character->current_job_id)->toBe($secondJob->id);
    expect($character->job_changed_at)->not->toBeNull();

    $this->actingAs($user)
        ->from(route('jobs.index'))
        ->post(route('jobs.store', $thirdJob))
        ->assertSessionHasErrors('job')
        ->assertRedirect(route('jobs.index'));
});
