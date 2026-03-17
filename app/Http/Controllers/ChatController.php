<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\DirectChatMessage;
use App\Models\GlobalChatMessage;
use App\Models\NationChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function state(Request $request): JsonResponse
    {
        $character = $request->user()->character()->with([
            'faction',
            'user.permissions.accountIcon',
        ])->firstOrFail();

        $request->user()->touchPresence();

        $onlineCharacters = Character::query()
            ->with(['faction', 'user.permissions.accountIcon'])
            ->whereKeyNot($character->id)
            ->whereHas('user', fn ($query) => $query->where('last_seen_at', '>=', now()->subMinutes(5)))
            ->orderBy('name')
            ->get();

        $selectedDirectCharacter = $this->resolveSelectedDirectCharacter($request, $character, $onlineCharacters);

        return response()->json([
            'world_messages' => $this->formatWorldMessages(),
            'nation_messages' => $this->formatNationMessages($character),
            'direct_messages' => $this->formatDirectMessages($character, $selectedDirectCharacter),
            'online_characters' => $onlineCharacters->map(fn (Character $onlineCharacter) => $this->formatPresenceCharacter($onlineCharacter))->values(),
            'selected_direct_character_id' => $selectedDirectCharacter?->id,
            'online_count' => $onlineCharacters->count() + 1,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $character = $request->user()->character()->with([
            'faction',
            'user.permissions.accountIcon',
        ])->firstOrFail();

        $request->user()->touchPresence();

        $validated = $request->validate([
            'channel' => ['required', 'in:world,nation,direct'],
            'message' => ['required', 'string', 'min:1', 'max:400'],
            'target_character_id' => ['nullable', 'integer', 'exists:characters,id'],
        ]);

        $payloadMessage = trim($validated['message']);

        if ($validated['channel'] === 'world') {
            $message = GlobalChatMessage::query()->create([
                'character_id' => $character->id,
                'message' => $payloadMessage,
            ]);

            $message->load(['character.faction', 'character.user.permissions.accountIcon']);

            return response()->json([
                'message' => $this->formatChatMessage($message->character, $message->message, $message->id, $message->created_at),
            ]);
        }

        if ($validated['channel'] === 'nation') {
            $message = NationChatMessage::query()->create([
                'character_id' => $character->id,
                'faction_id' => $character->faction_id,
                'message' => $payloadMessage,
            ]);

            $message->load(['character.faction', 'character.user.permissions.accountIcon']);

            return response()->json([
                'message' => $this->formatChatMessage($message->character, $message->message, $message->id, $message->created_at),
            ]);
        }

        $targetCharacter = Character::query()
            ->with(['faction', 'user.permissions.accountIcon'])
            ->whereKey($validated['target_character_id'])
            ->firstOrFail();

        abort_if($targetCharacter->id === $character->id, 422, 'You cannot message yourself.');

        $message = DirectChatMessage::query()->create([
            'sender_character_id' => $character->id,
            'recipient_character_id' => $targetCharacter->id,
            'message' => $payloadMessage,
        ]);

        $message->load(['sender.faction', 'sender.user.permissions.accountIcon']);

        return response()->json([
            'message' => $this->formatChatMessage($message->sender, $message->message, $message->id, $message->created_at),
            'selected_direct_character_id' => $targetCharacter->id,
        ]);
    }

    private function formatWorldMessages()
    {
        return GlobalChatMessage::query()
            ->with(['character.faction', 'character.user.permissions.accountIcon'])
            ->latest()
            ->limit(35)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (GlobalChatMessage $message) => $this->formatChatMessage($message->character, $message->message, $message->id, $message->created_at));
    }

    private function formatNationMessages(Character $character)
    {
        return NationChatMessage::query()
            ->with(['character.faction', 'character.user.permissions.accountIcon'])
            ->where('faction_id', $character->faction_id)
            ->latest()
            ->limit(35)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (NationChatMessage $message) => $this->formatChatMessage($message->character, $message->message, $message->id, $message->created_at));
    }

    private function formatDirectMessages(Character $character, ?Character $selectedDirectCharacter)
    {
        if (! $selectedDirectCharacter) {
            return collect();
        }

        return DirectChatMessage::query()
            ->with([
                'sender.faction',
                'sender.user.permissions.accountIcon',
            ])
            ->where(function ($query) use ($character, $selectedDirectCharacter) {
                $query
                    ->where('sender_character_id', $character->id)
                    ->where('recipient_character_id', $selectedDirectCharacter->id);
            })
            ->orWhere(function ($query) use ($character, $selectedDirectCharacter) {
                $query
                    ->where('sender_character_id', $selectedDirectCharacter->id)
                    ->where('recipient_character_id', $character->id);
            })
            ->latest()
            ->limit(35)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (DirectChatMessage $message) => $this->formatChatMessage($message->sender, $message->message, $message->id, $message->created_at));
    }

    private function resolveSelectedDirectCharacter(Request $request, Character $character, $onlineCharacters): ?Character
    {
        $selectedId = (int) $request->integer('direct_character_id');

        if ($selectedId > 0) {
            $selected = $onlineCharacters->firstWhere('id', $selectedId);

            if ($selected) {
                return $selected;
            }
        }

        $latestPartnerId = DirectChatMessage::query()
            ->where('sender_character_id', $character->id)
            ->orWhere('recipient_character_id', $character->id)
            ->latest()
            ->get()
            ->map(function (DirectChatMessage $message) use ($character) {
                return $message->sender_character_id === $character->id
                    ? $message->recipient_character_id
                    : $message->sender_character_id;
            })
            ->first(fn ($partnerId) => $onlineCharacters->contains('id', $partnerId));

        return $latestPartnerId
            ? $onlineCharacters->firstWhere('id', $latestPartnerId)
            : $onlineCharacters->first();
    }

    private function formatChatMessage(Character $character, string $message, int $messageId, $createdAt): array
    {
        [$displayMessage, $messageType] = $this->transformMessage($message, $character->name);

        return [
            'id' => $messageId,
            'message' => $message,
            'display_message' => $displayMessage,
            'message_type' => $messageType,
            'created_at' => $createdAt?->timezone(config('app.timezone'))->format('H:i') ?? now()->format('H:i'),
            'character_name' => $character->name,
            'faction_color' => $character->faction?->color ?: '#f4ecd0',
            'account_icons' => $character->user->permissionIcons()->map(fn ($icon) => [
                'name' => $icon->name,
                'tooltip' => $icon->tooltip ?: $icon->name,
                'icon_value' => $icon->icon_value,
                'color' => $icon->color ?: '#f4ecd0',
            ])->values()->all(),
        ];
    }

    private function formatPresenceCharacter(Character $character): array
    {
        return [
            'id' => $character->id,
            'name' => $character->name,
            'faction_color' => $character->faction?->color ?: '#f4ecd0',
            'account_icons' => $character->user->permissionIcons()->map(fn ($icon) => [
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
