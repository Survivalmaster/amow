<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordProfileController extends Controller
{
    public function __invoke(Request $request, string $discordUserId): JsonResponse
    {
        $secret = (string) config('services.discord.linking_secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Discord-Link-Secret'))) {
            abort(403);
        }

        $user = User::query()
            ->with([
                'character.faction',
                'character.rank',
                'character.licences',
                'character.holdings.company',
            ])
            ->where('discord_user_id', $discordUserId)
            ->first();

        if (! $user) {
            return response()->json([
                'linked' => false,
                'message' => 'No AMOW account is linked to this Discord user.',
            ], 404);
        }

        $character = $user->character;

        return response()->json([
            'linked' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'discord_username' => $user->discord_username,
            ],
            'character' => $character ? [
                'name' => $character->name,
                'faction' => $character->faction?->name,
                'rank' => $character->rank?->name,
                'role_type' => $character->role_type,
                'occupation' => $character->starting_occupation,
                'credits' => $character->plastic_credits,
                'health_points' => $character->health_points,
                'armor_points' => $character->armor_points,
                'business_owner' => $character->is_business_owner,
                'licences' => $character->licences->pluck('name')->values()->all(),
                'stock_holdings' => $character->holdings->map(fn ($holding) => [
                    'company' => $holding->company?->name,
                    'shares' => $holding->shares,
                ])->values()->all(),
            ] : null,
        ]);
    }
}
