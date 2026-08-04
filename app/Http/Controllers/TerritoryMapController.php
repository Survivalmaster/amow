<?php

namespace App\Http\Controllers;

use App\Models\Faction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TerritoryMapController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()?->loadMissing('permissions');

        abort_unless($user?->hasPermission('developer'), 403);

        $canManageTerritory = (bool) ($user?->canAccessAdmin() || $user?->hasPermission('territory-management'));
        $mapImagePath = public_path('images/world-map.webp');
        $mapDimensions = file_exists($mapImagePath) ? getimagesize($mapImagePath) : null;

        return view('territory-map.index', [
            'factions' => Faction::query()->orderBy('name')->get(),
            'canManageTerritory' => $canManageTerritory,
            'mapImageUrl' => asset('images/world-map.webp'),
            'mapWidth' => (int) ($mapDimensions[0] ?? 1280),
            'mapHeight' => (int) ($mapDimensions[1] ?? 1280),
        ]);
    }
}
