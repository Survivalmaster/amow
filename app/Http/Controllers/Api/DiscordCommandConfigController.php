<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordCommandConfigController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.discord.linking_secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Discord-Link-Secret'))) {
            abort(403);
        }

        $commands = DiscordWebhook::query()
            ->where('is_active', true)
            ->whereNotNull('command_name')
            ->orderBy('name')
            ->get()
            ->map(fn (DiscordWebhook $webhook) => [
                'id' => $webhook->id,
                'name' => $webhook->name,
                'command_name' => $webhook->command_name,
                'command_description' => $webhook->command_description ?: "Post to {$webhook->name}.",
                'channel_id' => $webhook->channel_id,
                'access_mode' => $webhook->access_mode,
                'role_id' => $webhook->role_id,
            ])
            ->values();

        return response()->json([
            'commands' => $commands,
        ]);
    }
}
