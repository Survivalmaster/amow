<?php

namespace App\Http\Controllers;

use App\Actions\Characters\ChangeCharacterJob;
use App\Actions\Characters\WorkTieredJob;
use App\Models\CharacterJobProgress;
use App\Models\GameJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class JobNewController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->load('permissions')->hasPermission('developer'), 403);

        $character = $request->user()->character()
            ->with(['currentJob.drops.item', 'jobProgress.job', 'inventory', 'licences', 'landBuildings.item'])
            ->firstOrFail();

        if (! $character->currentJob?->is_new) {
            $character->setRelation('currentJob', null);
        }

        $jobs = GameJob::query()
            ->where('is_new', true)
            ->with(['drops.item', 'progress' => fn ($query) => $query->where('character_id', $character->id)])
            ->orderBy('required_level')
            ->orderBy('name')
            ->get();

        $currentProgress = null;

        if ($character->current_job_id || $jobs->isNotEmpty()) {
            $progressJob = $character->currentJob ?? $jobs->first();

            $currentProgress = CharacterJobProgress::query()->firstOrCreate([
                'character_id' => $character->id,
                'game_job_id' => $progressJob->id,
            ], [
                'tier' => (int) $progressJob->max_tier > 0 ? 1 : 0,
                'tier_experience' => 0,
            ]);
        }

        return view('jobs.new', [
            'character' => $character,
            'jobs' => $jobs,
            'currentProgress' => $currentProgress,
        ]);
    }

    public function store(Request $request, GameJob $gameJob, ChangeCharacterJob $changeCharacterJob): RedirectResponse
    {
        abort_unless($request->user()?->load('permissions')->hasPermission('developer'), 403);
        abort_unless($gameJob->is_new, 404);

        $character = $request->user()->character()->with('currentJob')->firstOrFail();

        try {
            $result = $changeCharacterJob->execute($character, $gameJob);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['job' => $exception->getMessage()]);
        }

        CharacterJobProgress::query()->firstOrCreate([
            'character_id' => $character->id,
            'game_job_id' => $gameJob->id,
        ], [
            'tier' => (int) $gameJob->max_tier > 0 ? 1 : 0,
            'tier_experience' => 0,
        ]);

        return back()->with('status', $result['message']);
    }

    public function work(Request $request, WorkTieredJob $workTieredJob): RedirectResponse
    {
        abort_unless($request->user()?->load('permissions')->hasPermission('developer'), 403);

        $character = $request->user()->character()->with(['currentJob', 'inventory'])->firstOrFail();

        if (! $character->currentJob?->is_new) {
            return back()->withErrors(['work' => 'Take a Jobs New assignment before working a tiered shift.']);
        }

        try {
            $result = $workTieredJob->execute($character);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['work' => $exception->getMessage()]);
        }

        $dropText = collect($result['drops'])
            ->map(fn (array $drop) => $drop['quantity'].'x '.$drop['name'])
            ->implode(', ');
        $message = "Shift complete. Earned {$result['earnings']} Plastic Credits and {$result['experience_earned']} job XP.";

        if ($dropText !== '') {
            $message .= ' Drops: '.$dropText.'.';
        }

        if ($result['tiers_gained'] > 0) {
            $message .= ' Tier increased.';
        }

        if (($result['levels_gained'] ?? 0) > 0) {
            $message .= " Level up! You reached level {$result['character']->level}.";
        }

        return back()->with('status', $message);
    }
}
