<?php

namespace App\Http\Controllers;

use App\Models\Faction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FactionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->character) {
            return redirect()->route('lobby');
        }

        return view('factions.index', [
            'factions' => Faction::query()->orderBy('name')->get(),
            'selectedFactionId' => (int) $request->session()->get('selected_faction_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'faction_id' => ['required', 'exists:factions,id'],
        ]);

        $request->session()->put('selected_faction_id', $validated['faction_id']);

        return redirect()->route('characters.create');
    }
}
