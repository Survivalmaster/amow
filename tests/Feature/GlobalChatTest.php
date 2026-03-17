<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Rank;
use App\Models\User;
use Database\Seeders\FactionSeeder;
use Database\Seeders\GameJobSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        GameJobSeeder::class,
    ]);
});

function createGlobalChatCharacter(User $user): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Broadcaster',
        'age' => 22,
        'biography' => 'Testing global chat.',
        'starting_occupation' => 'Begger',
        'current_job_id' => GameJob::query()->where('is_starter', true)->value('id'),
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

test('character can post and fetch global chat messages', function () {
    $user = User::factory()->create();
    createGlobalChatCharacter($user);

    $this->actingAs($user)
        ->postJson(route('chat.global.store'), [
            'message' => 'Hello from the world feed.',
        ])
        ->assertOk()
        ->assertJsonPath('message.character_name', 'Broadcaster')
        ->assertJsonPath('message.message', 'Hello from the world feed.');

    $this->actingAs($user)
        ->getJson(route('chat.global.index'))
        ->assertOk()
        ->assertJsonPath('messages.0.character_name', 'Broadcaster')
        ->assertJsonPath('messages.0.message', 'Hello from the world feed.');
});

test('global chat formats roleplay commands', function () {
    $user = User::factory()->create();
    createGlobalChatCharacter($user);

    $this->actingAs($user)
        ->postJson(route('chat.global.store'), [
            'message' => '/me waves at Mighty',
        ])
        ->assertOk()
        ->assertJsonPath('message.message_type', 'emote')
        ->assertJsonPath('message.display_message', 'Broadcaster waves at Mighty');

    $this->actingAs($user)
        ->postJson(route('chat.global.store'), [
            'message' => '/do a storm rolls in',
        ])
        ->assertOk()
        ->assertJsonPath('message.message_type', 'description')
        ->assertJsonPath('message.display_message', 'a storm rolls in (Broadcaster)');
});
