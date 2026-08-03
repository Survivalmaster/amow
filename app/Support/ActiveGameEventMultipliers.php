<?php

namespace App\Support;

use App\Models\Character;
use App\Models\GameEvent;

class ActiveGameEventMultipliers
{
    public function forCharacter(Character $character): array
    {
        $character->loadMissing('faction');

        $events = GameEvent::query()
            ->where('is_enabled', true)
            ->where(function ($query) use ($character) {
                $query->whereNull('faction_id')
                    ->orWhere('faction_id', $character->faction_id);
            })
            ->get();

        return [
            'credits' => $this->highestMultiplier($events, 'credit_multiplier_enabled', 'credit_multiplier'),
            'xp' => $this->highestMultiplier($events, 'xp_multiplier_enabled', 'xp_multiplier'),
        ];
    }

    private function highestMultiplier($events, string $enabledKey, string $valueKey): int
    {
        return max(1, (int) $events
            ->filter(fn (GameEvent $event) => $event->{$enabledKey})
            ->max(fn (GameEvent $event) => min(5, max(1, (int) $event->{$valueKey}))));
    }
}
