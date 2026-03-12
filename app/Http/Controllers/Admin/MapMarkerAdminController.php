<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\MapMarker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapMarkerAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.map-markers', [
            'markers' => MapMarker::query()->with('faction')->orderBy('name')->get(),
            'factions' => Faction::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'icon_class' => ['required', 'string', 'max:255'],
            'map_x' => ['required', 'integer', 'between:0,100'],
            'map_y' => ['required', 'integer', 'between:0,100'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        MapMarker::query()->create($validated);

        return back()->with('status', 'Map marker created.');
    }

    public function update(Request $request, MapMarker $mapMarker): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'icon_class' => ['required', 'string', 'max:255'],
            'map_x' => ['required', 'integer', 'between:0,100'],
            'map_y' => ['required', 'integer', 'between:0,100'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $mapMarker->update($validated);

        return back()->with('status', 'Map marker updated.');
    }

    public function destroy(MapMarker $mapMarker): RedirectResponse
    {
        $mapMarker->delete();

        return back()->with('status', 'Map marker deleted.');
    }
}
