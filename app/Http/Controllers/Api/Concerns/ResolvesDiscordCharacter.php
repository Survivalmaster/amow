<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Character;
use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesDiscordCharacter
{
    private function authorizeDiscordRequest(Request $request): void
    {
        $secret = (string) config('services.discord.linking_secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Discord-Link-Secret'))) {
            abort(403);
        }
    }

    private function linkedCharacter(string $discordUserId, array $with = []): ?Character
    {
        $user = User::query()
            ->with(array_merge(['character'], array_map(fn (string $relation) => 'character.'.$relation, $with)))
            ->where('discord_user_id', $discordUserId)
            ->first();

        return $user?->character;
    }
}
