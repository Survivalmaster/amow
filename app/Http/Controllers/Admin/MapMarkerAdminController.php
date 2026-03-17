<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\MapMarker;
use App\Models\MapPolygon;
use App\Services\Discord\AdminActionLogger;
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

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $this->validatedMarkerData($request);

        $mapMarker = MapMarker::query()->create($validated);
        $adminActionLogger->created($request->user(), 'Map Marker', $mapMarker);

        return back()->with('status', 'Map marker created.');
    }

    public function update(Request $request, MapMarker $mapMarker, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($mapMarker);
        $validated = $this->validatedMarkerData($request);

        $mapMarker->update($validated);
        $adminActionLogger->updated($request->user(), 'Map Marker', $before, $mapMarker);

        return back()->with('status', 'Map marker updated.');
    }

    public function destroy(Request $request, MapMarker $mapMarker, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($mapMarker);
        $mapMarker->delete();
        $adminActionLogger->deleted($request->user(), 'Map Marker', $snapshot);

        return back()->with('status', 'Map marker deleted.');
    }

    public function storePolygon(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $mapPolygon = MapPolygon::query()->create($this->validatedPolygonData($request));
        $adminActionLogger->created($request->user(), 'Map Polygon', $mapPolygon);

        return back()->with('status', 'Map polygon created.');
    }

    public function updatePolygon(Request $request, MapPolygon $mapPolygon, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($mapPolygon);
        $mapPolygon->update($this->validatedPolygonData($request));
        $adminActionLogger->updated($request->user(), 'Map Polygon', $before, $mapPolygon);

        return back()->with('status', 'Map polygon updated.');
    }

    public function destroyPolygon(Request $request, MapPolygon $mapPolygon, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($mapPolygon);
        $mapPolygon->delete();
        $adminActionLogger->deleted($request->user(), 'Map Polygon', $snapshot);

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
            'icon_class_fontawesome' => ['nullable', 'string', 'max:255'],
            'icon_image' => ['nullable', 'string', 'max:255'],
            'map_x' => ['required', 'numeric', 'between:0,100'],
            'map_y' => ['required', 'numeric', 'between:0,100'],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $iconClass = $validated['icon_type'] === 'fontawesome'
            ? ($validated['icon_class_fontawesome'] ?? null)
            : ($validated['icon_image'] ?? null);

        if ($validated['icon_type'] === 'fontawesome' && blank($iconClass)) {
            throw ValidationException::withMessages([
                'icon_class_fontawesome' => 'A Font Awesome class is required when using an icon font marker.',
            ]);
        }

        if ($validated['icon_type'] === 'image') {
            $allowedImages = $this->mapIconImages()->pluck('file')->all();

            if (blank($iconClass) || ! in_array($iconClass, $allowedImages, true)) {
                throw ValidationException::withMessages([
                    'icon_image' => 'Select a valid PNG from the mapicons folder.',
                ]);
            }
        }

        return [
            'name' => $validated['name'],
            'faction_id' => $validated['faction_id'],
            'icon_type' => $validated['icon_type'],
            'icon_class' => $iconClass,
            'map_x' => round((float) $validated['map_x'], 4),
            'map_y' => round((float) $validated['map_y'], 4),
            'color' => $validated['color'],
            'description' => $validated['description'],
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
