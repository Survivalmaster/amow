<?php

namespace App\Support;

use App\Models\Character;
use App\Models\DiscordRole;
use App\Models\DiscordRoleMember;
use App\Models\Rank;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiscordCharacterRankSynchronizer
{
    public function syncLinkedCharacters(): int
    {
        $civilianRank = Rank::query()->where('name', 'Civilian')->first();

        if (! $civilianRank) {
            return 0;
        }

        $ranks = Rank::query()->orderByDesc('order_index')->get();
        $rankRoles = $this->rankRoles();
        $rankRoleIds = $rankRoles->pluck('id')->all();
        $discordRanksByUser = $rankRoleIds === []
            ? collect()
            : DiscordRoleMember::query()
                ->with('role')
                ->whereIn('discord_role_id', $rankRoleIds)
                ->get()
                ->groupBy('discord_user_id')
                ->map(fn (Collection $members): ?Rank => $this->rankForMembers($members, $ranks));

        $changed = 0;

        Character::query()
            ->with('user')
            ->whereHas('user', fn ($query) => $query->whereNotNull('discord_user_id'))
            ->orderBy('id')
            ->each(function (Character $character) use ($civilianRank, $discordRanksByUser, &$changed): void {
                $rank = $discordRanksByUser->get($character->user->discord_user_id) ?? $civilianRank;
                $roleType = $rank->is_military ? 'military' : 'civilian';

                if ((int) $character->rank_id === (int) $rank->id && $character->role_type === $roleType) {
                    return;
                }

                $character->forceFill([
                    'rank_id' => $rank->id,
                    'role_type' => $roleType,
                ])->save();

                $changed++;
            });

        return $changed;
    }

    private function rankRoles(): Collection
    {
        return DiscordRole::query()
            ->get()
            ->filter(fn (DiscordRole $role): bool => $this->isRankRole($role))
            ->values();
    }

    private function rankForMembers(Collection $members, Collection $ranks): ?Rank
    {
        return $members
            ->sortByDesc(fn (DiscordRoleMember $member): int => (int) ($member->role?->position ?? 0))
            ->map(fn (DiscordRoleMember $member): ?Rank => $this->rankForDiscordRole($member->role, $ranks))
            ->filter()
            ->first();
    }

    private function rankForDiscordRole(?DiscordRole $role, Collection $ranks): ?Rank
    {
        if (! $role) {
            return null;
        }

        $rankName = $this->normalizedRoleName($role);

        if ($rankName === '') {
            return null;
        }

        foreach ($this->rankAliases($ranks) as $localRankName => $aliases) {
            foreach ($aliases as $alias) {
                if ($this->nameMatches($rankName, $alias)) {
                    return $ranks->firstWhere('name', $localRankName);
                }
            }
        }

        return $ranks->first(fn (Rank $rank): bool => $this->nameMatches($rankName, strtolower($rank->name)));
    }

    private function rankAliases(Collection $ranks): array
    {
        $aliases = [
            'General' => ['lieutenant general', 'lt general', 'lt gen', 'major general', 'maj general', 'maj gen', 'brigadier general', 'brig general', 'brig gen', 'general', 'gen'],
            'Major' => ['major', 'maj'],
            'Captain' => ['captain', 'capt', 'cpt'],
            'Lieutenant' => ['lieutenant colonel', 'lt colonel', 'lt col', 'lieutenant', 'lt'],
            'Sergeant' => ['staff sergeant', 'ssgt', 'staff sgt', 'sergeant', 'sgt'],
            'Corporal' => ['corporal', 'cpl'],
            'Private' => ['private', 'pvt'],
            'Recruit' => ['recruit'],
        ];

        return collect($aliases)
            ->filter(fn (array $rankAliases, string $rankName): bool => $ranks->contains('name', $rankName))
            ->all();
    }

    private function isRankRole(DiscordRole $role): bool
    {
        $roleName = $this->normalizedRoleName($role);
        $categoryName = $this->categoryNameForRole($role);

        return $this->containsAny($categoryName, ['rank', 'command', 'leadership'])
            || $this->containsAny($roleName, ['general', 'colonel', 'captain', 'cpt', 'lieutenant', 'lt', 'sergeant', 'sgt', 'major', 'private', 'pvt', 'corporal', 'cpl', 'recruit', 'officer', 'commander']);
    }

    private function categoryNameForRole(DiscordRole $role): string
    {
        if (! $role->category) {
            return '';
        }

        if (! Schema::hasTable('discord_role_categories')) {
            return $this->normalizedText($role->category);
        }

        $categoryName = DB::table('discord_role_categories')
            ->where('slug', $role->category)
            ->value('name');

        return $this->normalizedText($categoryName ?: $role->category);
    }

    private function normalizedRoleName(DiscordRole $role): string
    {
        return $this->normalizedText($role->name);
    }

    private function normalizedText(string $value): string
    {
        $value = strtolower($value);
        $value = preg_replace('/\[[^\]]+\]|\([^)]*\)/', ' ', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function containsAny(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($this->nameMatches($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function nameMatches(string $value, string $pattern): bool
    {
        return (bool) preg_match('/\b'.preg_quote($pattern, '/').'\b/', $value);
    }
}
