<?php

namespace App\Http\Controllers;

use App\Models\GameJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with('currentJob')->firstOrFail();

        return view('jobs.index', [
            'character' => $character,
            'jobs' => GameJob::query()->orderBy('required_level')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, GameJob $gameJob): RedirectResponse
    {
        $character = $request->user()->character()->with('currentJob')->firstOrFail();

        if (! $gameJob->is_active) {
            return back()->withErrors(['job' => 'That job is currently unavailable.']);
        }

        if ($character->current_job_id === $gameJob->id) {
            return back()->withErrors(['job' => 'This character already has that job.']);
        }

        if ($character->level < $gameJob->required_level) {
            return back()->withErrors(['job' => 'Your level is too low for that job.']);
        }

        if (! $character->canChangeJob()) {
            $availableAt = $character->job_changed_at?->copy()->addDay()->format('d M H:i');

            return back()->withErrors(['job' => 'Job switch cooldown active until '.$availableAt.'.']);
        }

        DB::transaction(function () use ($character, $gameJob) {
            $character->forceFill([
                'current_job_id' => $gameJob->id,
                'job_changed_at' => now(),
            ])->save();
        });

        return back()->with('status', "Job changed to {$gameJob->name}.");
    }
}
