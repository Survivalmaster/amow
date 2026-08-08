<?php

namespace App\Actions\Characters;

use App\Models\Character;
use App\Models\CharacterJobProgress;
use App\Models\GameJob;
use App\Models\GameJobDrop;
use App\Support\CharacterActivity;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkTieredJob
{
    public function execute(Character $character): array
    {
        $character->loadMissing(['currentJob.drops.item', 'inventory']);

        $job = $character->currentJob ?? GameJob::query()->where('is_starter', true)->firstOrFail();
        $cooldownEndsAt = $character->workCooldownEndsAt();

        if ($cooldownEndsAt?->isFuture()) {
            throw new RuntimeException('Work cooldown active. You can work again at '.$cooldownEndsAt->format('H:i').'.');
        }

        if ((int) ($character->stamina_points ?? 100) <= 0) {
            throw new RuntimeException('You are too exhausted to work. Sleep to restore stamina before taking another shift.');
        }

        $progress = CharacterJobProgress::query()->firstOrCreate([
            'character_id' => $character->id,
            'game_job_id' => $job->id,
        ], [
            'tier' => 1,
            'tier_experience' => 0,
        ]);

        $tier = max(1, min((int) $progress->tier, (int) ($job->max_tier ?? 20)));
        $payMultiplier = 1 + (($tier - 1) * ((int) ($job->tier_pay_bonus_percent ?? 0) / 100));
        $xpMultiplier = 1 + (($tier - 1) * ((int) ($job->tier_xp_bonus_percent ?? 0) / 100));
        $baseEarnings = random_int((int) $job->min_pay, (int) $job->max_pay);
        $earnings = (int) round($baseEarnings * $payMultiplier);
        $experienceEarned = (int) round(max(0, (int) ($job->experience_reward ?? 5)) * $xpMultiplier);
        $dropsAwarded = [];
        $tiersGained = 0;

        DB::transaction(function () use ($character, $job, $progress, $tier, $earnings, $experienceEarned, &$dropsAwarded, &$tiersGained) {
            $previousCredits = (int) $character->plastic_credits;
            $previousTier = (int) $progress->tier;
            $previousTierExperience = (int) $progress->tier_experience;
            $staminaDecrease = max(0, (int) ($job->stamina_decrease ?? 0));

            $character->increment('plastic_credits', $earnings);
            $character->forceFill([
                'last_worked_at' => now(),
                'stamina_points' => max(0, ($character->stamina_points ?? 100) - $staminaDecrease),
            ])->save();
            $character->gainExperience($experienceEarned);

            $tierExperience = $previousTierExperience + $experienceEarned;
            $newTier = $previousTier;
            $required = max(1, (int) ($job->tier_xp_required ?? 100));
            $maxTier = max(1, (int) ($job->max_tier ?? 20));

            while ($newTier < $maxTier && $tierExperience >= $required) {
                $tierExperience -= $required;
                $newTier++;
                $tiersGained++;
            }

            $progress->forceFill([
                'tier' => $newTier,
                'tier_experience' => $newTier >= $maxTier ? min($tierExperience, $required) : $tierExperience,
            ])->save();

            $dropsAwarded = $this->awardDrops($character->fresh('inventory'), $job, $tier);

            CharacterActivity::recordTransaction(
                $character,
                'tiered_work',
                $earnings,
                "Completed a {$job->name} shift at tier {$tier} and earned {$earnings} Plastic Credits.",
                [
                    'job' => $job->name,
                    'tier_before' => $previousTier,
                    'tier_after' => $newTier,
                    'tier_xp_before' => $previousTierExperience,
                    'tier_xp_after' => $progress->tier_experience,
                    'tier_xp_earned' => $experienceEarned,
                    'credits_before' => $previousCredits,
                    'credits_after' => $character->fresh()->plastic_credits,
                    'drops' => $dropsAwarded,
                ]
            );
        });

        return [
            'character' => $character->fresh(['currentJob.drops.item', 'jobProgress', 'inventory']),
            'job' => $job->fresh('drops.item'),
            'earnings' => $earnings,
            'experience_earned' => $experienceEarned,
            'drops' => $dropsAwarded,
            'tiers_gained' => $tiersGained,
        ];
    }

    private function awardDrops(Character $character, GameJob $job, int $tier): array
    {
        return $job->drops
            ->filter(fn (GameJobDrop $drop) => $tier >= $drop->min_tier && $tier <= $drop->max_tier)
            ->filter(fn (GameJobDrop $drop) => random_int(1, 10000) <= (int) round(((float) $drop->drop_chance_percent) * 100))
            ->map(function (GameJobDrop $drop) use ($character) {
                $quantity = random_int((int) $drop->min_quantity, (int) $drop->max_quantity);
                $item = $drop->item;

                if (! $item || ! $character->canStoreItemQuantity($item, $quantity)) {
                    return null;
                }

                $currentQuantity = (int) optional($character->inventory->firstWhere('id', $drop->item_id))->pivot?->quantity;

                $character->inventory()->syncWithoutDetaching([
                    $drop->item_id => ['quantity' => $currentQuantity + $quantity],
                ]);

                return [
                    'item_id' => $drop->item_id,
                    'name' => $drop->item?->name,
                    'quantity' => $quantity,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
