<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Discord\DiscordLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DiscordLinkCompletionController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request, DiscordLinkService $linkService): JsonResponse
    {
        $secret = (string) config('services.discord.linking_secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Discord-Link-Secret'))) {
            abort(403);
        }

        $payload = Validator::make($request->all(), [
            'token' => ['required', 'string', 'size:12'],
            'discord_user_id' => ['required', 'string', 'max:255'],
            'discord_username' => ['required', 'string', 'max:255'],
            'discord_avatar' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $user = User::query()
            ->where('discord_link_token', strtoupper($payload['token']))
            ->where('discord_link_token_expires_at', '>', now())
            ->first();

        if (! $user) {
            return response()->json([
                'message' => 'Invalid or expired link token.',
            ], 422);
        }

        User::query()
            ->where('discord_user_id', $payload['discord_user_id'])
            ->whereKeyNot($user->getKey())
            ->update([
                'discord_user_id' => null,
                'discord_username' => null,
                'discord_avatar' => null,
                'discord_linked_at' => null,
            ]);

        $linkService->completeLink(
            $user,
            $payload['discord_user_id'],
            $payload['discord_username'],
            $payload['discord_avatar'] ?? null,
        );

        return response()->json([
            'message' => 'Discord account linked.',
            'user_id' => $user->getKey(),
        ]);
    }
}
