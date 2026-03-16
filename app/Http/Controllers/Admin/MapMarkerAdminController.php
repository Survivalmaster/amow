<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\MapMarker;
use App\Models\MapPolygon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MapMarkerAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.map-markers', [
            'markers' => MapMarker::query()->with('faction')->orderBy('name')->get(),
            'polygons' => MapPolygon::query()->with('faction')->orderBy('name')->get(),
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

    public function storePolygon(Request $request): RedirectResponse
    {
        MapPolygon::query()->create($this->validatedPolygonData($request));

        return back()->with('status', 'Map polygon created.');
    }

    public function updatePolygon(Request $request, MapPolygon $mapPolygon): RedirectResponse
    {
        $mapPolygon->update($this->validatedPolygonData($request));

        return back()->with('status', 'Map polygon updated.');
    }

    public function destroyPolygon(MapPolygon $mapPolygon): RedirectResponse
    {
        $mapPolygon->delete();

        return back()->with('status', 'Map polygon deleted.');
    }

    private function validatedPolygonData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'stroke_color' => ['nullable', 'string', 'max:20'],
            'fill_color' => ['nullable', 'string', 'max:20'],
            'fill_opacity' => ['required', 'numeric', 'between:0,1'],
            'stroke_weight' => ['required', 'integer', 'between:1,10'],
            'coordinates_json' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $coordinates = json_decode($validated['coordinates_json'], true);

        if (! is_array($coordinates) || count($coordinates) < 3) {
            throw ValidationException::withMessages([
                'coordinates_json' => 'A polygon needs at least three coordinate points.',
            ]);
        }

        foreach ($coordinates as $point) {
            if (! is_array($point) || ! array_key_exists('x', $point) || ! array_key_exists('y', $point)) {
                throw ValidationException::withMessages([
                    'coordinates_json' => 'Each polygon point must include x and y values.',
                ]);
            }
        }

        return [
            'name' => $validated['name'],
            'faction_id' => $validated['faction_id'],
            'stroke_color' => $validated['stroke_color'] ?: '#c2a84f',
            'fill_color' => $validated['fill_color'] ?: '#7ead59',
            'fill_opacity' => $validated['fill_opacity'],
            'stroke_weight' => $validated['stroke_weight'],
            'coordinates' => array_map(fn ($point) => [
                'x' => max(0, min(100, (int) $point['x'])),
                'y' => max(0, min(100, (int) $point['y'])),
            ], $coordinates),
            'description' => $validated['description'],
        ];
    }
}
