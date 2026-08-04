<?php

use App\Models\Character;
use App\Models\Faction;
use App\Models\Rank;
use App\Models\User;
use App\Models\Permission;
use Database\Seeders\FactionSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RankSeeder;

beforeEach(function () {
    $this->seed([
        FactionSeeder::class,
        RankSeeder::class,
        PermissionSeeder::class,
    ]);
});

test('the bank page shows banking information and same faction recipients', function () {
    $user = User::factory()->create();
    $faction = Faction::query()->firstOrFail();
    createBankCharacter($user, ['faction_id' => $faction->id, 'name' => 'Sender', 'plastic_credits' => 500]);
    createBankCharacter(User::factory()->create(), ['faction_id' => $faction->id, 'name' => 'Receiver']);

    $this
        ->actingAs($user)
        ->get(route('bank.index'))
        ->assertOk()
        ->assertSee('Banking Overview')
        ->assertSee('500 Plastic Credits available')
        ->assertSee('Receiver');
});

test('a player can transfer credits to a same faction character', function () {
    $senderUser = User::factory()->create();
    $recipientUser = User::factory()->create();
    $faction = Faction::query()->firstOrFail();
    $sender = createBankCharacter($senderUser, ['faction_id' => $faction->id, 'name' => 'Sender', 'plastic_credits' => 500]);
    $recipient = createBankCharacter($recipientUser, ['faction_id' => $faction->id, 'name' => 'Receiver', 'plastic_credits' => 75]);

    $this
        ->actingAs($senderUser)
        ->post(route('bank.transfers.store'), [
            'recipient_character_id' => $recipient->id,
            'amount' => 125,
            'note' => 'Mission split',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Bank transfer sent.');

    expect($sender->fresh()->plastic_credits)->toBe(375);
    expect($recipient->fresh()->plastic_credits)->toBe(200);

    $this->assertDatabaseHas('transactions', [
        'character_id' => $sender->id,
        'type' => 'player_transfer_sent',
        'amount' => -125,
    ]);

    $this->assertDatabaseHas('transactions', [
        'character_id' => $recipient->id,
        'type' => 'player_transfer_received',
        'amount' => 125,
    ]);

    $admin = User::factory()->create(['is_admin' => true]);
    $admin->permissions()->attach(Permission::query()->where('slug', 'admin')->firstOrFail());

    $this
        ->actingAs($admin)
        ->get(route('admin.character-logs.index', ['character_id' => $sender->id]))
        ->assertOk()
        ->assertSee('Money Sent')
        ->assertSee('To Receiver')
        ->assertSee('Balance 500 -&gt; 375', false)
        ->assertSee('Note: Mission split');

    $this
        ->actingAs($admin)
        ->get(route('admin.character-logs.index', ['character_id' => $recipient->id]))
        ->assertOk()
        ->assertSee('Money Received')
        ->assertSee('From Sender')
        ->assertSee('Balance 75 -&gt; 200', false)
        ->assertSee('Note: Mission split');
});

test('a player cannot transfer credits outside their faction', function () {
    $senderUser = User::factory()->create();
    $senderFaction = Faction::query()->firstOrFail();
    $recipientFaction = Faction::query()->whereKeyNot($senderFaction->id)->firstOrFail();
    $sender = createBankCharacter($senderUser, ['faction_id' => $senderFaction->id, 'plastic_credits' => 500]);
    $recipient = createBankCharacter(User::factory()->create(), ['faction_id' => $recipientFaction->id, 'plastic_credits' => 75]);

    $this
        ->actingAs($senderUser)
        ->post(route('bank.transfers.store'), [
            'recipient_character_id' => $recipient->id,
            'amount' => 125,
        ])
        ->assertSessionHasErrors('recipient_character_id');

    expect($sender->fresh()->plastic_credits)->toBe(500);
    expect($recipient->fresh()->plastic_credits)->toBe(75);
});

test('a player cannot transfer more credits than they have', function () {
    $senderUser = User::factory()->create();
    $faction = Faction::query()->firstOrFail();
    $sender = createBankCharacter($senderUser, ['faction_id' => $faction->id, 'plastic_credits' => 50]);
    $recipient = createBankCharacter(User::factory()->create(), ['faction_id' => $faction->id, 'plastic_credits' => 75]);

    $this
        ->actingAs($senderUser)
        ->post(route('bank.transfers.store'), [
            'recipient_character_id' => $recipient->id,
            'amount' => 125,
        ])
        ->assertSessionHasErrors('amount');

    expect($sender->fresh()->plastic_credits)->toBe(50);
    expect($recipient->fresh()->plastic_credits)->toBe(75);
});

function createBankCharacter(User $user, array $overrides = []): Character
{
    return Character::query()->create([
        'user_id' => $user->id,
        'faction_id' => Faction::query()->firstOrFail()->id,
        'name' => 'Bank Tester',
        'age' => 25,
        'biography' => 'Testing bank transfers.',
        'starting_occupation' => 'Laborer',
        'plastic_credits' => 100,
        'rank_id' => Rank::query()->where('name', 'Civilian')->firstOrFail()->id,
        'role_type' => 'civilian',
        'level' => 0,
        'experience_points' => 0,
        'health_points' => 100,
        'stamina_points' => 100,
        'armor_points' => 0,
        ...$overrides,
    ]);
}
