<?php

namespace App\Actions\Store;

use App\Models\Character;
use App\Models\Item;
use App\Models\Licence;
use App\Support\CharacterActivity;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseStoreEntry
{
    public function execute(Character $character, string $purchaseType, int $id): array
    {
        $character->loadMissing(['rank', 'licences', 'inventory']);

        if ($purchaseType === 'licence') {
            return $this->purchaseLicence($character, $id);
        }

        if ($purchaseType === 'item') {
            return $this->purchaseItem($character, $id);
        }

        throw new RuntimeException('Unknown purchase type.');
    }

    private function purchaseLicence(Character $character, int $id): array
    {
        $licence = Licence::query()->with('requiredRank')->findOrFail($id);

        if ($character->licences->contains('id', $licence->id)) {
            throw new RuntimeException('Licence already owned.');
        }

        if ($licence->required_rank_id && $character->rank->order_index < $licence->requiredRank->order_index) {
            throw new RuntimeException('Rank requirement not met.');
        }

        if ($character->plastic_credits < $licence->cost) {
            throw new RuntimeException('Not enough Plastic Credits.');
        }

        DB::transaction(function () use ($character, $licence) {
            $character->decrement('plastic_credits', $licence->cost);
            $character->licences()->attach($licence->id);

            if ($licence->slug === 'business-owner' || $licence->grants_business_creation) {
                $character->forceFill(['is_business_owner' => true])->save();
            }

            CharacterActivity::recordTransaction(
                $character,
                'licence_purchase',
                -$licence->cost,
                "Purchased {$licence->name} licence."
            );
        });

        return [
            'type' => 'licence',
            'entry' => $licence,
            'message' => "{$licence->name} acquired.",
            'character' => $character->fresh(['rank', 'licences', 'inventory']),
        ];
    }

    private function purchaseItem(Character $character, int $id): array
    {
        $item = Item::query()->with(['requiredRank', 'requiredLicence'])->findOrFail($id);

        if (! $character->canPurchaseItem($item)) {
            throw new RuntimeException('Your rank, role, or licences do not allow this purchase.');
        }

        if (! $character->canStoreAdditionalItem($item)) {
            throw new RuntimeException('Inventory capacity reached. Buy or equip a backpack-style item to unlock more slots.');
        }

        if ($character->plastic_credits < $item->price) {
            throw new RuntimeException('Not enough Plastic Credits.');
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

        return [
            'type' => 'item',
            'entry' => $item,
            'message' => "{$item->name} added to your inventory.",
            'character' => $character->fresh(['rank', 'licences', 'inventory']),
        ];
    }
}
