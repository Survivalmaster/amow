<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Changelog;
use App\Services\Discord\DiscordWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordChangelogController extends Controller
{
    public function pending(Request $request, DiscordWebhookService $discordWebhookService): JsonResponse
    {
        $this->authorizeBot($request);

        $changelogs = Changelog::query()
            ->released()
            ->whereNull('discord_message_sent_at')
            ->whereNotNull('discord_channel_id')
            ->oldest('released_at')
            ->oldest()
            ->limit(5)
            ->get()
            ->map(fn (Changelog $changelog): array => [
                'id' => $changelog->id,
                'channel_id' => $changelog->discord_channel_id,
                'embed' => $discordWebhookService->changelogEmbed($changelog),
            ]);

        return response()->json([
            'changelogs' => $changelogs,
        ]);
    }

    public function markSent(Request $request, Changelog $changelog): JsonResponse
    {
        $this->authorizeBot($request);

        $changelog->forceFill([
            'discord_message_sent_at' => now(),
        ])->save();

        return response()->json([
            'ok' => true,
        ]);
    }

    private function authorizeBot(Request $request): void
    {
        $secret = config('services.discord.bot_sync_secret') ?: config('services.discord.linking_secret');

        abort_if(blank($secret), 403);
        abort_unless(hash_equals((string) $secret, (string) $request->header('X-Discord-Sync-Secret')), 403);
    }
}
