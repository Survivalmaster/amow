<?php

namespace App\Http\Controllers;

use App\Actions\Store\PurchaseStoreEntry;
use App\Models\Item;
use App\Models\Licence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        return $this->marketplaceView($request, 'store');
    }

    public function licences(Request $request): View
    {
        return $this->marketplaceView($request, 'licences');
    }

    private function marketplaceView(Request $request, string $section): View
    {
        $character = $request->user()->character()->with(['rank', 'licences', 'inventory'])->firstOrFail();
        $items = Item::query()->where('is_buyable', true)->with('requiredLicence')->orderBy('type')->orderBy('price')->get();

        return view('store.index', [
            'character' => $character,
            'marketplaceSection' => $section,
            'gearItems' => $items->where('is_building', false)->values(),
            'buildingItems' => $items->where('is_building', true)->values(),
            'licences' => Licence::query()->orderBy('cost')->get(),
        ]);
    }

    public function purchase(Request $request, PurchaseStoreEntry $purchaseStoreEntry): RedirectResponse
    {
        $character = $request->user()->character()->with(['rank', 'licences', 'inventory'])->firstOrFail();

        $validated = $request->validate([
            'purchase_type' => ['required', 'in:item,licence'],
            'id' => ['required', 'integer'],
        ]);

        try {
            $result = $purchaseStoreEntry->execute($character, $validated['purchase_type'], (int) $validated['id']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['purchase' => $exception->getMessage()]);
        }

        return back()->with('status', $result['message']);
    }
}
