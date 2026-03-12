<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Licence;
use App\Models\Location;
use App\Models\Rank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.locations', [
            'locations' => Location::query()->with(['city', 'requiredRank', 'requiredLicence'])->orderBy('name')->get(),
            'cities' => City::query()->orderBy('name')->get(),
            'ranks' => Rank::query()->orderBy('order_index')->get(),
            'licences' => Licence::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'required_rank_id' => ['nullable', 'exists:ranks,id'],
            'required_licence_id' => ['nullable', 'exists:licences,id'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $validated['is_public'] = $request->boolean('is_public');

        Location::query()->create($validated);

        return back()->with('status', 'Location created.');
    }
}
