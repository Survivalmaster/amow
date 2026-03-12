<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkController extends Controller
{
    public function store(Request $request, Location $location): RedirectResponse
    {
        abort_unless($location->slug === 'go-to-work', 403);

        $character = $request->user()->character()->firstOrFail();

        if ($character->last_worked_at && $character->last_worked_at->gt(now()->subMinutes(5))) {
            return back()->withErrors([
                'work' => 'Work cooldown active. You can work again at '.$character->last_worked_at->copy()->addMinutes(5)->format('H:i'),
            ]);
        }

        $earnings = random_int(10, 30);

        DB::transaction(function () use ($character, $earnings) {
            $character->increment('plastic_credits', $earnings);
            $character->forceFill(['last_worked_at' => now()])->save();
            CharacterActivity::recordTransaction($character, 'work', $earnings, 'Completed a work shift.');
        });

        return back()->with('status', "Shift complete. You earned {$earnings} Plastic Credits.");
    }
}
