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
            ->active()
            ->where(function ($query) use ($character) {
                $query->whereNull('faction_id')
                    ->orWhere('faction_id', $character->faction_id);
            })
            ->get();

        return [
            'credits' => $this->multiplierDetails($events, 'credit_multiplier_enabled', 'credit_multiplier'),
            'xp' => $this->multiplierDetails($events, 'xp_multiplier_enabled', 'xp_multiplier'),
        ];
    }

    private function multiplierDetails($events, string $enabledKey, string $valueKey): array
    {
        $eligibleEvents = $events
            ->filter(fn (GameEvent $event) => $event->{$enabledKey})
            ->map(function (GameEvent $event) use ($valueKey) {
                return [
                    'name' => $event->title,
                    'multiplier' => min(5, max(1, (float) $event->{$valueKey})),
                ];
            })
            ->filter(fn (array $event) => $event['multiplier'] > 1)
            ->values();

        $highest = (float) ($eligibleEvents->max('multiplier') ?? 1);

        return [
            'multiplier' => max(1, $highest),
            'events' => $eligibleEvents
                ->filter(fn (array $event) => $event['multiplier'] === $highest)
                ->values()
                ->all(),
        ];
    }
}
