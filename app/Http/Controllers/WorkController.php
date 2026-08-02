<?php

namespace App\Http\Controllers;

use App\Actions\Characters\WorkCharacter;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class WorkController extends Controller
{
    public function store(Request $request, Location $location, WorkCharacter $workCharacter): RedirectResponse
    {
        abort_unless($location->slug === 'go-to-work', 403);

        try {
            $result = $workCharacter->execute($request->user()->character()->with('currentJob')->firstOrFail());
        } catch (RuntimeException $exception) {
            return back()->withErrors([
                'work' => $exception->getMessage(),
            ]);
        }

        $levelMessage = $result['levels_gained'] > 0 ? " Level up! You reached level {$result['character']->level}." : '';

        return back()->with('status', "Shift complete. You earned {$result['earnings']} Plastic Credits and {$result['experience_earned']} XP.".$levelMessage);
    }
}
