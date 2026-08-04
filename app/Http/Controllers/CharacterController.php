<?php

namespace App\Http\Controllers;

use App\Models\Faction;
use App\Models\GameJob;
use App\Models\Rank;
use App\Support\CharacterActivity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            'starterJob' => GameJob::query()->where('is_starter', true)->where('is_active', true)->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->character) {
            return redirect()->route('lobby');
        }

        $faction = Faction::query()->findOrFail($request->session()->get('selected_faction_id'));
        $starterJob = GameJob::query()->where('is_starter', true)->where('is_active', true)->first();

        if (! $starterJob) {
            throw new ModelNotFoundException('No starter job is configured.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'between:16,80'],
            'biography' => ['required', 'string', 'max:2000'],
        ]);

        $rank = Rank::query()
            ->where('name', 'Civilian')
            ->firstOrFail();

        $character = $request->user()->character()->create([
            ...$validated,
            'starting_occupation' => $starterJob->name,
            'role_type' => 'civilian',
            'faction_id' => $faction->id,
            'rank_id' => $rank->id,
            'current_job_id' => $starterJob->id,
            'plastic_credits' => 100,
            'level' => 0,
            'experience_points' => 0,
            'health_points' => 100,
            'stamina_points' => 100,
            'armor_points' => 0,
        ]);

        CharacterActivity::recordTransaction(
            $character,
            'character_created',
            100,
            "Character created in {$faction->name} with starter job {$starterJob->name}.",
            [
                'faction' => $faction->name,
                'rank' => $rank->name,
                'starter_job' => $starterJob->name,
                'starting_credits' => 100,
            ]
        );

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
            'currentJob',
            'licences',
            'inventory',
            'transactions' => fn ($query) => $query->latest()->limit(12),
            'holdings.company',
        ])->firstOrFail();

        $this->authorize('view', $character);

        return view('characters.profile', [
            'character' => $character,
            'inventorySlotsUsed' => $character->inventorySlotsUsed(),
            'inventorySlotCapacity' => $character->inventorySlotCapacity(),
            'buildingItemCount' => $character->buildingItems()->sum(fn ($item) => (int) $item->pivot->quantity),
        ]);
    }
}
