<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CharacterStateController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var Character $character */
        $character = $request->user()->character()->with(['rank', 'currentJob'])->firstOrFail();
        $workCooldownEndsAt = $character->workCooldownEndsAt();
        $nextLevelExperience = $character->experienceRequiredForNextLevel();

        return response()->json([
            'name' => $character->name,
            'rank_name' => $character->rank?->name ?? 'Unranked',
            'displayed_job_name' => $character->displayed_job_name,
            'level' => $character->level,
            'experience_points' => $character->experience_points,
            'next_level_experience' => $nextLevelExperience,
            'experience_label' => $character->experience_points.'/'.$nextLevelExperience,
            'experience_progress_percent' => min(100, (int) round(($character->experience_points / max(1, $nextLevelExperience)) * 100)),
            'plastic_credits' => $character->plastic_credits,
            'formatted_credits' => $this->formatCredits($character->plastic_credits),
            'health_points' => $character->health_points ?? 100,
            'health_label' => ($character->health_points ?? 100).'/100',
            'stamina_points' => $character->stamina_points ?? 100,
            'stamina_label' => ($character->stamina_points ?? 100).'/100',
            'stamina_percent' => max(0, min(100, (int) ($character->stamina_points ?? 100))),
            'armor_points' => $character->armor_points ?? 0,
            'work_cooldown_active' => $workCooldownEndsAt?->isFuture() ?? false,
            'work_available_at_iso' => $workCooldownEndsAt?->toIso8601String(),
            'work_remaining_seconds' => $workCooldownEndsAt && $workCooldownEndsAt->isFuture()
                ? now()->diffInSeconds($workCooldownEndsAt)
                : 0,
            'can_change_job' => $character->canChangeJob(),
            'job_change_available_at_iso' => $character->job_changed_at?->copy()->addDay()->toIso8601String(),
        ]);
    }

    private function formatCredits(int $amount): string
    {
        return match (true) {
            $amount >= 1000000 => rtrim(rtrim(number_format($amount / 1000000, 1), '0'), '.').'M',
            $amount >= 100000 => rtrim(rtrim(number_format($amount / 1000, 1), '0'), '.').'K',
            default => number_format($amount),
        };
    }
}
