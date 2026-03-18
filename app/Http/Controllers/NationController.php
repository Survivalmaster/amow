<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Rank;
use App\Support\CharacterActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NationController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with([
            'faction.characters.user',
            'faction.characters.rank',
            'rank',
        ])->firstOrFail();

        return view('nation.index', [
            'character' => $character,
            'faction' => $character->faction->load([
                'characters.user',
                'characters.rank',
                'requisitions' => fn ($query) => $query->latest()->limit(5),
            ]),
            'militaryRanks' => Rank::query()->where('is_military', true)->orderBy('order_index')->get(),
        ]);
    }

    public function donate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $character = $request->user()->character()->with('faction')->firstOrFail();

        if ($character->plastic_credits < $validated['amount']) {
            return back()->withErrors(['amount' => 'You do not have enough Plastic Credits to donate that amount.']);
        }

        DB::transaction(function () use ($character, $validated) {
            $character->decrement('plastic_credits', $validated['amount']);
            $character->faction()->increment('nation_bank_credits', $validated['amount']);

            CharacterActivity::recordTransaction(
                $character,
                'nation_donation',
                -$validated['amount'],
                "Donated {$validated['amount']} Plastic Credits to {$character->faction->name}."
            );
        });

        return back()->with('status', 'Donation sent to the nation bank.');
    }

    public function updateRank(Request $request, Character $character): RedirectResponse
    {
        $validated = $request->validate([
            'rank_id' => ['required', 'exists:ranks,id'],
        ]);

        $leader = $request->user()->character()->with(['faction', 'rank'])->firstOrFail();
        abort_unless($request->user()->hasPermission('nation-leader') && $leader->canLeadNation(), 403);
        abort_unless($leader->faction_id === $character->faction_id, 403);
        abort_unless($character->role_type === 'military', 422);

        $rank = Rank::query()->where('is_military', true)->findOrFail($validated['rank_id']);
        $character->update(['rank_id' => $rank->id]);

        return back()->with('status', "Updated {$character->name}'s nation rank.");
    }
}
