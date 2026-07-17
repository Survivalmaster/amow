<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordRole;
use Illuminate\View\View;

class DiscordManagementController extends Controller
{
    public function index(): View
    {
        $roles = DiscordRole::query()
            ->with(['members' => fn ($query) => $query->orderBy('display_name')->orderBy('username')])
            ->orderByDesc('position')
            ->orderBy('name')
            ->get();

        return view('admin.discord-management', [
            'roles' => $roles,
            'lastSyncedAt' => $roles->max('synced_at'),
            'memberAssignmentCount' => $roles->sum('member_count'),
        ]);
    }
}
