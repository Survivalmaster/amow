<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscordRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
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

        $roleGroups = $this->categoriseRoles($roles);

        return view('admin.discord-management', [
            'roles' => $roles,
            'roleGroups' => $roleGroups,
            'roleCategories' => $this->categoryDefinitions(),
            'categoryOverridesEnabled' => $this->categoryOverridesEnabled(),
            'lastSyncedAt' => $roles->max('synced_at'),
            'memberAssignmentCount' => $roles->sum('member_count'),
        ]);
    }

    public function updateCategory(Request $request, DiscordRole $discordRole): RedirectResponse
    {
        if (! $this->categoryOverridesEnabled()) {
            return back()->withErrors([
                'category' => 'Run the latest migrations before changing Discord role categories.',
            ]);
        }

        $validated = $request->validate([
            'category' => ['nullable', Rule::in($this->categoryDefinitions()->keys()->all())],
        ]);

        $discordRole->update([
            'category' => $validated['category'] ?: null,
        ]);

        return back()->with('status', 'Discord role category updated.');
    }

    private function categoriseRoles(Collection $roles): Collection
    {
        $definitions = $this->categoryDefinitions();

        return $roles
            ->groupBy(fn (DiscordRole $role): string => $this->categoryForRole($role))
            ->sortBy(fn (Collection $group, string $key): int => $definitions->keys()->search($key))
            ->map(fn (Collection $group, string $key): array => [
                'key' => $key,
                'label' => $definitions[$key]['label'],
                'description' => $definitions[$key]['description'],
                'roles' => $group,
                'role_count' => $group->count(),
                'member_count' => $group->sum('member_count'),
            ]);
    }

    private function categoryDefinitions(): Collection
    {
        return collect([
            'staff' => [
                'label' => 'Staff & Community Team',
                'description' => 'Admins, moderators, managers, creators, and other server team roles.',
            ],
            'nations' => [
                'label' => 'Nations & Ranks',
                'description' => 'Nation, faction, command, rank, and leadership roles.',
            ],
            'departments' => [
                'label' => 'Departments & Teams',
                'description' => 'Department, company, unit, job, and operational team roles.',
            ],
            'managed' => [
                'label' => 'Managed & Bot Roles',
                'description' => 'Discord-managed integration roles and bot-created roles.',
            ],
            'community' => [
                'label' => 'Community Roles',
                'description' => 'Personal, social, and general community roles.',
            ],
            'empty' => [
                'label' => 'Empty Roles',
                'description' => 'Roles that currently have no members assigned.',
            ],
        ]);
    }

    private function categoryForRole(DiscordRole $role): string
    {
        if ($role->category && $this->categoryDefinitions()->has($role->category)) {
            return $role->category;
        }

        $name = strtolower($role->name);

        if ($role->is_managed || $this->containsAny($name, ['bot', 'integration', 'webhook'])) {
            return 'managed';
        }

        if ($this->containsAny($name, ['admin', 'moderator', 'mod', 'staff', 'manager', 'owner', 'founder', 'developer', 'content creator'])) {
            return 'staff';
        }

        if ($this->containsAny($name, ['nation', 'faction', 'leader', 'leadership', 'command', 'rank', 'general', 'officer'])) {
            return 'nations';
        }

        if ($this->containsAny($name, ['department', 'police', 'fire', 'ems', 'team', 'unit', 'company', 'job', 'division'])) {
            return 'departments';
        }

        if ($role->member_count === 0) {
            return 'empty';
        }

        return 'community';
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

    private function categoryOverridesEnabled(): bool
    {
        return Schema::hasColumn('discord_roles', 'category');
    }
}
