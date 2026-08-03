<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CharacterLogAdminController extends Controller
{
    private const VISIBLE_LOG_TYPES = [
        'work',
        'item_purchase',
        'licence_purchase',
        'stock_buy',
        'stock_sell',
        'job_change',
        'rank_change',
    ];

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
                ->whereIn('type', self::VISIBLE_LOG_TYPES)
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
