<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Models\GameEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameMasterAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.game-master', [
            'events' => GameEvent::query()->with(['faction', 'creator'])->latest()->get(),
            'factions' => Faction::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'is_enabled' => ['nullable', 'boolean'],
            'ends_at' => ['nullable', 'date'],
            'xp_multiplier_enabled' => ['nullable', 'boolean'],
            'xp_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'credit_multiplier_enabled' => ['nullable', 'boolean'],
            'credit_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
        ]);

        GameEvent::query()->create([
            ...$validated,
            'created_by_user_id' => $request->user()->id,
            'is_enabled' => $request->boolean('is_enabled', true),
            'ends_at' => $request->date('ends_at'),
            'xp_multiplier_enabled' => $request->boolean('xp_multiplier_enabled') && filled($request->input('xp_multiplier')),
            'xp_multiplier' => $request->boolean('xp_multiplier_enabled') ? (float) $request->input('xp_multiplier') : null,
            'credit_multiplier_enabled' => $request->boolean('credit_multiplier_enabled') && filled($request->input('credit_multiplier')),
            'credit_multiplier' => $request->boolean('credit_multiplier_enabled') ? (float) $request->input('credit_multiplier') : null,
        ]);

        return back()->with('status', 'Game event created.');
    }

    public function update(Request $request, GameEvent $gameEvent): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:2000'],
            'faction_id' => ['nullable', 'exists:factions,id'],
            'is_enabled' => ['nullable', 'boolean'],
            'ends_at' => ['nullable', 'date'],
            'xp_multiplier_enabled' => ['nullable', 'boolean'],
            'xp_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'credit_multiplier_enabled' => ['nullable', 'boolean'],
            'credit_multiplier' => ['nullable', 'numeric', 'min:1', 'max:5'],
        ]);

        $gameEvent->update([
            ...$validated,
            'is_enabled' => $request->boolean('is_enabled'),
            'ends_at' => $request->date('ends_at'),
            'xp_multiplier_enabled' => $request->boolean('xp_multiplier_enabled') && filled($request->input('xp_multiplier')),
            'xp_multiplier' => $request->boolean('xp_multiplier_enabled') ? (float) $request->input('xp_multiplier') : null,
            'credit_multiplier_enabled' => $request->boolean('credit_multiplier_enabled') && filled($request->input('credit_multiplier')),
            'credit_multiplier' => $request->boolean('credit_multiplier_enabled') ? (float) $request->input('credit_multiplier') : null,
        ]);

        return back()->with('status', 'Game event updated.');
    }
}
