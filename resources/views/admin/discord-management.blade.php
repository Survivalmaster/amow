<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord Management</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Synced Discord roles and the members currently assigned to them.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Roles</p>
                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ number_format($roles->count()) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Assignments</p>
                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ number_format($memberAssignmentCount) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Last Sync</p>
                <p class="mt-3 text-sm font-semibold text-[#d7edc7]">{{ $lastSyncedAt ? $lastSyncedAt->diffForHumans() : 'Not synced yet' }}</p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30"
            x-data="{
                openGroups: JSON.parse(localStorage.getItem('discordManagementOpenGroups') || '{}'),
                isOpen(key) {
                    return this.openGroups[key] ?? key === 'staff';
                },
                toggleGroup(key) {
                    this.openGroups[key] = ! this.isOpen(key);
                    localStorage.setItem('discordManagementOpenGroups', JSON.stringify(this.openGroups));
                }
            }"
        >
            <div class="border-b border-white/10 px-5 py-4">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Current Discord Roles</p>
                <p class="mt-1 text-sm text-white/55">The bot refreshes this snapshot from Discord.</p>
            </div>

            <div class="divide-y divide-white/10">
                @forelse ($roleGroups as $group)
                    <div>
                        <button
                            type="button"
                            @click="toggleGroup('{{ $group['key'] }}')"
                            class="flex w-full items-center gap-4 px-5 py-4 text-left transition hover:bg-white/[0.03]"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#7ead59]/25 bg-[#7ead59]/10 text-[#7ead59]">
                                <i class="fa-solid" :class="isOpen('{{ $group['key'] }}') ? 'fa-folder-open' : 'fa-folder'"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-semibold text-white">{{ $group['label'] }}</span>
                                <span class="mt-0.5 block text-xs text-white/45">{{ $group['description'] }}</span>
                            </span>
                            <span class="hidden text-right text-xs uppercase tracking-[0.16em] text-white/50 sm:block">
                                {{ number_format($group['role_count']) }} roles
                                <span class="block normal-case tracking-normal text-white/35">{{ number_format($group['member_count']) }} assignments</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-xs text-white/45 transition" :class="isOpen('{{ $group['key'] }}') ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="isOpen('{{ $group['key'] }}')" x-cloak class="border-t border-white/10 bg-black/10">
                            <div class="divide-y divide-white/10">
                                @foreach ($group['roles'] as $role)
                                    <details class="group/role">
                                        <summary class="grid cursor-pointer gap-3 px-5 py-3 transition hover:bg-white/[0.03] md:grid-cols-[minmax(0,1fr)_7rem_7rem_2rem] md:items-center">
                                            <div class="min-w-0">
                                                <div class="flex min-w-0 items-center gap-3">
                                                    <span class="h-3.5 w-3.5 shrink-0 rounded-full border border-white/15" style="background-color: {{ $role->color ?: '#7ead59' }}"></span>
                                                    <span class="truncate text-sm font-semibold text-white">{{ $role->name }}</span>
                                                    @if ($role->is_managed)
                                                        <span class="rounded-full border border-[#c2a84f]/30 bg-[#c2a84f]/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#f4d77a]">Managed</span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 truncate text-xs text-white/35">Role ID {{ $role->discord_id }}</p>
                                            </div>
                                            <div class="text-sm text-white/65">{{ number_format($role->member_count) }} members</div>
                                            <div class="text-sm text-white/45">Position {{ $role->position }}</div>
                                            <i class="fa-solid fa-chevron-down text-right text-xs text-white/45 transition group-open/role:rotate-180"></i>
                                        </summary>

                                        <div class="border-t border-white/10 bg-black/15 px-5 py-4">
                                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                                @forelse ($role->members as $member)
                                                    <div class="flex min-w-0 items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-3 py-3">
                                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#17271e] text-sm font-bold text-[#f4ecd0]">
                                                            @if ($member->avatar_url)
                                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->display_name ?? $member->username ?? 'Discord member' }} avatar" class="h-full w-full object-cover">
                                                            @else
                                                                {{ Str::upper(Str::substr($member->display_name ?? $member->username ?? '?', 0, 1)) }}
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-semibold text-white">{{ $member->display_name ?? $member->username ?? 'Unknown member' }}</p>
                                                            <p class="truncate text-xs text-white/45">{{ $member->username ?? $member->discord_user_id }}</p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-white/50">No members currently have this role.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-white/55">
                        No Discord roles have been synced yet. Start or restart the Discord bot after setting the website sync URL and secret.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
