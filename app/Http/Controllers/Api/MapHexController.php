<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maps\UpdateMapHexRequest;
use App\Http\Resources\MapHexResource;
use App\Models\Faction;
use App\Models\MapHex;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapHexController extends Controller
{
    public function index(Request $request)
    {
        $canManage = (bool) ($request->user()?->loadMissing('permissions')->canAccessAdmin()
            || $request->user()?->hasPermission('territory-management'));

        $hexes = MapHex::query()
            ->with('faction')
            ->when(! $canManage || ! $request->boolean('include_hidden'), function ($query) {
                $query->where('is_visible', true)
                    ->whereNotIn('tile_type', [MapHex::TYPE_INACTIVE, MapHex::TYPE_DECORATIVE]);
            })
            ->orderBy('grid_row')
            ->orderBy('grid_column')
            ->get();

        return MapHexResource::collection($hexes);
    }

    public function show(MapHex $mapHex): MapHexResource
    {
        return MapHexResource::make($mapHex->load('faction'));
    }

    public function update(UpdateMapHexRequest $request, MapHex $mapHex): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('tile_type', $validated) && $validated['tile_type'] !== MapHex::TYPE_CLAIMABLE) {
            $validated['faction_id'] = null;
            $validated['claimed_at'] = null;
            $validated['claim_strength'] = 0;
        } elseif (array_key_exists('faction_id', $validated)) {
            $validated['claimed_at'] = $validated['faction_id'] ? ($mapHex->claimed_at ?? now()) : null;
        }

        $mapHex->update($validated);

        return response()->json([
            'data' => MapHexResource::make($mapHex->fresh('faction'))->resolve($request),
        ]);
    }

    public function destroyClaim(Request $request, MapHex $mapHex, \App\Services\Maps\MapClaimService $claimService): JsonResponse
    {
        $this->authorize('removeClaim', $mapHex);

        return response()->json([
            'data' => MapHexResource::make($claimService->removeClaim($mapHex, $request->user()))->resolve($request),
        ]);
    }
}
