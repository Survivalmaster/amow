<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\City;
use App\Models\Faction;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $onlineUsers = $this->onlineUsers();

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::query()->count(),
                'characters' => Character::query()->count(),
                'factions' => Faction::query()->count(),
                'cities' => City::query()->count(),
                'locations' => Location::query()->count(),
                'items' => Item::query()->count(),
                'online_users' => $onlineUsers->count(),
            ],
            'onlineUsers' => $onlineUsers,
        ]);
    }

    public function state(): JsonResponse
    {
        $onlineUsers = $this->onlineUsers();

        return response()->json([
            'online_count' => $onlineUsers->count(),
            'online_users' => $onlineUsers->map(fn (User $user) => [
                'id' => $user->id,
                'account_name' => $user->name,
                'character_name' => $user->character?->name,
                'current_page_name' => $user->current_page_name ?: 'Unknown Page',
                'current_path' => $user->current_path ?: '/',
                'last_seen_label' => optional($user->last_seen_at)?->timezone(config('app.timezone'))->format('H:i:s') ?? 'Unknown',
            ])->values(),
        ]);
    }

    private function onlineUsers()
    {
        return User::query()
            ->with('character')
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->orderByDesc('last_seen_at')
            ->get();
    }
}
