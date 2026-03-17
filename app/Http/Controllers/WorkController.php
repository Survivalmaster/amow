<?php

namespace App\Http\Controllers;

use App\Jobs\SendDiscordChannelMessage;
use App\Models\GameJob;
use App\Models\Location;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkController extends Controller
{
    private const WORK_ACTIVITY_CHANNEL_ID = '1483329516796379136';

    public function store(Request $request, Location $location): RedirectResponse
    {
        abort_unless($location->slug === 'go-to-work', 403);

        $character = $request->user()->character()->with('currentJob')->firstOrFail();
        $job = $character->currentJob ?? GameJob::query()->where('is_starter', true)->firstOrFail();
        $cooldownEndsAt = $character->workCooldownEndsAt();

        if ($cooldownEndsAt?->isFuture()) {
            return back()->withErrors([
                'work' => 'Work cooldown active. You can work again at '.$cooldownEndsAt->format('H:i'),
            ]);
        }

        $earnings = random_int($job->min_pay, $job->max_pay);
        $experienceEarned = 5;
        $staminaDecrease = max(0, (int) ($job->stamina_decrease ?? 0));
        $levelsGained = 0;

        DB::transaction(function () use ($character, $earnings, $experienceEarned, $staminaDecrease, &$levelsGained) {
            $character->increment('plastic_credits', $earnings);
            $character->forceFill([
                'last_worked_at' => now(),
                'stamina_points' => max(0, ($character->stamina_points ?? 100) - $staminaDecrease),
            ])->save();
            $levelsGained = $character->gainExperience($experienceEarned);
            CharacterActivity::recordTransaction($character, 'work', $earnings, 'Completed a work shift.');
        });

        $updatedCharacter = $character->fresh(['currentJob']);
        $currentLevel = $updatedCharacter->level;
        $workActivityMessage = $job->working_display_message ?: 'Is working.';

        SendDiscordChannelMessage::dispatch(
            self::WORK_ACTIVITY_CHANNEL_ID,
            sprintf(
                '%s %s They have earned %s credits, their total now is %s.',
                $updatedCharacter->name,
                $workActivityMessage,
                number_format($earnings),
                number_format($updatedCharacter->plastic_credits)
            )
        )->afterCommit();

        $levelMessage = $levelsGained > 0 ? " Level up! You reached level {$currentLevel}." : '';

        return back()->with('status', "Shift complete. You earned {$earnings} Plastic Credits and {$experienceEarned} XP.".$levelMessage);
    }
}
