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

        $slots = collect(range(0, max(0, $character->inventorySlotCapacity() - 1)))
            ->map(fn (int $index) => $character->inventory->values()->get($index));

        return view('inventory.index', [
            'character' => $character,
            'slots' => $slots,
        ]);
    }
}
