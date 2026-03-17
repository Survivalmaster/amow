<?php

namespace App\Http\Controllers;

use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with([
            'faction',
            'rank',
            'currentJob',
            'inventory',
            'licences',
        ])->firstOrFail();

        abort_unless($character->hasHomeItem(), 404);

        return view('home.index', [
            'character' => $character,
            'homeItems' => $character->homeItems(),
        ]);
    }

    public function sleep(Request $request): RedirectResponse
    {
        $character = $request->user()->character()->with('inventory')->firstOrFail();

        abort_unless($character->hasHomeItem(), 404);

        if ((int) $character->stamina_points >= 100) {
            return back()->with('status', 'Your character is already fully rested.');
        }

        $restored = 100 - (int) $character->stamina_points;

        $character->forceFill([
            'stamina_points' => 100,
        ])->save();

        CharacterActivity::recordTransaction(
            $character,
            'sleep_recovery',
            0,
            "Slept at home and restored {$restored} stamina."
        );

        return back()->with('status', "Sleep complete. Restored {$restored} stamina.");
    }
}
