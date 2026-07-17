<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DiscordBulkRankPlanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscordBulkDefaultRankPlanController extends Controller
{
    public function __invoke(Request $request, DiscordBulkRankPlanner $bulkRankPlanner): JsonResponse
    {
        $secret = config('services.discord.bot_sync_secret') ?: config('services.discord.linking_secret');

        if (! $secret || ! hash_equals($secret, (string) $request->header('X-Discord-Sync-Secret'))) {
            abort(403);
        }

        $validated = $request->validate([
            'default_rank_role_id' => ['nullable', 'string', 'max:255'],
            'nation_role_id' => ['nullable', 'string', 'max:255'],
        ]);

        $plan = $bulkRankPlanner->plan(
            $validated['default_rank_role_id'] ?? null,
            $validated['nation_role_id'] ?? null,
        );

        if (! $plan['default_rank_role']) {
            return response()->json([
                'message' => 'No default rank role could be found. Choose a rank role explicitly.',
            ], 422);
        }

        return response()->json([
            'default_rank_role' => [
                'id' => $plan['default_rank_role']->discord_id,
                'name' => $plan['default_rank_role']->name,
                'position' => $plan['default_rank_role']->position,
            ],
            'assignment_count' => $plan['assignments']->count(),
            'assignments' => $plan['assignments']->map(fn (array $assignment): array => [
                'member_id' => $assignment['member']->discord_user_id,
                'display_name' => $assignment['member']->display_name,
                'username' => $assignment['member']->username,
                'nation_role_id' => $assignment['nation']->discord_id,
                'nation_name' => $assignment['nation']->name,
                'rank_role_id' => $assignment['rank_role']->discord_id,
                'rank_name' => $assignment['rank_role']->name,
            ])->values(),
        ]);
    }
}
