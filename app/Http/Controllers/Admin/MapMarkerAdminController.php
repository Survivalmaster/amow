<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\MapMarker;
use App\Models\MapPolygon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
            'mapIconImages' => $this->mapIconImages(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedMarkerData($request);

        MapMarker::query()->create($validated);

        return back()->with('status', 'Map marker created.');
    }

    public function update(Request $request, MapMarker $mapMarker): RedirectResponse
    {
        $validated = $this->validatedMarkerData($request);

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

            if (! is_numeric($point['x']) || ! is_numeric($point['y'])) {
                throw ValidationException::withMessages([
                    'coordinates_json' => 'Each polygon point must use numeric x and y values.',
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
                'x' => round(max(0, min(100, (float) $point['x'])), 4),
                'y' => round(max(0, min(100, (float) $point['y'])), 4),
            ], $coordinates),
            'description' => $validated['description'],
        ];
    }

    private function validatedMarkerData(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'icon_type' => ['required', 'in:fontawesome,image'],
            'icon_class' => ['nullable', 'string', 'max:255'],
            'map_x' => ['required', 'numeric', 'between:0,100'],
            'map_y' => ['required', 'numeric', 'between:0,100'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['icon_type'] === 'fontawesome' && blank($validated['icon_class'])) {
            throw ValidationException::withMessages([
                'icon_class' => 'A Font Awesome class is required when using an icon font marker.',
            ]);
        }

        if ($validated['icon_type'] === 'image') {
            $allowedImages = $this->mapIconImages()->pluck('file')->all();

            if (blank($validated['icon_class']) || ! in_array($validated['icon_class'], $allowedImages, true)) {
                throw ValidationException::withMessages([
                    'icon_class' => 'Select a valid PNG from the mapicons folder.',
                ]);
            }
        }

        return [
            ...$validated,
            'map_x' => round((float) $validated['map_x'], 4),
            'map_y' => round((float) $validated['map_y'], 4),
        ];
    }

    private function mapIconImages()
    {
        $directory = public_path('images/mapicons');

        if (! File::isDirectory($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'png')
            ->map(fn ($file) => [
                'file' => $file->getFilename(),
                'label' => $file->getFilename(),
                'url' => asset('images/mapicons/'.$file->getFilename()),
            ])
            ->sortBy('file')
            ->values();
    }
}
