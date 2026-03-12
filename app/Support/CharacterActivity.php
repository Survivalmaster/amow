<?php

namespace App\Support;

use App\Models\Character;

class CharacterActivity
{
    public static function recordTransaction(
        Character $character,
        string $type,
        int $amount,
        string $description,
        ?array $metadata = null
    ): void {
        $character->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
