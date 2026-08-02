<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesDiscordCharacter;
use App\Http\Controllers\Controller;
use App\Models\GameJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordJobsController extends Controller
{
    use ResolvesDiscordCharacter;

    public function __invoke(Request $request, string $discordUserId): JsonResponse
    {
        $this->authorizeDiscordRequest($request);

        $character = $this->linkedCharacter($discordUserId, ['currentJob', 'faction']);

        if (! $character) {
            return response()->json([
                'linked' => false,
                'message' => 'No AMOW character is linked to this Discord user.',
            ], 404);
        }

        $jobCooldownEndsAt = $character->job_changed_at?->copy()->addDay();
        $canChangeJob = $character->canChangeJob();

        return response()->json([
            'linked' => true,
            'character' => [
                'name' => $character->name,
                'faction' => $character->faction?->name,
                'faction_color' => $character->faction?->color,
                'level' => $character->level,
                'current_job_id' => $character->current_job_id,
                'current_job' => $character->displayed_job_name,
                'can_change_job' => $canChangeJob,
                'job_change_cooldown_ends_at' => $canChangeJob ? null : $jobCooldownEndsAt?->toIso8601String(),
            ],
            'jobs' => GameJob::query()
                ->orderBy('required_level')
                ->orderBy('name')
                ->get()
                ->map(fn (GameJob $job) => [
                    'id' => $job->id,
                    'name' => $job->name,
                    'description' => $job->description,
                    'min_pay' => $job->min_pay,
                    'max_pay' => $job->max_pay,
                    'required_level' => $job->required_level,
                    'work_cooldown_minutes' => $job->work_cooldown_minutes,
                    'experience_reward' => $job->experience_reward,
                    'is_active' => $job->is_active,
                    'is_current' => $character->current_job_id === $job->id,
                    'can_choose' => $job->is_active
                        && $character->current_job_id !== $job->id
                        && $character->level >= $job->required_level
                        && $canChangeJob,
                ])
                ->values(),
        ]);
    }
}
