<?php

namespace App\Support;

use App\Models\DiscordRole;
use App\Models\DiscordRoleCategory;
use App\Models\DiscordRoleMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DiscordRosterBuilder
{
    public function build(): array
    {
        $roles = DiscordRole::query()
            ->with(['members' => fn ($query) => $query->orderBy('display_name')->orderBy('username')])
            ->orderByDesc('position')
            ->orderBy('name')
            ->get();

        $nationRoles = $roles
            ->filter(fn (DiscordRole $role): bool => $this->isNationRole($role))
            ->values();

        $rankRoles = $roles
            ->filter(fn (DiscordRole $role): bool => $this->isRankRole($role) && ! $this->isNationRole($role))
            ->sortByDesc('position')
            ->values();

        $rolesByMember = $roles
            ->flatMap(fn (DiscordRole $role) => $role->members->map(fn (DiscordRoleMember $member): array => [
                'member_id' => $member->discord_user_id,
                'role' => $role,
            ]))
            ->groupBy('member_id')
            ->map(fn (Collection $items): Collection => $items->pluck('role'));

        $rankRoleIds = $rankRoles->pluck('id')->all();

        $nations = $nationRoles
            ->groupBy(fn (DiscordRole $role): string => $this->nationKeyForRole($role))
            ->map(function (Collection $nationRoleGroup) use ($rolesByMember, $rankRoleIds): array {
                $nationRoles = $nationRoleGroup->sortByDesc('position')->values();
                $displayRole = $nationRoles->first();
                $members = $nationRoles
                    ->flatMap(fn (DiscordRole $nationRole): Collection => $nationRole->members)
                    ->unique('discord_user_id')
                    ->values()
                    ->map(function (DiscordRoleMember $member) use ($rolesByMember, $rankRoleIds): array {
                        $memberRankRoles = ($rolesByMember->get($member->discord_user_id) ?? collect())
                            ->filter(fn (DiscordRole $role): bool => in_array($role->id, $rankRoleIds, true))
                            ->sortByDesc('position')
                            ->values();

                        return [
                            'member' => $member,
                            'rank' => $memberRankRoles->first(),
                            'rank_roles' => $memberRankRoles,
                        ];
                    })
                    ->sortBy([
                        fn (array $left, array $right): int => ($right['rank']?->position ?? -1) <=> ($left['rank']?->position ?? -1),
                        fn (array $left, array $right): int => strnatcasecmp($left['member']->display_name ?? $left['member']->username ?? '', $right['member']->display_name ?? $right['member']->username ?? ''),
                    ])
                    ->values();

                $rankGroups = $members
                    ->groupBy(fn (array $item): string => $item['rank']?->discord_id ?? 'unranked')
                    ->map(function (Collection $rankMembers): array {
                        $rank = $rankMembers->first()['rank'];

                        return [
                            'rank' => $rank,
                            'label' => $rank?->name ?? 'Unranked',
                            'badge_file' => $this->rankBadgeFileForRole($rank),
                            'is_nation_leadership' => $this->isNationLeadershipRank($rank),
                            'position' => $rank?->position ?? -1,
                            'members' => $rankMembers,
                        ];
                    })
                    ->sortByDesc('position')
                    ->values();

                return [
                    'key' => $this->nationKeyForRole($displayRole),
                    'label' => $this->nationLabelForRole($displayRole),
                    'color' => $displayRole->color ?: '#7ead59',
                    'roles' => $nationRoles,
                    'members' => $members,
                    'rank_groups' => $rankGroups,
                ];
            })
            ->sortBy('label')
            ->values();

        return [
            'roles' => $roles,
            'nations' => $nations,
            'rank_roles' => $rankRoles,
            'last_synced_at' => $roles->max('synced_at'),
        ];
    }

    public function nationBySlug(string $slug): ?array
    {
        return $this->build()['nations']->firstWhere('key', Str::slug($slug));
    }

    private function isNationRole(DiscordRole $role): bool
    {
        $roleName = strtolower($role->name);
        $categoryName = $this->categoryNameForRole($role);

        if ($this->containsAny($categoryName, ['nation'])) {
            return ! $this->containsAny($roleName, ['rank', 'general', 'colonel', 'captain', 'cpt', 'lieutenant', 'lt', 'sergeant', 'sgt', 'major', 'private', 'officer', 'commander']);
        }

        return str_ends_with($roleName, ' nation')
            || $this->containsAny($roleName, ['green nation', 'blue nation', 'red nation', 'yellow nation', 'purple nation', 'orange nation']);
    }

    private function isRankRole(DiscordRole $role): bool
    {
        $roleName = strtolower($role->name);
        $categoryName = $this->categoryNameForRole($role);

        return $this->containsAny($categoryName, ['rank', 'command', 'leadership'])
            || $this->containsAny($roleName, ['rank', 'general', 'colonel', 'captain', 'cpt', 'lieutenant', 'lt', 'sergeant', 'sgt', 'major', 'private', 'officer', 'commander']);
    }

    private function categoryNameForRole(DiscordRole $role): string
    {
        $definitions = $this->categoryDefinitions();
        $key = $this->categoryForRole($role);

        return strtolower($definitions[$key]['label'] ?? $key);
    }

    private function categoryForRole(DiscordRole $role): string
    {
        $definitions = $this->categoryDefinitions();

        if ($role->category && $definitions->has($role->category)) {
            return $role->category;
        }

        $name = strtolower($role->name);
        $guessedCategory = match (true) {
            $role->is_managed || $this->containsAny($name, ['bot', 'integration', 'webhook']) => 'managed',
            $this->containsAny($name, ['admin', 'moderator', 'mod', 'staff', 'manager', 'owner', 'founder', 'developer', 'content creator']) => 'staff',
            $this->containsAny($name, ['nation', 'faction', 'leader', 'leadership', 'command', 'rank', 'general', 'officer']) => 'nations',
            $this->containsAny($name, ['department', 'police', 'fire', 'ems', 'team', 'unit', 'company', 'job', 'division']) => 'departments',
            $role->member_count === 0 => 'empty',
            default => 'community',
        };

        return $definitions->has($guessedCategory) ? $guessedCategory : 'uncategorized';
    }

    private function categoryDefinitions(): Collection
    {
        if (! Schema::hasTable('discord_role_categories')) {
            return collect([
                'staff' => ['label' => 'Staff & Community Team'],
                'nations' => ['label' => 'Nations & Ranks'],
                'departments' => ['label' => 'Departments & Teams'],
                'managed' => ['label' => 'Managed & Bot Roles'],
                'community' => ['label' => 'Community Roles'],
                'empty' => ['label' => 'Empty Roles'],
                'uncategorized' => ['label' => 'Uncategorized'],
            ]);
        }

        $definitions = DiscordRoleCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->keyBy('slug')
            ->map(fn (DiscordRoleCategory $category): array => ['label' => $category->name]);

        if (! $definitions->has('uncategorized')) {
            $definitions->put('uncategorized', ['label' => 'Uncategorized']);
        }

        return $definitions;
    }

    private function nationKeyForRole(DiscordRole $role): string
    {
        return Str::slug($this->nationLabelForRole($role)) ?: $role->discord_id;
    }

    private function nationLabelForRole(DiscordRole $role): string
    {
        $label = preg_replace('/\b(high command|nation|leadership|leaders|leader|command|hq)\b/i', '', $role->name);
        $label = trim(preg_replace('/\s+/', ' ', str_replace(['-', '_', '|'], ' ', $label ?? '')));

        return $label !== '' ? $label : $role->name;
    }

    private function rankBadgeFileForRole(?DiscordRole $role): ?string
    {
        $rankName = $this->normalizedRankName($role);

        if ($rankName === '') {
            return null;
        }

        $rankFiles = [
            ['file' => 'Lieutenant General.png', 'patterns' => ['lieutenant general', 'lt general', 'lt gen']],
            ['file' => 'Major General.png', 'patterns' => ['major general', 'maj general', 'maj gen']],
            ['file' => 'Brigadier General.png', 'patterns' => ['brigadier general', 'brig general', 'brig gen']],
            ['file' => 'Lieutenant Colonel.png', 'patterns' => ['lieutenant colonel', 'lt colonel', 'lt col']],
            ['file' => 'Staff Sergeant.png', 'patterns' => ['staff sergeant', 'ssgt', 'staff sgt']],
            ['file' => 'Warrant Officer.png', 'patterns' => ['warrant officer']],
            ['file' => 'General.png', 'patterns' => ['general', 'gen']],
            ['file' => 'Colonel.png', 'patterns' => ['colonel', 'col']],
            ['file' => 'Captain.png', 'patterns' => ['captain', 'cpt', 'capt']],
            ['file' => 'Lieutenant.png', 'patterns' => ['lieutenant', 'lt']],
            ['file' => 'Major.png', 'patterns' => ['major', 'maj']],
            ['file' => 'Sergeant.png', 'patterns' => ['sergeant', 'sgt']],
            ['file' => 'Corporal.png', 'patterns' => ['corporal', 'cpl']],
            ['file' => 'Private.png', 'patterns' => ['private', 'pvt']],
        ];

        foreach ($rankFiles as $rankFile) {
            foreach ($rankFile['patterns'] as $pattern) {
                if ($this->rankNameMatches($rankName, $pattern)) {
                    return $rankFile['file'];
                }
            }
        }

        return null;
    }

    private function isNationLeadershipRank(?DiscordRole $role): bool
    {
        $rankName = $this->normalizedRankName($role);

        if ($rankName === '') {
            return false;
        }

        foreach (['lieutenant colonel', 'lt colonel', 'lt col'] as $excludedPattern) {
            if ($this->rankNameMatches($rankName, $excludedPattern)) {
                return false;
            }
        }

        foreach (['colonel', 'col', 'brigadier general', 'brig general', 'brig gen', 'major general', 'maj general', 'maj gen', 'lieutenant general', 'lt general', 'lt gen', 'general', 'gen'] as $leadershipPattern) {
            if ($this->rankNameMatches($rankName, $leadershipPattern)) {
                return true;
            }
        }

        return false;
    }

    private function normalizedRankName(?DiscordRole $role): string
    {
        if (! $role) {
            return '';
        }

        $rankName = strtolower($role->name);
        $rankName = preg_replace('/\[[^\]]+\]|\([^)]*\)/', ' ', $rankName) ?? $rankName;
        $rankName = preg_replace('/[^a-z0-9]+/', ' ', $rankName) ?? $rankName;

        return trim(preg_replace('/\s+/', ' ', $rankName) ?? $rankName);
    }

    private function rankNameMatches(string $rankName, string $pattern): bool
    {
        return (bool) preg_match('/\b'.preg_quote($pattern, '/').'\b/', $rankName);
    }

    private function containsAny(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }
}
