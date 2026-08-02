<?php

namespace App\Http\Controllers\Api;

use App\Actions\Characters\ChangeCharacterJob;
use App\Http\Controllers\Api\Concerns\ResolvesDiscordCharacter;
use App\Http\Controllers\Controller;
use App\Models\GameJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DiscordJobChangeController extends Controller
{
    use ResolvesDiscordCharacter;

    public function __invoke(Request $request, ChangeCharacterJob $changeCharacterJob): JsonResponse
    {
        $this->authorizeDiscordRequest($request);

        $payload = $request->validate([
            'discord_user_id' => ['required', 'string', 'max:32'],
            'job_id' => ['required', 'integer'],
        ]);

        $character = $this->linkedCharacter($payload['discord_user_id'], ['currentJob', 'faction']);

        if (! $character) {
            return response()->json([
                'linked' => false,
                'message' => 'No AMOW character is linked to this Discord user.',
            ], 404);
        }

        $job = GameJob::query()->findOrFail($payload['job_id']);

        try {
            $result = $changeCharacterJob->execute($character, $job);
        } catch (RuntimeException $exception) {
            return response()->json([
                'linked' => true,
                'changed' => false,
                'message' => $exception->getMessage(),
                'job_change_cooldown_ends_at' => $character->job_changed_at?->copy()->addDay()?->toIso8601String(),
            ], 422);
        }

        return response()->json([
            'linked' => true,
            'changed' => true,
            'message' => $result['message'],
            'job_change_cooldown_ends_at' => $result['cooldown_ends_at']?->toIso8601String(),
            'character' => [
                'name' => $result['character']->name,
                'faction' => $result['character']->faction?->name,
                'faction_color' => $result['character']->faction?->color,
                'level' => $result['character']->level,
                'current_job_id' => $result['character']->current_job_id,
                'current_job' => $result['character']->displayed_job_name,
            ],
            'job' => [
                'id' => $result['job']->id,
                'name' => $result['job']->name,
                'min_pay' => $result['job']->min_pay,
                'max_pay' => $result['job']->max_pay,
                'work_cooldown_minutes' => $result['job']->work_cooldown_minutes,
                'experience_reward' => $result['job']->experience_reward,
            ],
        ]);
    }
}
