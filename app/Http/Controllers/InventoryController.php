<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with([
            'faction',
            'rank',
            'currentJob',
            'inventory',
        ])->firstOrFail();

        $inventoryStacks = $character->inventory->flatMap(function ($item) use ($character) {
            $quantity = max(1, (int) ($item->pivot->quantity ?? 1));
            $maxPerSlot = max(1, (int) ($item->max_stack_per_slot ?? 1));
            $stackCount = $character->inventorySlotsForItemQuantity($item, $quantity);

            return collect(range(1, $stackCount))->map(function (int $stackIndex) use ($item, $quantity, $maxPerSlot) {
                $slotItem = clone $item;
                $slotQuantity = min($maxPerSlot, max(0, $quantity - (($stackIndex - 1) * $maxPerSlot)));
                $slotItem->setRelation('pivot', clone $item->pivot);
                $slotItem->pivot->quantity = $slotQuantity;
                $slotItem->slot_stack_quantity = $slotQuantity;
                $slotItem->slot_stack_max = $maxPerSlot;

                return $slotItem;
            });
        })->values();

        $slots = collect(range(0, max(0, $character->inventorySlotCapacity() - 1)))
            ->map(fn (int $index) => $inventoryStacks->get($index));

        return view('inventory.index', [
            'character' => $character,
            'slots' => $slots,
        ]);
    }
}
