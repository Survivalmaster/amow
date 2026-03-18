<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\GlobalChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalChatController extends Controller
{
    public function index(): JsonResponse
    {
        $messages = GlobalChatMessage::query()
            ->with([
                'character.rank',
                'character.faction',
                'character.user.permissions',
            ])
            ->latest()
            ->limit(40)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (GlobalChatMessage $message) => $this->formatMessage($message));

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Character $character */
        $character = $request->user()->character()->with(['user.permissions'])->firstOrFail();

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:400'],
        ]);

        $message = GlobalChatMessage::query()->create([
            'character_id' => $character->id,
            'message' => trim($validated['message']),
        ]);

        $message->load(['character.rank', 'character.faction', 'character.user.permissions']);

        return response()->json([
            'message' => $this->formatMessage($message),
        ]);
    }

    private function formatMessage(GlobalChatMessage $message): array
    {
        $character = $message->character;
        $user = $character->user;
        [$displayMessage, $messageType] = $this->transformMessage($message->message, $character->name);

        return [
            'id' => $message->id,
            'message' => $message->message,
            'display_message' => $displayMessage,
            'message_type' => $messageType,
            'created_at' => $message->created_at?->timezone(config('app.timezone'))->format('H:i') ?? now()->format('H:i'),
            'character_name' => $character->name,
            'rank_name' => $character->rank?->name ?? 'Unranked',
            'faction_color' => $character->faction?->color ?: '#f4ecd0',
            'account_icons' => $user->permissionIcons()->map(fn ($icon) => [
                'name' => $icon->name,
                'tooltip' => $icon->tooltip ?: $icon->name,
                'icon_value' => $icon->icon_value,
                'color' => $icon->color ?: '#f4ecd0',
            ])->values()->all(),
        ];
    }

    private function transformMessage(string $message, string $characterName): array
    {
        $trimmed = trim($message);

        if (preg_match('/^\/me\s+(.+)$/is', $trimmed, $matches)) {
            return [trim($characterName.' '.$matches[1]), 'emote'];
        }

        if (preg_match('/^\/do\s+(.+)$/is', $trimmed, $matches)) {
            return [trim($matches[1]).' ('.$characterName.')', 'description'];
        }

        return [$trimmed, 'standard'];
    }
}
