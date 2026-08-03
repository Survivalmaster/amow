<?php

namespace App\Actions\Characters;

use App\Models\Character;
use App\Models\GameJob;
use App\Support\ActiveGameEventMultipliers;
use App\Services\Discord\DiscordClient;
use App\Support\CharacterActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class WorkCharacter
{
    private const WORK_ACTIVITY_CHANNEL_ID = '1483329516796379136';

    public function __construct(
        private readonly DiscordClient $discord,
        private readonly ActiveGameEventMultipliers $eventMultipliers,
    ) {}

    public function execute(Character $character): array
    {
        $character->loadMissing(['currentJob', 'faction', 'user']);

        $job = $character->currentJob ?? GameJob::query()->where('is_starter', true)->firstOrFail();
        $cooldownEndsAt = $character->workCooldownEndsAt();

        if ($cooldownEndsAt?->isFuture()) {
            throw new RuntimeException('Work cooldown active. You can work again at '.$cooldownEndsAt->format('H:i').'.');
        }

        $baseEarnings = random_int($job->min_pay, $job->max_pay);
        $baseExperienceEarned = max(0, (int) ($job->experience_reward ?? 5));
        $multipliers = $this->eventMultipliers->forCharacter($character);
        $creditMultiplier = (float) $multipliers['credits']['multiplier'];
        $xpMultiplier = (float) $multipliers['xp']['multiplier'];
        $earnings = (int) round($baseEarnings * $creditMultiplier);
        $experienceEarned = (int) round($baseExperienceEarned * $xpMultiplier);
        $staminaDecrease = max(0, (int) ($job->stamina_decrease ?? 0));
        $levelsGained = 0;

        DB::transaction(function () use ($character, $job, $baseEarnings, $baseExperienceEarned, $multipliers, $creditMultiplier, $xpMultiplier, $earnings, $experienceEarned, $staminaDecrease, &$levelsGained) {
            $previousCredits = (int) $character->plastic_credits;
            $previousLevel = (int) $character->level;
            $previousExperience = (int) $character->experience_points;
            $previousStamina = (int) ($character->stamina_points ?? 100);

            $character->increment('plastic_credits', $earnings);
            $character->forceFill([
                'last_worked_at' => now(),
                'stamina_points' => max(0, ($character->stamina_points ?? 100) - $staminaDecrease),
            ])->save();
            $levelsGained = $character->gainExperience($experienceEarned);
            $character->refresh();

            CharacterActivity::recordTransaction(
                $character,
                'work',
                $earnings,
                "Completed a {$job->name} shift and earned {$earnings} Plastic Credits.",
                [
                    'job' => $job->name,
                    'base_credits_earned' => $baseEarnings,
                    'credits_before' => $previousCredits,
                    'credits_after' => $character->plastic_credits,
                    'credit_multiplier' => $creditMultiplier,
                    'credit_multiplier_events' => $multipliers['credits']['events'],
                    'base_xp_earned' => $baseExperienceEarned,
                    'xp_earned' => $experienceEarned,
                    'xp_multiplier' => $xpMultiplier,
                    'xp_multiplier_events' => $multipliers['xp']['events'],
                    'level_before' => $previousLevel,
                    'level_after' => $character->level,
                    'xp_before' => $previousExperience,
                    'xp_after' => $character->experience_points,
                    'levels_gained' => $levelsGained,
                    'stamina_before' => $previousStamina,
                    'stamina_after' => $character->stamina_points,
                ]
            );
        });

        $updatedCharacter = $character->fresh(['currentJob', 'faction', 'user']);

        $this->sendDiscordWorkActivity($updatedCharacter, $job, $earnings, $multipliers);

        return [
            'character' => $updatedCharacter,
            'job' => $job,
            'earnings' => $earnings,
            'experience_earned' => $experienceEarned,
            'levels_gained' => $levelsGained,
            'cooldown_ends_at' => $updatedCharacter->workCooldownEndsAt(),
        ];
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
