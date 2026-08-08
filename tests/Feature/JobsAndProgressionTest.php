<?php

use App\Models\Character;
use App\Models\CharacterJobProgress;
use App\Models\Faction;
use App\Models\GameEvent;
use App\Models\GameJob;
use App\Models\Item;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\LicenceSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;
use Database\Seeders\WorldSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        LicenceSeeder::class,
        GameJobSeeder::class,
        PermissionSeeder::class,
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
    config()->set('services.discord.bot_token', 'test-token');
    Http::fake([
        'https://discord.com/api/v10/channels/1483329516796379136/messages' => Http::response(['id' => '123'], 200),
    ]);

    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $character->currentJob()->update([
        'stamina_decrease' => 12,
        'experience_reward' => 8,
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
    expect($character->experience_points)->toBe(8);
    expect($character->stamina_points)->toBe(88);
    expect($character->last_worked_at)->not->toBeNull();

    Http::assertSent(function ($request) use ($character) {
        $payload = $request->data();
        $embed = $payload['embeds'][0] ?? [];

        return $request->url() === 'https://discord.com/api/v10/channels/1483329516796379136/messages'
            && $request->hasHeader('Authorization', 'Bot test-token')
            && ($embed['author']['name'] ?? null) === $character->name
            && str_contains($embed['title'] ?? '', $character->name.' is begging in the city.')
            && str_contains($embed['description'] ?? '', 'They have earned **')
            && str_contains($embed['description'] ?? '', 'Their total now is **'.number_format($character->plastic_credits).'**.')
            && isset($embed['color'])
            && ! empty($embed['timestamp']);
    });
});

test('characters with no stamina cannot work', function () {
    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $character->update(['stamina_points' => 0]);
    $location = Location::query()->where('slug', 'go-to-work')->firstOrFail();

    $this
        ->actingAs($user)
        ->from(route('jobs.index'))
        ->post(route('work.store', $location))
        ->assertSessionHasErrors('work')
        ->assertRedirect(route('jobs.index'));

    $character->refresh();

    expect($character->plastic_credits)->toBe(100);
    expect($character->last_worked_at)->toBeNull();
});

test('character state marks work unavailable with no stamina', function () {
    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $character->update(['stamina_points' => 0]);

    $this
        ->actingAs($user)
        ->getJson(route('characters.state'))
        ->assertOk()
        ->assertJsonPath('can_work', false)
        ->assertJsonPath('work_blocked_by_stamina', true)
        ->assertJsonPath('work_unavailable', true)
        ->assertJsonPath('work_status_label', 'Exhausted');
});

test('active game master events multiply work credits and experience', function () {
    config()->set('services.discord.bot_token', 'test-token');
    Http::fake([
        'https://discord.com/api/v10/channels/1483329516796379136/messages' => Http::response(['id' => '123'], 200),
    ]);

    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $character->currentJob()->update([
        'min_pay' => 10,
        'max_pay' => 10,
        'experience_reward' => 8,
        'work_cooldown_minutes' => 1,
    ]);
    GameEvent::query()->create([
        'created_by_user_id' => $user->id,
        'title' => 'Factory Surge',
        'body' => 'Temporary production bonuses.',
        'is_enabled' => true,
        'xp_multiplier_enabled' => true,
        'xp_multiplier' => 1.5,
        'credit_multiplier_enabled' => true,
        'credit_multiplier' => 1.5,
    ]);
    $location = Location::query()->where('slug', 'go-to-work')->firstOrFail();

    $this->actingAs($user)
        ->post(route('work.store', $location))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $character->refresh();

    expect($character->plastic_credits)->toBe(115);
    expect($character->experience_points)->toBe(12);

    $transaction = $character->transactions()->where('type', 'work')->latest()->firstOrFail();

    expect($transaction->amount)->toBe(15);
    expect($transaction->metadata['credit_multiplier'])->toBe(1.5);
    expect($transaction->metadata['xp_multiplier'])->toBe(1.5);
    expect($transaction->metadata['credit_multiplier_events'][0]['id'])->toBeInt();
    expect($transaction->metadata['credit_multiplier_events'][0]['name'])->toBe('Factory Surge');
    expect($transaction->metadata['xp_multiplier_events'][0]['id'])->toBeInt();
    expect($transaction->metadata['xp_multiplier_events'][0]['name'])->toBe('Factory Surge');

    Http::assertSent(function ($request) {
        $embed = $request->data()['embeds'][0] ?? [];
        $description = $embed['description'] ?? '';

        return $request->url() === 'https://discord.com/api/v10/channels/1483329516796379136/messages'
            && str_contains($description, '**Event bonus:**')
            && str_contains($description, 'XP 1.5x from Factory Surge')
            && str_contains($description, 'Credits 1.5x from Factory Surge');
    });
});

test('expired game master events do not multiply work rewards', function () {
    config()->set('services.discord.bot_token', 'test-token');
    Http::fake([
        'https://discord.com/api/v10/channels/1483329516796379136/messages' => Http::response(['id' => '123'], 200),
    ]);

    $user = User::factory()->create();
    $character = createCharacterForUser($user);
    $character->currentJob()->update([
        'min_pay' => 10,
        'max_pay' => 10,
        'experience_reward' => 8,
    ]);
    GameEvent::query()->create([
        'created_by_user_id' => $user->id,
        'title' => 'Expired Surge',
        'body' => 'This should no longer apply.',
        'is_enabled' => true,
        'ends_at' => now()->subMinute(),
        'xp_multiplier_enabled' => true,
        'xp_multiplier' => 5,
        'credit_multiplier_enabled' => true,
        'credit_multiplier' => 5,
    ]);
    $location = Location::query()->where('slug', 'go-to-work')->firstOrFail();

    $this->actingAs($user)
        ->post(route('work.store', $location))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $character->refresh();

    expect($character->plastic_credits)->toBe(110);
    expect($character->experience_points)->toBe(8);
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

test('jobs new is visible only to developers', function () {
    $user = User::factory()->create();
    createCharacterForUser($user);
    $previewJob = GameJob::query()->create([
        'name' => 'Log Chopper',
        'slug' => 'log-chopper',
        'description' => 'Cuts timber for land projects.',
        'min_pay' => 10,
        'max_pay' => 15,
        'required_level' => 0,
        'work_cooldown_minutes' => 5,
        'is_active' => true,
        'is_new' => true,
    ]);
    $oldJob = GameJob::query()->create([
        'name' => 'Old Miner',
        'slug' => 'old-miner',
        'description' => 'Old jobs page only.',
        'min_pay' => 10,
        'max_pay' => 15,
        'required_level' => 0,
        'work_cooldown_minutes' => 5,
        'is_active' => true,
        'is_new' => false,
    ]);

    $this->actingAs($user)
        ->get(route('lobby'))
        ->assertOk()
        ->assertSee('Jobs New')
        ->assertSee('Coming Soon')
        ->assertDontSee(route('jobs-new.index'), false);

    $this->actingAs($user)
        ->get(route('jobs-new.index'))
        ->assertForbidden();

    $user->permissions()->attach(Permission::query()->where('slug', 'developer')->firstOrFail());

    $this->actingAs($user)
        ->get(route('jobs-new.index'))
        ->assertOk()
        ->assertSee('Jobs New')
        ->assertSee($previewJob->name)
        ->assertDontSee($oldJob->name);
});

test('jobs new work advances job tier and awards configured drops', function () {
    $user = User::factory()->create();
    $user->permissions()->attach(Permission::query()->where('slug', 'developer')->firstOrFail());
    $character = createCharacterForUser($user);
    $character->currentJob()->update([
        'min_pay' => 10,
        'max_pay' => 10,
        'experience_reward' => 100,
        'tier_xp_required' => 100,
        'tier_pay_bonus_percent' => 10,
        'tier_xp_bonus_percent' => 10,
        'work_cooldown_minutes' => 1,
        'is_new' => true,
    ]);
    $item = Item::query()->create([
        'name' => 'Log',
        'slug' => 'log',
        'description' => 'Job reward material.',
        'type' => 'material',
        'icon_class' => 'fa-solid fa-tree',
        'is_buyable' => false,
        'price' => 1,
    ]);
    $character->currentJob->drops()->create([
        'item_id' => $item->id,
        'min_tier' => 1,
        'max_tier' => 20,
        'min_quantity' => 2,
        'max_quantity' => 2,
        'drop_chance_percent' => 100,
    ]);

    $this->actingAs($user)
        ->post(route('jobs-new.work'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $progress = CharacterJobProgress::query()
        ->where('character_id', $character->id)
        ->where('game_job_id', $character->current_job_id)
        ->firstOrFail();

    expect($progress->tier)->toBe(2);
    expect($character->fresh()->plastic_credits)->toBe(110);
    expect((int) $character->fresh('inventory')->inventory->firstWhere('id', $item->id)->pivot->quantity)->toBe(2);
});

test('jobs new keeps tier progress when characters change jobs and return', function () {
    $user = User::factory()->create();
    $user->permissions()->attach(Permission::query()->where('slug', 'developer')->firstOrFail());
    $character = createCharacterForUser($user);
    $firstJob = $character->currentJob;
    $firstJob->update([
        'min_pay' => 10,
        'max_pay' => 10,
        'experience_reward' => 100,
        'tier_xp_required' => 100,
        'work_cooldown_minutes' => 1,
        'is_new' => true,
    ]);
    $secondJob = GameJob::query()->create([
        'name' => 'Stone Gatherer',
        'slug' => 'stone-gatherer',
        'description' => 'Collects stone for builders.',
        'min_pay' => 10,
        'max_pay' => 15,
        'required_level' => 0,
        'work_cooldown_minutes' => 5,
        'stamina_decrease' => 5,
        'experience_reward' => 5,
        'is_active' => true,
        'is_new' => true,
    ]);

    $this->actingAs($user)
        ->post(route('jobs-new.work'))
        ->assertSessionHasNoErrors();

    expect(CharacterJobProgress::query()
        ->where('character_id', $character->id)
        ->where('game_job_id', $firstJob->id)
        ->firstOrFail()->tier)->toBe(2);

    $this->actingAs($user)
        ->post(route('jobs-new.store', $secondJob))
        ->assertSessionHasNoErrors();

    $character->refresh()->update(['job_changed_at' => now()->subDay()]);

    $this->actingAs($user)
        ->post(route('jobs-new.store', $firstJob))
        ->assertSessionHasNoErrors();

    expect(CharacterJobProgress::query()
        ->where('character_id', $character->id)
        ->where('game_job_id', $firstJob->id)
        ->firstOrFail()->tier)->toBe(2);
});
