<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordCommand;
use App\Services\Discord\DiscordWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DiscordWpnnController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request, DiscordWebhookService $webhooks): JsonResponse
    {
        $secret = (string) config('services.discord.linking_secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Discord-Link-Secret'))) {
            abort(403);
        }

        $payload = Validator::make($request->all(), [
            'headline' => ['required', 'string', 'max:120'],
            'announcement' => ['required', 'string', 'max:1900'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'author_name' => ['required', 'string', 'max:255'],
            'command_name' => ['required', 'string', 'max:32'],
        ])->validate();

        $command = DiscordCommand::query()
            ->with('webhook')
            ->where('command_name', $payload['command_name'])
            ->first();

        if (! $command) {
            return response()->json([
                'message' => 'No Discord command is configured for this request.',
            ], 422);
        }

        try {
            $webhooks->postWpnnAnnouncement(
                $command->webhook,
                $payload['headline'],
                $payload['announcement'],
                $payload['author_name'],
                $payload['image_url'] ?? null,
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Discord announcement posted.',
        ]);
    }
}
