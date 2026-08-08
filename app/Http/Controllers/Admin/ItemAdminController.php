<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Licence;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.items', [
            'items' => Item::query()->with(['requiredLicence', 'producingBuilding'])->orderBy('name')->get(),
            'buildingItems' => Item::query()->where('is_building', true)->orderBy('name')->get(),
            'itemTypes' => Item::TYPES,
            'licences' => Licence::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:items,slug'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in(array_keys(Item::TYPES))],
            'icon_class' => ['nullable', 'string', 'max:255'],
            'is_home' => ['nullable', 'boolean'],
            'is_building' => ['nullable', 'boolean'],
            'is_buyable' => ['nullable', 'boolean'],
            'footprint_width' => ['nullable', 'integer', 'min:1', 'max:10'],
            'footprint_height' => ['nullable', 'integer', 'min:1', 'max:10'],
            'build_time_minutes' => ['nullable', 'integer', 'min:0'],
            'produced_by_building_item_id' => ['nullable', Rule::exists('items', 'id')->where('is_building', true)],
            'inventory_slot_bonus' => ['nullable', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:1'],
            'required_level' => ['nullable', 'integer', 'min:0'],
            'required_role_type' => ['nullable', 'in:civilian,military'],
            'required_licence_id' => ['nullable', 'exists:licences,id'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]) + [
            'is_home' => $request->boolean('is_home'),
            'is_building' => $request->boolean('is_building'),
            'is_buyable' => $request->has('is_buyable') ? $request->boolean('is_buyable') : true,
            'footprint_width' => (int) $request->input('footprint_width', 1),
            'footprint_height' => (int) $request->input('footprint_height', 1),
            'build_time_minutes' => (int) $request->input('build_time_minutes', 0),
            'inventory_slot_bonus' => (int) $request->input('inventory_slot_bonus', 0),
            'required_rank_id' => null,
        ];

        $item = Item::query()->create($validated);
        $adminActionLogger->created($request->user(), 'Item', $item);

        return back()->with('status', 'Item created.');
    }

    public function update(Request $request, Item $item, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($item);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:items,slug,'.$item->id],
            'description' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in(array_keys(Item::TYPES))],
            'icon_class' => ['nullable', 'string', 'max:255'],
            'is_home' => ['nullable', 'boolean'],
            'is_building' => ['nullable', 'boolean'],
            'is_buyable' => ['nullable', 'boolean'],
            'footprint_width' => ['nullable', 'integer', 'min:1', 'max:10'],
            'footprint_height' => ['nullable', 'integer', 'min:1', 'max:10'],
            'build_time_minutes' => ['nullable', 'integer', 'min:0'],
            'produced_by_building_item_id' => ['nullable', Rule::exists('items', 'id')->where('is_building', true)],
            'inventory_slot_bonus' => ['nullable', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:1'],
            'required_level' => ['nullable', 'integer', 'min:0'],
            'required_role_type' => ['nullable', 'in:civilian,military'],
            'required_licence_id' => ['nullable', 'exists:licences,id'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]) + [
            'is_home' => $request->boolean('is_home'),
            'is_building' => $request->boolean('is_building'),
            'is_buyable' => $request->has('is_buyable') ? $request->boolean('is_buyable') : (bool) $item->is_buyable,
            'footprint_width' => (int) $request->input('footprint_width', 1),
            'footprint_height' => (int) $request->input('footprint_height', 1),
            'build_time_minutes' => (int) $request->input('build_time_minutes', 0),
            'inventory_slot_bonus' => (int) $request->input('inventory_slot_bonus', 0),
            'required_rank_id' => null,
        ];

        if ((int) ($validated['produced_by_building_item_id'] ?? 0) === $item->id) {
            return back()->withErrors(['produced_by_building_item_id' => 'An item cannot be produced by itself.'])->withInput();
        }

        $item->update($validated);
        $adminActionLogger->updated($request->user(), 'Item', $before, $item);

        return back()->with('status', 'Item updated.');
    }

    public function destroy(Request $request, Item $item, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($item);
        try {
            $item->delete();
        } catch (QueryException) {
            return back()->withErrors('Item could not be deleted because related records still exist.');
        }

        $adminActionLogger->deleted($request->user(), 'Item', $snapshot);

        return back()->with('status', 'Item deleted.');
    }
}
