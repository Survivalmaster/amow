<?php

namespace App\Http\Controllers;

use App\Actions\Characters\ChangeCharacterJob;
use App\Models\GameJob;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with('currentJob')->firstOrFail();
        $workLocation = Location::query()->where('slug', 'go-to-work')->first();

        return view('jobs.index', [
            'character' => $character,
            'jobs' => GameJob::query()->orderBy('required_level')->orderBy('name')->get(),
            'workLocation' => $workLocation,
        ]);
    }

    public function store(Request $request, GameJob $gameJob, ChangeCharacterJob $changeCharacterJob): RedirectResponse
    {
        $character = $request->user()->character()->with('currentJob')->firstOrFail();

        try {
            $result = $changeCharacterJob->execute($character, $gameJob);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        return back()->with('status', $result['message']);
    }
}
