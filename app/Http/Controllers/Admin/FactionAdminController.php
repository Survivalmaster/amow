<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use App\Services\Discord\AdminActionLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class FactionAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.factions', ['factions' => Faction::query()->orderBy('name')->get()]);
    }

    public function store(Request $request, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:factions,slug'],
            'short_description' => ['required', 'string', 'max:255'],
            'flag_image' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'lore' => ['nullable', 'string'],
        ]);

        $validated['color'] = isset($validated['color']) ? '#'.ltrim($validated['color'], '#') : null;
        $faction = Faction::query()->create($validated);
        $adminActionLogger->created($request->user(), 'Faction', $faction);

        return back()->with('status', 'Faction created.');
    }

    public function update(Request $request, Faction $faction, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $before = $adminActionLogger->snapshot($faction);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:factions,slug,'.$faction->id],
            'short_description' => ['required', 'string', 'max:255'],
            'flag_image' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'lore' => ['nullable', 'string'],
        ]);

        $validated['color'] = isset($validated['color']) ? '#'.ltrim($validated['color'], '#') : null;
        $faction->update($validated);
        $adminActionLogger->updated($request->user(), 'Faction', $before, $faction);

        return back()->with('status', 'Faction updated.');
    }

    public function destroy(Request $request, Faction $faction, AdminActionLogger $adminActionLogger): RedirectResponse
    {
        $snapshot = $adminActionLogger->snapshot($faction);
        try {
            $faction->delete();
        } catch (QueryException) {
            return back()->withErrors('Faction could not be deleted because related records still exist.');
        }

        $adminActionLogger->deleted($request->user(), 'Faction', $snapshot);

        return back()->with('status', 'Faction deleted.');
    }
}
