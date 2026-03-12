<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FactionAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.factions', ['factions' => Faction::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:factions,slug'],
            'short_description' => ['required', 'string', 'max:255'],
            'flag_image' => ['nullable', 'string', 'max:255'],
            'lore' => ['nullable', 'string'],
        ]);

        Faction::query()->create($validated);

        return back()->with('status', 'Faction created.');
    }
}
