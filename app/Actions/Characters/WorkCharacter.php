<?php

namespace App\Actions\Characters;

use App\Models\Character;
use App\Models\GameJob;
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

    public function __construct(private readonly DiscordClient $discord)
    {
    }

    public function execute(Character $character): array
    {
        $character->loadMissing(['currentJob', 'faction', 'user']);

        $job = $character->currentJob ?? GameJob::query()->where('is_starter', true)->firstOrFail();
        $cooldownEndsAt = $character->workCooldownEndsAt();

        if ($cooldownEndsAt?->isFuture()) {
            throw new RuntimeException('Work cooldown active. You can work again at '.$cooldownEndsAt->format('H:i').'.');
        }

        $earnings = random_int($job->min_pay, $job->max_pay);
        $experienceEarned = max(0, (int) ($job->experience_reward ?? 5));
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

        $updatedCharacter = $character->fresh(['currentJob', 'faction', 'user']);

        $this->sendDiscordWorkActivity($updatedCharacter, $job, $earnings);

        return [
            'character' => $updatedCharacter,
            'job' => $job,
            'earnings' => $earnings,
            'experience_earned' => $experienceEarned,
            'levels_gained' => $levelsGained,
            'cooldown_ends_at' => $updatedCharacter->workCooldownEndsAt(),
        ];
    }

    private function sendDiscordWorkActivity(Character $character, GameJob $job, int $earnings): void
    {
        $workActivityMessage = $job->working_display_message ?: 'Is working.';
        $discordActivityMessage = $this->normalizeWorkActivityMessage($workActivityMessage);

        try {
            $this->discord->sendEmbedMessage(
                self::WORK_ACTIVITY_CHANNEL_ID,
                [
                    'author' => array_filter([
                        'name' => $character->name,
                        'icon_url' => $character->user?->discord_avatar_url,
                    ]),
                    'title' => sprintf('%s is %s', $character->name, $discordActivityMessage),
                    'description' => sprintf(
                        "They have earned **%s** credits.\nTheir total now is **%s**.",
                        number_format($earnings),
                        number_format($character->plastic_credits)
                    ),
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

    private function resolveDiscordEmbedColor(?string $hexColor): int
    {
        $normalizedHexColor = strtoupper(ltrim((string) $hexColor, '#'));

        if (! preg_match('/^[0-9A-F]{6}$/', $normalizedHexColor)) {
            return hexdec('7EAD59');
        }

        return hexdec($normalizedHexColor);
    }
}
