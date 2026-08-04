<?php

use App\Models\Faction;
use App\Models\MapHex;
use App\Models\User;
use App\Services\Maps\MapClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('map claim service validates claimable tile type', function () {
    $service = app(MapClaimService::class);
    $hex = MapHex::factory()->create(['tile_type' => MapHex::TYPE_WATER]);

    expect(fn () => $service->ensureClaimable($hex))->toThrow(RuntimeException::class, 'Only visible claimable land can be claimed.');
});

test('map claim service claims visible claimable land', function () {
    $service = app(MapClaimService::class);
    $faction = Faction::query()->create(['name' => 'Service Faction', 'slug' => 'service-faction', 'short_description' => 'Service.', 'color' => '#3478c5']);
    $hex = MapHex::factory()->create(['tile_type' => MapHex::TYPE_CLAIMABLE, 'is_visible' => true]);
    $user = User::factory()->create();

    $claimed = $service->claim($hex, $faction, $user);

    expect($claimed->faction_id)->toBe($faction->id);
    expect($claimed->claim_strength)->toBe(1);
    expect($claimed->claimed_at)->not->toBeNull();
});
