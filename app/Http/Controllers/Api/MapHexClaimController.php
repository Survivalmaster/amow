<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maps\ClaimMapHexRequest;
use App\Http\Resources\MapHexResource;
use App\Models\Faction;
use App\Models\MapHex;
use App\Services\Maps\MapClaimService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class MapHexClaimController extends Controller
{
    public function __invoke(ClaimMapHexRequest $request, MapHex $mapHex, MapClaimService $claimService): MapHexResource|JsonResponse
    {
        try {
            $faction = Faction::query()->findOrFail($request->integer('faction_id'));

            return response()->json([
                'data' => MapHexResource::make($claimService->claim($mapHex, $faction, $request->user()))->resolve($request),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
