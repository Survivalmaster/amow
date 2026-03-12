<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Licence;
use App\Models\Rank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.items', [
            'items' => Item::query()->with(['requiredRank', 'requiredLicence'])->orderBy('name')->get(),
            'ranks' => Rank::query()->orderBy('order_index')->get(),
            'licences' => Licence::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:items,slug'],
            'description' => ['required', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1'],
            'required_rank_id' => ['nullable', 'exists:ranks,id'],
            'required_role_type' => ['nullable', 'in:civilian,military'],
            'required_licence_id' => ['nullable', 'exists:licences,id'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        Item::query()->create($validated);

        return back()->with('status', 'Item created.');
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:items,slug,'.$item->id],
            'description' => ['required', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:1'],
            'required_rank_id' => ['nullable', 'exists:ranks,id'],
            'required_role_type' => ['nullable', 'in:civilian,military'],
            'required_licence_id' => ['nullable', 'exists:licences,id'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $item->update($validated);

        return back()->with('status', 'Item updated.');
    }
}
