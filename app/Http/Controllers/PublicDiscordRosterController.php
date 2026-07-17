<?php

namespace App\Http\Controllers;

use App\Models\DiscordRole;
use App\Models\User;
use App\Support\DiscordRosterBuilder;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicDiscordRosterController extends Controller
{
    public function show(string $nation, DiscordRosterBuilder $rosterBuilder): View
    {
        $roster = $rosterBuilder->build();
        $selectedNation = $roster['nations']->firstWhere('key', Str::slug($nation));

        if (! $selectedNation) {
            throw new NotFoundHttpException;
        }

        $memberIds = $selectedNation['members']
            ->map(fn (array $entry): string => $entry['member']->discord_user_id)
            ->unique()
            ->values();
        $filledRankDiscordIds = $selectedNation['rank_groups']
            ->pluck('rank.discord_id')
            ->filter()
            ->values();
        $unrankedGroup = $selectedNation['rank_groups']->firstWhere('rank', null);
        $newestMember = $selectedNation['members']
            ->filter(fn (array $entry): bool => (bool) $entry['member']->joined_at)
            ->sortByDesc(fn (array $entry) => $entry['member']->joined_at)
            ->first();
        $leadershipCount = $selectedNation['rank_groups']
            ->filter(fn (array $rankGroup): bool => $rankGroup['is_nation_leadership'])
            ->sum(fn (array $rankGroup): int => $rankGroup['members']->count());

        $rankDistribution = $selectedNation['rank_groups']
            ->map(fn (array $rankGroup): array => [
                'label' => $rankGroup['label'],
                'count' => $rankGroup['members']->count(),
                'badge_file' => $rankGroup['badge_file'],
                'is_nation_leadership' => $rankGroup['is_nation_leadership'],
                'percent' => $selectedNation['members']->isNotEmpty()
                    ? round(($rankGroup['members']->count() / $selectedNation['members']->count()) * 100)
                    : 0,
            ]);
        $unfilledRanks = $roster['rank_roles']
            ->reject(fn (DiscordRole $role): bool => $filledRankDiscordIds->contains($role->discord_id))
            ->values();

        return view('discord.public-roster', [
            'nation' => $selectedNation,
            'nations' => $roster['nations'],
            'lastSyncedAt' => $roster['last_synced_at'],
            'stats' => [
                'personnel_count' => $selectedNation['members']->count(),
                'filled_rank_count' => $filledRankDiscordIds->count(),
                'tracked_rank_count' => $roster['rank_roles']->count(),
                'unfilled_rank_count' => $unfilledRanks->count(),
                'unranked_count' => $unrankedGroup ? $unrankedGroup['members']->count() : 0,
                'leadership_count' => $leadershipCount,
                'linked_account_count' => $memberIds->isNotEmpty()
                    ? User::query()->whereIn('discord_user_id', $memberIds->all())->count()
                    : 0,
                'newest_member' => $newestMember,
                'top_rank' => $selectedNation['rank_groups']->first()['label'] ?? 'Unranked',
                'rank_distribution' => $rankDistribution,
                'unfilled_ranks' => $unfilledRanks,
            ],
        ]);
    }
}
