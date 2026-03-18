<?php

namespace App\Http\Controllers;

use App\Models\NationRequisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NationRequisitionController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character()->with('faction')->firstOrFail();
        abort_unless($request->user()->hasPermission('nation-leader') && $character->canLeadNation(), 403);

        return view('nation.requisitions', [
            'character' => $character,
            'requisitions' => NationRequisition::query()
                ->with('reviewer')
                ->where('faction_id', $character->faction_id)
                ->latest()
                ->get(),
            'hasOutstandingRequest' => NationRequisition::query()
                ->where('faction_id', $character->faction_id)
                ->whereIn('status', NationRequisition::openStatuses())
                ->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $character = $request->user()->character()->with('faction')->firstOrFail();
        abort_unless($request->user()->hasPermission('nation-leader') && $character->canLeadNation(), 403);

        $hasOutstandingRequest = NationRequisition::query()
            ->where('faction_id', $character->faction_id)
            ->whereIn('status', NationRequisition::openStatuses())
            ->exists();

        if ($hasOutstandingRequest) {
            return back()->withErrors(['requisition' => 'There is already an outstanding requisition for this nation.']);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:2000'],
        ]);

        NationRequisition::query()->create([
            'faction_id' => $character->faction_id,
            'submitted_by_character_id' => $character->id,
            'title' => $validated['title'],
            'details' => $validated['details'],
            'status' => NationRequisition::STATUS_SUBMITTED,
        ]);

        return back()->with('status', 'Nation requisition submitted.');
    }
}
