<?php

namespace App\Http\Controllers;

use App\Models\CharacterLandBuilding;
use App\Models\Item;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with([
            'faction',
            'rank',
            'currentJob',
            'inventory',
            'licences',
            'landBuildings.item',
        ])->firstOrFail();

        abort_unless($character->hasLand(), 404);

        return view('home.index', [
            'character' => $character,
            'buildingItems' => $character->buildingItems(),
            'gridRows' => $this->buildGridRows($character),
        ]);
    }

    public function sleep(Request $request): RedirectResponse
    {
        $character = $request->user()->character()->with(['inventory', 'licences', 'landBuildings.item'])->firstOrFail();

        abort_unless($character->hasLand(), 404);

        if (! $character->hasCompletedLandBuilding()) {
            return back()->withErrors(['home' => 'You need at least one completed building on your land before you can rest there.']);
        }

        if ((int) $character->stamina_points >= 100) {
            return back()->with('status', 'Your character is already fully rested.');
        }

        $restored = 100 - (int) $character->stamina_points;

        $character->forceFill([
            'stamina_points' => 100,
        ])->save();

        CharacterActivity::recordTransaction(
            $character,
            'sleep_recovery',
            0,
            "Slept at home and restored {$restored} stamina."
        );

        return back()->with('status', "Sleep complete. Restored {$restored} stamina.");
    }

    public function placeBuilding(Request $request): RedirectResponse
    {
        $character = $request->user()->character()->with(['inventory', 'licences', 'landBuildings.item'])->firstOrFail();

        abort_unless($character->hasLand(), 404);

        $validated = $request->validate([
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'grid_x' => ['required', 'integer', 'min:1', 'max:10'],
            'grid_y' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        /** @var Item $item */
        $item = Item::query()->findOrFail($validated['item_id']);
        $ownedItem = $character->buildingItems()->firstWhere('id', $item->id);

        if (! $item->is_building) {
            return back()->withErrors(['building' => 'Only building items can be placed on land.']);
        }

        if (! $ownedItem || (int) $ownedItem->pivot->quantity < 1) {
            return back()->withErrors(['building' => 'You do not own that building item.']);
        }

        if (! $this->canPlaceBuilding($character, $item, (int) $validated['grid_x'], (int) $validated['grid_y'])) {
            return back()->withErrors(['building' => 'That building does not fit there or overlaps an existing placement.']);
        }

        DB::transaction(function () use ($character, $item, $ownedItem, $validated) {
            $quantity = (int) $ownedItem->pivot->quantity;

            if ($quantity <= 1) {
                $character->inventory()->detach($item->id);
            } else {
                $character->inventory()->updateExistingPivot($item->id, ['quantity' => $quantity - 1]);
            }

            CharacterLandBuilding::query()->create([
                'character_id' => $character->id,
                'item_id' => $item->id,
                'grid_x' => (int) $validated['grid_x'],
                'grid_y' => (int) $validated['grid_y'],
                'build_started_at' => now(),
                'build_complete_at' => now()->addMinutes(max(0, (int) $item->build_time_minutes)),
            ]);

            CharacterActivity::recordTransaction(
                $character,
                'building_placement',
                0,
                "Placed {$item->name} on land at {$validated['grid_x']}, {$validated['grid_y']}."
            );
        });

        return back()->with('status', "{$item->name} is now under construction.");
    }

    public function moveBuilding(Request $request, CharacterLandBuilding $characterLandBuilding): RedirectResponse
    {
        $character = $request->user()->character()->with(['inventory', 'licences', 'landBuildings.item'])->firstOrFail();

        abort_unless($character->hasLand(), 404);
        abort_unless($characterLandBuilding->character_id === $character->id, 404);

        $validated = $request->validate([
            'grid_x' => ['required', 'integer', 'min:1', 'max:10'],
            'grid_y' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $item = $characterLandBuilding->item;

        if (! $this->canPlaceBuilding(
            $character,
            $item,
            (int) $validated['grid_x'],
            (int) $validated['grid_y'],
            $characterLandBuilding
        )) {
            return back()->withErrors(['building' => 'That building cannot be moved there because the space is occupied or out of bounds.']);
        }

        $characterLandBuilding->forceFill([
            'grid_x' => (int) $validated['grid_x'],
            'grid_y' => (int) $validated['grid_y'],
            'build_started_at' => now(),
            'build_complete_at' => now()->addMinutes(max(0, (int) $item->build_time_minutes)),
        ])->save();

        CharacterActivity::recordTransaction(
            $character,
            'building_move',
            0,
            "Moved {$item->name} to {$validated['grid_x']}, {$validated['grid_y']} and restarted construction."
        );

        return back()->with('status', "{$item->name} is being rebuilt in its new position.");
    }

    public function destroyBuilding(Request $request, CharacterLandBuilding $characterLandBuilding): RedirectResponse
    {
        $character = $request->user()->character()->with(['inventory', 'licences', 'landBuildings.item'])->firstOrFail();

        abort_unless($character->hasLand(), 404);
        abort_unless($characterLandBuilding->character_id === $character->id, 404);

        $item = $characterLandBuilding->item;

        DB::transaction(function () use ($character, $characterLandBuilding, $item) {
            $ownedItem = $character->inventory->firstWhere('id', $item->id);
            $currentQuantity = (int) optional($ownedItem?->pivot)->quantity;

            $character->inventory()->syncWithoutDetaching([
                $item->id => ['quantity' => $currentQuantity + 1],
            ]);

            $characterLandBuilding->delete();

            CharacterActivity::recordTransaction(
                $character,
                'building_remove',
                0,
                "Removed {$item->name} from land and returned it to inventory."
            );
        });

        return back()->with('status', "{$item->name} removed from land.");
    }

    protected function buildGridRows($character): array
    {
        $grid = [];

        for ($y = 1; $y <= 10; $y++) {
            $row = [];

            for ($x = 1; $x <= 10; $x++) {
                $row[] = [
                    'x' => $x,
                    'y' => $y,
                    'building' => null,
                    'label' => null,
                    'status' => 'empty',
                    'is_anchor' => false,
                ];
            }

            $grid[] = $row;
        }

        foreach ($character->landBuildings as $building) {
            $width = max(1, (int) $building->item->footprint_width);
            $height = max(1, (int) $building->item->footprint_height);
            $status = $building->isComplete() ? 'complete' : 'building';

            for ($offsetY = 0; $offsetY < $height; $offsetY++) {
                for ($offsetX = 0; $offsetX < $width; $offsetX++) {
                    $x = ((int) $building->grid_x - 1) + $offsetX;
                    $y = ((int) $building->grid_y - 1) + $offsetY;

                    if (! isset($grid[$y][$x])) {
                        continue;
                    }

                    $grid[$y][$x] = [
                        'x' => $x + 1,
                        'y' => $y + 1,
                        'building' => $building,
                        'label' => $offsetX === 0 && $offsetY === 0 ? $building->item->name : 'Occupied',
                        'status' => $status,
                        'is_anchor' => $offsetX === 0 && $offsetY === 0,
                    ];
                }
            }
        }

        return $grid;
    }

    protected function canPlaceBuilding($character, Item $item, int $gridX, int $gridY, ?CharacterLandBuilding $ignoreBuilding = null): bool
    {
        $width = max(1, (int) $item->footprint_width);
        $height = max(1, (int) $item->footprint_height);

        if (($gridX + $width - 1) > 10 || ($gridY + $height - 1) > 10) {
            return false;
        }

        foreach ($character->landBuildings as $placedBuilding) {
            if ($ignoreBuilding && $placedBuilding->id === $ignoreBuilding->id) {
                continue;
            }

            $placedLeft = (int) $placedBuilding->grid_x;
            $placedTop = (int) $placedBuilding->grid_y;
            $placedRight = $placedLeft + max(1, (int) $placedBuilding->item->footprint_width) - 1;
            $placedBottom = $placedTop + max(1, (int) $placedBuilding->item->footprint_height) - 1;

            $newRight = $gridX + $width - 1;
            $newBottom = $gridY + $height - 1;

            $overlaps = ! (
                $newRight < $placedLeft
                || $gridX > $placedRight
                || $newBottom < $placedTop
                || $gridY > $placedBottom
            );

            if ($overlaps) {
                return false;
            }
        }

        return true;
    }
}
