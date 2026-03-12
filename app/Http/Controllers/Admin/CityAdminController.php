<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Faction;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'faction_id' => ['required', 'exists:factions,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:cities,slug'],
            'description' => ['required', 'string'],
        ]);

        City::query()->create($validated);

        return back()->with('status', 'City created.');
    }
}
