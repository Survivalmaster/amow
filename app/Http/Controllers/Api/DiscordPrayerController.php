<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DiscordPrayerController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.discord.linking_secret');

        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Discord-Link-Secret'))) {
            abort(403);
        }

        $payload = Validator::make($request->all(), [
            'command_name' => ['required', 'string', 'max:32'],
            'deity' => ['required', 'in:Marble,Obsidian'],
            'user_mention' => ['required', 'string', 'max:255'],
        ])->validate();

        $command = DiscordCommand::query()
            ->where('command_name', $payload['command_name'])
            ->where('handler_key', 'pray_to_deity')
            ->where('is_active', true)
            ->first();

        if (! $command) {
            return response()->json([
                'message' => 'No prayer command is configured for this request.',
            ], 422);
        }

        $deity = $payload['deity'];
        $isBlessed = random_int(0, 1) === 1;
        $outcome = $isBlessed ? 'blessed' : 'smited';

        return response()->json([
            'message' => "{$payload['user_mention']} {$deity} has {$outcome} you.",
            'outcome' => $outcome,
            'deity' => $deity,
        ]);
    }
}
