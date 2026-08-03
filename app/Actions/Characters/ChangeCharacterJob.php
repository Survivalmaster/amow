<?php

namespace App\Actions\Characters;

use App\Models\Character;
use App\Models\GameJob;
use App\Support\CharacterActivity;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChangeCharacterJob
{
    public function execute(Character $character, GameJob $gameJob): array
    {
        $character->loadMissing('currentJob');

        if (! $gameJob->is_active) {
            throw new RuntimeException('That job is currently unavailable.');
        }

        if ($character->current_job_id === $gameJob->id) {
            throw new RuntimeException('This character already has that job.');
        }

        if ($character->level < $gameJob->required_level) {
            throw new RuntimeException('Your level is too low for that job.');
        }

        if (! $character->canChangeJob()) {
            $availableAt = $character->job_changed_at?->copy()->addDay()->format('d M H:i');

            throw new RuntimeException('Job switch cooldown active until '.$availableAt.'.');
        }

        DB::transaction(function () use ($character, $gameJob) {
            $previousJob = $character->currentJob;

            $character->forceFill([
                'current_job_id' => $gameJob->id,
                'job_changed_at' => now(),
            ])->save();

            CharacterActivity::recordTransaction(
                $character,
                'job_change',
                0,
                'Changed job from '.($previousJob?->name ?? $character->starting_occupation).' to '.$gameJob->name.'.',
                [
                    'from_job' => $previousJob?->name ?? $character->starting_occupation,
                    'to_job' => $gameJob->name,
                    'required_level' => $gameJob->required_level,
                    'cooldown_minutes' => $gameJob->work_cooldown_minutes,
                ]
            );
        });

        return [
            'character' => $character->fresh(['currentJob', 'faction']),
            'job' => $gameJob,
            'message' => "Job changed to {$gameJob->name}.",
            'cooldown_ends_at' => now()->addDay(),
        ];
    }
}
