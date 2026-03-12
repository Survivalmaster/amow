<?php

namespace App\Http\Controllers;

use App\Models\Faction;
use App\Models\Rank;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()->character) {
            return redirect()->route('lobby');
        }

        $factionId = $request->session()->get('selected_faction_id');

        if (! $factionId || ! Faction::query()->whereKey($factionId)->exists()) {
            return redirect()->route('factions.index');
        }

        return view('characters.create', [
            'faction' => Faction::query()->findOrFail($factionId),
            'occupations' => ['Laborer', 'Merchant', 'Mechanic'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->character) {
            return redirect()->route('lobby');
        }

        $faction = Faction::query()->findOrFail($request->session()->get('selected_faction_id'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'between:16,80'],
            'biography' => ['required', 'string', 'max:2000'],
            'starting_occupation' => ['required', 'in:Laborer,Merchant,Mechanic'],
            'role_type' => ['required', 'in:civilian,military'],
        ]);

        $rank = Rank::query()
            ->where('name', $validated['role_type'] === 'military' ? 'Recruit' : 'Civilian')
            ->firstOrFail();

        $request->user()->character()->create([
            ...$validated,
            'faction_id' => $faction->id,
            'rank_id' => $rank->id,
            'plastic_credits' => 100,
            'health_points' => 100,
        ]);

        $request->session()->forget('selected_faction_id');

        return redirect()->route('lobby')->with('status', 'Character created and deployed to Plastica.');
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Request $request): View
    {
        $character = $request->user()->character()->with([
            'faction',
            'rank',
            'licences.requiredRank',
            'inventory',
            'transactions' => fn ($query) => $query->latest()->limit(12),
            'holdings.company',
        ])->firstOrFail();

        $this->authorize('view', $character);

        return view('characters.profile', [
            'character' => $character,
        ]);
    }
}
