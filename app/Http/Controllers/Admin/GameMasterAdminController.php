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
        ]);

        GameEvent::query()->create([
            ...$validated,
            'created_by_user_id' => $request->user()->id,
            'is_enabled' => $request->boolean('is_enabled', true),
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
        ]);

        $gameEvent->update([
            ...$validated,
            'is_enabled' => $request->boolean('is_enabled'),
        ]);

        return back()->with('status', 'Game event updated.');
    }
}
