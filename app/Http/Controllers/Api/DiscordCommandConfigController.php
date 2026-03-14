<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordCommand;
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

        $commands = DiscordCommand::query()
            ->with('webhook')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (DiscordCommand $command) => [
                'id' => $command->id,
                'name' => $command->name,
                'command_name' => $command->command_name,
                'command_description' => $command->command_description ?: "Post to {$command->webhook->name}.",
                'channel_id' => $command->webhook->channel_id,
                'access_mode' => $command->access_mode,
                'role_id' => $command->role_id,
            ])
            ->values();

        return response()->json([
            'commands' => $commands,
        ]);
    }
}
