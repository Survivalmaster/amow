<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesDiscordCharacter;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Licence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordStoreController extends Controller
{
    use ResolvesDiscordCharacter;

    public function __invoke(Request $request, string $discordUserId): JsonResponse
    {
        $this->authorizeDiscordRequest($request);

        $character = $this->linkedCharacter($discordUserId, ['faction', 'rank', 'licences', 'inventory']);

        if (! $character) {
            return response()->json([
                'linked' => false,
                'message' => 'No AMOW character is linked to this Discord user.',
            ], 404);
        }

        $licences = Licence::query()->orderBy('cost')->get();
        $items = Item::query()->with('requiredLicence')->orderBy('type')->orderBy('price')->get();

        return response()->json([
            'linked' => true,
            'character' => [
                'name' => $character->name,
                'faction' => $character->faction?->name,
                'faction_color' => $character->faction?->color,
                'credits' => $character->plastic_credits,
                'inventory_slots_used' => $character->inventorySlotsUsed(),
                'inventory_slot_capacity' => $character->inventorySlotCapacity(),
            ],
            'licences' => $licences->map(fn (Licence $licence) => [
                'id' => $licence->id,
                'name' => $licence->name,
                'description' => $licence->description,
                'price' => $licence->cost,
                'owned' => $character->licences->contains('id', $licence->id),
                'required_level' => $licence->required_level,
            ])->values(),
            'items' => $items->map(fn (Item $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'type' => $item->type,
                'price' => $item->price,
                'stock' => $item->stock,
                'can_purchase' => $character->canPurchaseItem($item) && $character->canStoreAdditionalItem($item),
                'required_level' => $item->required_level,
                'required_role_type' => $item->required_role_type,
                'required_licence' => $item->requiredLicence?->name,
            ])->values(),
        ]);
    }
}
