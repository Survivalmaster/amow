<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function show(Request $request, Location $location): View
    {
        $character = $request->user()->character()->with(['rank', 'licences'])->firstOrFail();
        $location->load([
            'city.faction',
            'requiredRank',
            'requiredLicence',
            'messages' => fn ($query) => $query->with('character.rank')->latest()->limit(50),
        ]);

        abort_unless($character->canAccessLocation($location), 403);

        return view('locations.show', [
            'character' => $character,
            'location' => $location,
            'messages' => $location->messages->sortBy('created_at'),
        ]);
    }
}
