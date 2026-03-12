<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\City;
use App\Models\Faction;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::query()->count(),
                'characters' => Character::query()->count(),
                'factions' => Faction::query()->count(),
                'cities' => City::query()->count(),
                'locations' => Location::query()->count(),
                'items' => Item::query()->count(),
            ],
        ]);
    }
}
