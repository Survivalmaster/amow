<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Faction;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.cities', [
            'cities' => City::query()->with('faction')->orderBy('name')->get(),
            'factions' => Faction::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $request->validate([
            'faction_id' => ['required', 'exists:factions,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:cities,slug'],
            'description' => ['required', 'string'],
            'map_x' => ['required', 'integer', 'between:0,100'],
            'map_y' => ['required', 'integer', 'between:0,100'],
        ]);

        $city = City::query()->create($validated);
        $adminActionLogger->created($request->user(), 'City', $city);

        return back()->with('status', 'City created.');
    }

    public function update(Request $request, City $city, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($city);
        $validated = $request->validate([
            'faction_id' => ['required', 'exists:factions,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:cities,slug,'.$city->id],
            'description' => ['required', 'string'],
            'map_x' => ['required', 'integer', 'between:0,100'],
            'map_y' => ['required', 'integer', 'between:0,100'],
        ]);

        $city->update($validated);
        $adminActionLogger->updated($request->user(), 'City', $before, $city);

        return back()->with('status', 'City updated.');
    }

    public function destroy(Request $request, City $city, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($city);
        try {
            $city->delete();
        } catch (QueryException) {
            return back()->withErrors('City could not be deleted because related records still exist.');
        }

        $adminActionLogger->deleted($request->user(), 'City', $snapshot);

        return back()->with('status', 'City deleted.');
    }
}
