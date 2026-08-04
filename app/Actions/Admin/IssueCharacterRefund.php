<?php

namespace App\Actions\Admin;

use App\Models\Character;
use App\Models\User;
use App\Support\CharacterActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueCharacterRefund
{
    public function execute(Character $character, User $admin, int $credits, int $experience, string $reason): Character
    {
        return DB::transaction(function () use ($character, $admin, $credits, $experience, $reason) {
            $character->refresh();

            $credits = max(0, $credits);
            $experience = max(0, $experience);
            $reason = trim($reason);
            $adminLabel = $admin->name ?: 'Admin #'.$admin->id;

            $creditsBefore = (int) $character->plastic_credits;
            $levelBefore = (int) $character->level;
            $xpBefore = (int) $character->experience_points;
            $levelsGained = 0;

            if ($credits > 0) {
                $character->increment('plastic_credits', $credits);
            }

            if ($experience > 0) {
                $levelsGained = $character->gainExperience($experience);
            }

            $character->refresh();

            CharacterActivity::recordTransaction(
                $character,
                'refund',
                $credits,
                $this->buildDescription($adminLabel, $credits, $experience, $reason),
                [
                    'admin_id' => $admin->id,
                    'admin' => $adminLabel,
                    'reason' => $reason,
                    'refund_credits' => $credits,
                    'credits_before' => $creditsBefore,
                    'credits_after' => (int) $character->plastic_credits,
                    'refund_xp' => $experience,
                    'xp_before' => $xpBefore,
                    'xp_after' => (int) $character->experience_points,
                    'level_before' => $levelBefore,
                    'level_after' => (int) $character->level,
                    'levels_gained' => $levelsGained,
                ]
            );

            return $character;
        });
    }

    private function buildDescription(string $adminLabel, int $credits, int $experience, string $reason): string
    {
        $parts = collect([
            $credits > 0 ? '+'.number_format($credits).' Plastic Credits' : null,
            $experience > 0 ? '+'.number_format($experience).' XP' : null,
        ])->filter()->implode(', ');

        return Str::limit("Refund issued by {$adminLabel}: {$parts}. Reason: {$reason}", 250);
    }
}
