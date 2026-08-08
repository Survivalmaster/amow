<?php

namespace App\Actions\Characters;

use App\Models\Character;
use App\Models\CharacterJobProgress;
use App\Models\GameJob;
use App\Models\GameJobDrop;
use App\Services\Discord\DiscordClient;
use App\Support\ActiveGameEventMultipliers;
use App\Support\CharacterActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class WorkTieredJob
{
    private const WORK_ACTIVITY_CHANNEL_ID = '1483329516796379136';

    public function __construct(
        private readonly DiscordClient $discord,
        private readonly ActiveGameEventMultipliers $eventMultipliers,
    ) {}

    public function execute(Character $character): array
    {
        $character->loadMissing(['currentJob.drops.item', 'faction', 'inventory', 'user']);

        $job = $character->currentJob ?? GameJob::query()->where('is_starter', true)->firstOrFail();
        $cooldownEndsAt = $character->workCooldownEndsAt();

        if ($cooldownEndsAt?->isFuture()) {
            throw new RuntimeException('Work cooldown active. You can work again at '.$cooldownEndsAt->format('H:i').'.');
        }

        if ((int) ($character->stamina_points ?? 100) <= 0) {
            throw new RuntimeException('You are too exhausted to work. Sleep to restore stamina before taking another shift.');
        }

        $hasTiers = (int) ($job->max_tier ?? 20) > 0;
        $progress = CharacterJobProgress::query()->firstOrCreate([
            'character_id' => $character->id,
            'game_job_id' => $job->id,
        ], [
            'tier' => $hasTiers ? 1 : 0,
            'tier_experience' => 0,
        ]);

        $tier = $hasTiers ? max(1, min((int) $progress->tier, (int) $job->max_tier)) : 0;
        $payMultiplier = $hasTiers ? 1 + (($tier - 1) * ((int) ($job->tier_pay_bonus_percent ?? 0) / 100)) : 1;
        $xpMultiplier = $hasTiers ? 1 + (($tier - 1) * ((int) ($job->tier_xp_bonus_percent ?? 0) / 100)) : 1;
        $baseEarnings = random_int((int) $job->min_pay, (int) $job->max_pay);
        $baseExperienceEarned = max(0, (int) ($job->experience_reward ?? 5));
        $multipliers = $this->eventMultipliers->forCharacter($character);
        $creditMultiplier = (float) $multipliers['credits']['multiplier'];
        $eventXpMultiplier = (float) $multipliers['xp']['multiplier'];
        $earnings = (int) round($baseEarnings * $payMultiplier * $creditMultiplier);
        $experienceEarned = (int) round($baseExperienceEarned * $xpMultiplier * $eventXpMultiplier);
        $dropsAwarded = [];
        $tiersGained = 0;
        $levelsGained = 0;

        DB::transaction(function () use ($character, $job, $progress, $tier, $hasTiers, $baseEarnings, $baseExperienceEarned, $payMultiplier, $xpMultiplier, $multipliers, $creditMultiplier, $eventXpMultiplier, $earnings, $experienceEarned, &$dropsAwarded, &$tiersGained, &$levelsGained) {
            $previousCredits = (int) $character->plastic_credits;
            $previousLevel = (int) $character->level;
            $previousExperience = (int) $character->experience_points;
            $previousTier = (int) $progress->tier;
            $previousTierExperience = (int) $progress->tier_experience;
            $previousStamina = (int) ($character->stamina_points ?? 100);
            $staminaDecrease = max(0, (int) ($job->stamina_decrease ?? 0));

            $character->increment('plastic_credits', $earnings);
            $character->forceFill([
                'last_worked_at' => now(),
                'stamina_points' => max(0, ($character->stamina_points ?? 100) - $staminaDecrease),
            ])->save();
            $levelsGained = $character->gainExperience($experienceEarned);
            $character->refresh();

            $tierExperience = $hasTiers ? $previousTierExperience + $experienceEarned : 0;
            $newTier = $hasTiers ? $previousTier : 0;
            $required = max(1, (int) ($job->tier_xp_required ?? 100));
            $maxTier = max(0, (int) ($job->max_tier ?? 20));

            while ($hasTiers && $newTier < $maxTier && $tierExperience >= $required) {
                $tierExperience -= $required;
                $newTier++;
                $tiersGained++;
            }

            $progress->forceFill([
                'tier' => $newTier,
                'tier_experience' => $hasTiers && $newTier >= $maxTier ? min($tierExperience, $required) : $tierExperience,
            ])->save();

            $dropsAwarded = $this->awardDrops($character->fresh('inventory'), $job, $tier);

            CharacterActivity::recordTransaction(
                $character,
                'work',
                $earnings,
                $hasTiers
                    ? "Completed a {$job->name} shift at tier {$tier} and earned {$earnings} Plastic Credits."
                    : "Completed a {$job->name} shift and earned {$earnings} Plastic Credits.",
                [
                    'job' => $job->name,
                    'base_credits_earned' => $baseEarnings,
                    'credits_before' => $previousCredits,
                    'credits_after' => $character->plastic_credits,
                    'credit_multiplier' => $creditMultiplier,
                    'credit_multiplier_events' => $multipliers['credits']['events'],
                    'tier_credit_multiplier' => $payMultiplier,
                    'base_xp_earned' => $baseExperienceEarned,
                    'xp_earned' => $experienceEarned,
                    'xp_multiplier' => $eventXpMultiplier,
                    'xp_multiplier_events' => $multipliers['xp']['events'],
                    'tier_xp_multiplier' => $xpMultiplier,
                    'level_before' => $previousLevel,
                    'level_after' => $character->level,
                    'xp_before' => $previousExperience,
                    'xp_after' => $character->experience_points,
                    'levels_gained' => $levelsGained,
                    'tier_before' => $previousTier,
                    'tier_after' => $newTier,
                    'tier_xp_before' => $previousTierExperience,
                    'tier_xp_after' => $progress->tier_experience,
                    'tier_xp_earned' => $experienceEarned,
                    'stamina_before' => $previousStamina,
                    'stamina_after' => $character->stamina_points,
                    'drops' => $dropsAwarded,
                ]
            );
        });

        $updatedCharacter = $character->fresh(['currentJob.drops.item', 'faction', 'jobProgress', 'inventory', 'user']);

        $this->sendDiscordWorkActivity($updatedCharacter, $job, $earnings, $multipliers);

        return [
            'character' => $updatedCharacter,
            'job' => $job->fresh('drops.item'),
            'earnings' => $earnings,
            'experience_earned' => $experienceEarned,
            'drops' => $dropsAwarded,
            'tiers_gained' => $tiersGained,
            'levels_gained' => $levelsGained,
            'cooldown_ends_at' => $updatedCharacter->workCooldownEndsAt(),
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

    private function sendDiscordWorkActivity(Character $character, GameJob $job, int $earnings, array $multipliers): void
    {
        $workActivityMessage = $job->working_display_message ?: 'Is working.';
        $discordActivityMessage = $this->normalizeWorkActivityMessage($workActivityMessage);
        $eventBonusText = $this->formatDiscordEventBonusText($multipliers);
        $description = sprintf(
            "They have earned **%s** credits.\nTheir total now is **%s**.",
            number_format($earnings),
            number_format($character->plastic_credits)
        );

        if ($eventBonusText !== '') {
            $description .= "\n\n**Event bonus:** {$eventBonusText}";
        }

        try {
            $this->discord->sendEmbedMessage(
                self::WORK_ACTIVITY_CHANNEL_ID,
                [
                    'author' => array_filter([
                        'name' => $character->name,
                        'icon_url' => $character->user?->discord_avatar_url,
                    ]),
                    'title' => sprintf('%s is %s', $character->name, $discordActivityMessage),
                    'description' => $description,
                    'color' => $this->resolveDiscordEmbedColor($character->faction?->color),
                    'footer' => [
                        'text' => 'AMOW Work Activity',
                    ],
                    'timestamp' => now()->toIso8601String(),
                ]
            );
        } catch (Throwable $exception) {
            Log::warning('Work activity Discord message failed to send.', [
                'character_id' => $character->id,
                'channel_id' => self::WORK_ACTIVITY_CHANNEL_ID,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function normalizeWorkActivityMessage(string $message): string
    {
        $message = trim($message);

        if ($message === '') {
            return 'working.';
        }

        $message = preg_replace('/^is\s+/i', '', $message) ?? $message;

        return Str::of($message)->trim()->lower()->toString();
    }

    private function formatDiscordEventBonusText(array $multipliers): string
    {
        $bonuses = collect([
            $this->formatBonusLine('XP', $multipliers['xp'] ?? []),
            $this->formatBonusLine('Credits', $multipliers['credits'] ?? []),
        ])->filter();

        return $bonuses->implode(' | ');
    }

    private function formatBonusLine(string $label, array $details): ?string
    {
        $multiplier = (float) ($details['multiplier'] ?? 1);
        $eventNames = collect($details['events'] ?? [])->pluck('name')->filter()->implode(', ');

        if ($multiplier <= 1 || $eventNames === '') {
            return null;
        }

        return sprintf('%s %sx from %s', $label, $this->formatMultiplier($multiplier), $eventNames);
    }

    private function formatMultiplier(float $multiplier): string
    {
        return rtrim(rtrim(number_format($multiplier, 2, '.', ''), '0'), '.');
    }

    private function resolveDiscordEmbedColor(?string $hexColor): int
    {
        $normalizedHexColor = strtoupper(ltrim((string) $hexColor, '#'));

        if (! preg_match('/^[0-9A-F]{6}$/', $normalizedHexColor)) {
            return hexdec('7EAD59');
        }

        return hexdec($normalizedHexColor);
    }
}
