<?php

namespace App\Http\Controllers\Api;

use App\Actions\Characters\WorkCharacter;
use App\Http\Controllers\Api\Concerns\ResolvesDiscordCharacter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DiscordWorkController extends Controller
{
    use ResolvesDiscordCharacter;

    public function __invoke(Request $request, WorkCharacter $workCharacter): JsonResponse
    {
        $this->authorizeDiscordRequest($request);

        $payload = $request->validate([
            'discord_user_id' => ['required', 'string', 'max:32'],
        ]);

        $character = $this->linkedCharacter($payload['discord_user_id'], ['currentJob', 'faction', 'user']);

        if (! $character) {
            return response()->json([
                'linked' => false,
                'message' => 'No AMOW character is linked to this Discord user.',
            ], 404);
        }

        try {
            $result = $workCharacter->execute($character);
        } catch (RuntimeException $exception) {
            return response()->json([
                'linked' => true,
                'worked' => false,
                'message' => $exception->getMessage(),
                'cooldown_ends_at' => $character->workCooldownEndsAt()?->toIso8601String(),
            ], 422);
        }

        return response()->json([
            'linked' => true,
            'worked' => true,
            'message' => 'Shift complete.',
            'earnings' => $result['earnings'],
            'experience_earned' => $result['experience_earned'],
            'levels_gained' => $result['levels_gained'],
            'cooldown_ends_at' => $result['cooldown_ends_at']?->toIso8601String(),
            'character' => [
                'name' => $result['character']->name,
                'job' => $result['job']->name,
                'credits' => $result['character']->plastic_credits,
                'level' => $result['character']->level,
                'experience_points' => $result['character']->experience_points,
                'stamina_points' => $result['character']->stamina_points,
            ],
        ]);
    }
}
