<?php

namespace App\Http\Controllers;

use App\Models\GameJob;
use App\Models\Location;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkController extends Controller
{
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
        $levelsGained = 0;

        DB::transaction(function () use ($character, $earnings, $experienceEarned, &$levelsGained) {
            $character->increment('plastic_credits', $earnings);
            $character->forceFill(['last_worked_at' => now()])->save();
            $levelsGained = $character->gainExperience($experienceEarned);
            CharacterActivity::recordTransaction($character, 'work', $earnings, 'Completed a work shift.');
        });

        $currentLevel = $character->fresh()->level;
        $levelMessage = $levelsGained > 0 ? " Level up! You reached level {$currentLevel}." : '';

        return back()->with('status', "Shift complete. You earned {$earnings} Plastic Credits and {$experienceEarned} XP.".$levelMessage);
    }
}
