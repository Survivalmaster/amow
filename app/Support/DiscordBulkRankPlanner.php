<?php

namespace App\Support;

use App\Models\DiscordRole;
use App\Models\DiscordRoleCategory;
use App\Models\DiscordRoleMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DiscordBulkRankPlanner
{
    public function plan(?string $defaultRankRoleId = null, ?string $nationRoleId = null): array
    {
        $roles = DiscordRole::query()
            ->with(['members' => fn ($query) => $query->orderBy('display_name')->orderBy('username')])
            ->orderByDesc('position')
            ->orderBy('name')
            ->get();

        $nationRoles = $roles
            ->filter(fn (DiscordRole $role): bool => $this->isNationRole($role))
            ->when($nationRoleId, fn (Collection $roles): Collection => $roles->where('discord_id', $nationRoleId))
            ->values();

        $rankRoles = $roles
            ->filter(fn (DiscordRole $role): bool => $this->isRankRole($role) && ! $this->isNationRole($role))
            ->sortByDesc('position')
            ->values();

        $defaultRankRole = $this->defaultRankRole($rankRoles, $defaultRankRoleId);
        $rankRoleIds = $rankRoles->pluck('id')->all();

        $rolesByMember = $roles
            ->flatMap(fn (DiscordRole $role) => $role->members->map(fn (DiscordRoleMember $member): array => [
                'member_id' => $member->discord_user_id,
                'role' => $role,
            ]))
            ->groupBy('member_id')
            ->map(fn (Collection $items): Collection => $items->pluck('role'));

        $nationGroups = $nationRoles
            ->groupBy(fn (DiscordRole $role): string => $this->nationKeyForRole($role))
            ->map(function (Collection $nationRoleGroup) use ($rolesByMember, $rankRoleIds, $defaultRankRole): array {
                $nationRoles = $nationRoleGroup->sortByDesc('position')->values();
                $displayRole = $nationRoles->first();

                $members = $nationRoles
                    ->flatMap(fn (DiscordRole $nationRole): Collection => $nationRole->members)
                    ->unique('discord_user_id')
                    ->values()
                    ->map(function (DiscordRoleMember $member) use ($rolesByMember, $rankRoleIds, $defaultRankRole, $displayRole): array {
                        $memberRankRoles = ($rolesByMember->get($member->discord_user_id) ?? collect())
                            ->filter(fn (DiscordRole $role): bool => in_array($role->id, $rankRoleIds, true))
                            ->sortByDesc('position')
                            ->values();

                        return [
                            'member' => $member,
                            'nation' => $displayRole,
                            'rank' => $memberRankRoles->first(),
                            'needs_default_rank' => $defaultRankRole && $memberRankRoles->isEmpty(),
                        ];
                    })
                    ->sortBy(fn (array $item): string => $item['member']->display_name ?? $item['member']->username ?? '')
                    ->values();

                return [
                    'key' => $this->nationKeyForRole($displayRole),
                    'label' => $this->nationLabelForRole($displayRole),
                    'color' => $displayRole->color ?: '#7ead59',
                    'roles' => $nationRoles,
                    'members' => $members,
                    'missing_rank_members' => $members->filter(fn (array $item): bool => $item['needs_default_rank'])->values(),
                ];
            })
            ->sortBy('label')
            ->values();

        $assignments = $defaultRankRole
            ? $nationGroups->flatMap(fn (array $nation): Collection => $nation['missing_rank_members']->map(fn (array $item): array => [
                'member' => $item['member'],
                'nation' => $item['nation'],
                'rank_role' => $defaultRankRole,
            ]))->unique(fn (array $assignment): string => $assignment['member']->discord_user_id)->values()
            : collect();

        return [
            'roles' => $roles,
            'nation_roles' => $nationRoles,
            'rank_roles' => $rankRoles,
            'default_rank_role' => $defaultRankRole,
            'nation_groups' => $nationGroups,
            'assignments' => $assignments,
        ];
    }

    private function defaultRankRole(Collection $rankRoles, ?string $defaultRankRoleId): ?DiscordRole
    {
        if ($defaultRankRoleId) {
            return $rankRoles->firstWhere('discord_id', $defaultRankRoleId);
        }

        return $rankRoles->first(fn (DiscordRole $role): bool => $this->rankNameMatches($this->normalizedRoleName($role), 'private'))
            ?? $rankRoles->first(fn (DiscordRole $role): bool => $this->rankNameMatches($this->normalizedRoleName($role), 'pvt'));
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

    private function normalizedRoleName(DiscordRole $role): string
    {
        $roleName = strtolower($role->name);
        $roleName = preg_replace('/\[[^\]]+\]|\([^)]*\)/', ' ', $roleName) ?? $roleName;
        $roleName = preg_replace('/[^a-z0-9]+/', ' ', $roleName) ?? $roleName;

        return trim(preg_replace('/\s+/', ' ', $roleName) ?? $roleName);
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
