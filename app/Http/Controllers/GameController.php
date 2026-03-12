<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Company;
use App\Models\Faction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'factions' => Faction::query()->take(4)->get(),
        ]);
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $character = $request->user()->character()->with(['faction', 'rank', 'licences'])->first();

        if (! $character) {
            return view('game.dashboard', [
                'factions' => Faction::query()->orderBy('name')->get(),
            ]);
        }

        return redirect()->route('lobby');
    }

    public function lobby(Request $request): View
    {
        /** @var Character $character */
        $character = $request->user()->character()->with([
            'faction.cities.locations',
            'rank',
            'licences',
            'holdings.company',
        ])->firstOrFail();

        return view('game.lobby', [
            'character' => $character,
            'cities' => $character->faction->cities,
            'companies' => Company::query()->orderByDesc('current_price')->take(3)->get(),
        ]);
    }
}
