<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Location $location): RedirectResponse
    {
        $character = $request->user()->character()->with(['rank', 'licences'])->firstOrFail();
        $location->load(['city', 'requiredRank', 'requiredLicence']);

        abort_unless($character->canAccessLocation($location), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $location->messages()->create([
            'character_id' => $character->id,
            'message' => $validated['message'],
        ]);

        return redirect()->route('locations.show', $location)->with('status', 'Message transmitted.');
    }
}
