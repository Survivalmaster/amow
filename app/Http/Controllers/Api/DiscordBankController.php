<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesDiscordCharacter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordBankController extends Controller
{
    use ResolvesDiscordCharacter;

    public function __invoke(Request $request, string $discordUserId): JsonResponse
    {
        $this->authorizeDiscordRequest($request);

        $character = $this->linkedCharacter($discordUserId, ['faction', 'rank']);

        if (! $character) {
            return response()->json([
                'linked' => false,
                'message' => 'No AMOW character is linked to this Discord user.',
            ], 404);
        }

        return response()->json([
            'linked' => true,
            'character' => [
                'name' => $character->name,
                'faction' => $character->faction?->name,
                'faction_color' => $character->faction?->color,
                'rank' => $character->rank?->name,
                'credits' => $character->plastic_credits,
            ],
        ]);
    }
}
