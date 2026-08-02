<?php

namespace App\Http\Controllers\Api;

use App\Actions\Store\PurchaseStoreEntry;
use App\Http\Controllers\Api\Concerns\ResolvesDiscordCharacter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DiscordStorePurchaseController extends Controller
{
    use ResolvesDiscordCharacter;

    public function __invoke(Request $request, PurchaseStoreEntry $purchaseStoreEntry): JsonResponse
    {
        $this->authorizeDiscordRequest($request);

        $payload = $request->validate([
            'discord_user_id' => ['required', 'string', 'max:32'],
            'purchase_type' => ['required', 'in:item,licence'],
            'id' => ['required', 'integer'],
        ]);

        $character = $this->linkedCharacter($payload['discord_user_id'], ['faction', 'rank', 'licences', 'inventory']);

        if (! $character) {
            return response()->json([
                'linked' => false,
                'message' => 'No AMOW character is linked to this Discord user.',
            ], 404);
        }

        try {
            $result = $purchaseStoreEntry->execute($character, $payload['purchase_type'], (int) $payload['id']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'linked' => true,
                'purchased' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $result['character']->loadMissing('faction');

        return response()->json([
            'linked' => true,
            'purchased' => true,
            'message' => $result['message'],
            'purchase' => [
                'type' => $result['type'],
                'name' => $result['entry']->name,
            ],
            'character' => [
                'name' => $result['character']->name,
                'faction' => $result['character']->faction?->name,
                'faction_color' => $result['character']->faction?->color,
                'credits' => $result['character']->plastic_credits,
                'inventory_slots_used' => $result['character']->inventorySlotsUsed(),
                'inventory_slot_capacity' => $result['character']->inventorySlotCapacity(),
            ],
        ]);
    }
}
