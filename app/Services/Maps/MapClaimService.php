<?php

namespace App\Services\Maps;

use App\Models\Faction;
use App\Models\MapHex;
use App\Models\User;
use RuntimeException;

class MapClaimService
{
    public function claim(MapHex $mapHex, Faction $faction, User $user): MapHex
    {
        $this->ensureClaimable($mapHex);

        // Extension points: adjacency, cooldowns, war state, resource costs, and contested strength.
        $mapHex->forceFill([
            'faction_id' => $faction->id,
            'claim_strength' => max(1, (int) $mapHex->claim_strength),
            'claimed_at' => now(),
        ])->save();

        return $mapHex->fresh('faction');
    }

    public function removeClaim(MapHex $mapHex, User $user): MapHex
    {
        $mapHex->forceFill([
            'faction_id' => null,
            'claim_strength' => 0,
            'claimed_at' => null,
        ])->save();

        return $mapHex->fresh('faction');
    }

    public function ensureClaimable(MapHex $mapHex): void
    {
        if (! $mapHex->isClaimable()) {
            throw new RuntimeException('Only visible claimable land can be claimed.');
        }
    }
}
