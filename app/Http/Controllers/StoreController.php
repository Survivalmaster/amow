<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Licence;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with(['rank', 'licences', 'inventory'])->firstOrFail();

        return view('store.index', [
            'character' => $character,
            'items' => Item::query()->with(['requiredRank', 'requiredLicence'])->orderBy('type')->orderBy('price')->get(),
            'licences' => Licence::query()->with('requiredRank')->orderBy('cost')->get(),
        ]);
    }

    public function purchase(Request $request): RedirectResponse
    {
        $character = $request->user()->character()->with(['rank', 'licences', 'inventory'])->firstOrFail();

        $validated = $request->validate([
            'purchase_type' => ['required', 'in:item,licence'],
            'id' => ['required', 'integer'],
        ]);

        if ($validated['purchase_type'] === 'licence') {
            $licence = Licence::query()->with('requiredRank')->findOrFail($validated['id']);

            if ($character->licences->contains('id', $licence->id)) {
                return back()->withErrors(['purchase' => 'Licence already owned.']);
            }

            if ($licence->required_rank_id && $character->rank->order_index < $licence->requiredRank->order_index) {
                return back()->withErrors(['purchase' => 'Rank requirement not met.']);
            }

            if ($character->plastic_credits < $licence->cost) {
                return back()->withErrors(['purchase' => 'Not enough Plastic Credits.']);
            }

            DB::transaction(function () use ($character, $licence) {
                $character->decrement('plastic_credits', $licence->cost);
                $character->licences()->attach($licence->id);

                if ($licence->slug === 'business-owner') {
                    $character->forceFill(['is_business_owner' => true])->save();
                }

                CharacterActivity::recordTransaction(
                    $character,
                    'licence_purchase',
                    -$licence->cost,
                    "Purchased {$licence->name} licence."
                );
            });

            return back()->with('status', "{$licence->name} acquired.");
        }

        $item = Item::query()->with(['requiredRank', 'requiredLicence'])->findOrFail($validated['id']);

        if (! $character->canPurchaseItem($item)) {
            return back()->withErrors(['purchase' => 'Your rank, role, or licences do not allow this purchase.']);
        }

        if ($character->plastic_credits < $item->price) {
            return back()->withErrors(['purchase' => 'Not enough Plastic Credits.']);
        }

        DB::transaction(function () use ($character, $item) {
            $currentQuantity = (int) optional($character->inventory->firstWhere('id', $item->id))->pivot?->quantity;

            $character->decrement('plastic_credits', $item->price);
            $character->inventory()->syncWithoutDetaching([
                $item->id => ['quantity' => $currentQuantity + 1],
            ]);

            if ($item->stock !== null) {
                $item->decrement('stock');
            }

            CharacterActivity::recordTransaction(
                $character,
                'item_purchase',
                -$item->price,
                "Purchased {$item->name}."
            );
        });

        return back()->with('status', "{$item->name} added to your inventory.");
    }
}
