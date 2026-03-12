<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Faction;
use App\Models\Rank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CharacterAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.characters', [
            'characters' => Character::query()->with(['user', 'faction', 'rank'])->orderBy('name')->get(),
            'factions' => Faction::query()->orderBy('name')->get(),
            'ranks' => Rank::query()->orderBy('order_index')->get(),
        ]);
    }

    public function update(Request $request, Character $character): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'between:16,80'],
            'faction_id' => ['required', 'exists:factions,id'],
            'rank_id' => ['required', 'exists:ranks,id'],
            'starting_occupation' => ['required', 'in:Laborer,Merchant,Mechanic'],
            'role_type' => ['required', 'in:civilian,military'],
            'plastic_credits' => ['required', 'integer', 'min:0'],
            'influence_score' => ['required', 'integer', 'min:0'],
            'military_score' => ['required', 'integer', 'min:0'],
            'economic_score' => ['required', 'integer', 'min:0'],
            'is_business_owner' => ['nullable', 'boolean'],
            'biography' => ['required', 'string', 'max:2000'],
        ]);

        $character->update([
            ...$validated,
            'is_business_owner' => $request->boolean('is_business_owner'),
        ]);

        return back()->with('status', "Updated character {$character->name}.");
    }
}
