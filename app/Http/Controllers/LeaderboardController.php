<?php

namespace App\Http\Controllers;

use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function index(): View
    {
        return view('leaderboards.index', [
            'wealth' => Character::query()->with(['faction', 'rank'])->orderByDesc('plastic_credits')->limit(10)->get(),
            'rankings' => Character::query()->with(['faction', 'rank'])->orderByDesc('rank_id')->limit(10)->get(),
            'influence' => Character::query()->with(['faction', 'rank'])->orderByDesc('influence_score')->limit(10)->get(),
            'military' => Character::query()->with(['faction', 'rank'])->orderByDesc('military_score')->limit(10)->get(),
            'economy' => Character::query()->with(['faction', 'rank'])->orderByDesc('economic_score')->limit(10)->get(),
        ]);
    }
}
