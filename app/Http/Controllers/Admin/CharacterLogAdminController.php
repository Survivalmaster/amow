<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CharacterLogAdminController extends Controller
{
    public function index(Request $request): View
    {
        $characters = Character::query()
            ->with(['user', 'faction', 'rank', 'currentJob'])
            ->orderBy('name')
            ->get();

        $selectedCharacter = null;
        $transactions = null;

        if ($characters->isNotEmpty()) {
            $selectedCharacter = $request->integer('character_id')
                ? $characters->firstWhere('id', $request->integer('character_id'))
                : $characters->first();

            $selectedCharacter ??= $characters->first();
            $selectedCharacter->loadMissing(['user', 'faction', 'rank', 'currentJob']);

            $transactions = $selectedCharacter
                ->transactions()
                ->latest()
                ->paginate(50)
                ->withQueryString();
        }

        return view('admin.character-logs', [
            'characters' => $characters,
            'selectedCharacter' => $selectedCharacter,
            'transactions' => $transactions,
        ]);
    }
}
