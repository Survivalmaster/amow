<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function show(Request $request, City $city): View
    {
        $character = $request->user()->character()->with('rank', 'licences')->firstOrFail();
        abort_unless($city->faction_id === $character->faction_id, 403);

        return view('cities.show', [
            'character' => $character,
            'city' => $city->load(['locations.requiredRank', 'locations.requiredLicence', 'faction']),
        ]);
    }
}
