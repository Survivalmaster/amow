<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord Commands</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Bot commands powered by the synced Discord role snapshot.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.discord-management.index') }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Roles</a>
            <a href="{{ route('admin.discord-management.roster') }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Roster</a>
            <a href="{{ route('admin.discord-management.commands') }}" class="rounded-full border border-[#7ead59]/40 bg-[#7ead59]/15 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Commands</a>
        </div>

        <section class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-[linear-gradient(135deg,rgba(30,39,50,0.96),rgba(19,46,39,0.9))] p-6 shadow-2xl shadow-black/30">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-center">
                <div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-semibold text-white/80">Manage Roles</span>
                        <span class="rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-semibold text-white/80">Bulk Rank</span>
                    </div>
                    <p class="mt-5 text-3xl font-bold tracking-normal text-[#f4ecd0]">Default Rank Automation</p>
                    <p class="mt-2 max-w-3xl text-sm text-white/60">Find nation members who do not have any synced rank role, then let the Discord bot assign the default rank in bulk.</p>
                    <div class="mt-5 inline-flex rounded-xl border border-white/10 bg-black/25 px-4 py-3 font-mono text-sm text-[#d7edc7]">/rank-tools default-rank apply:true</div>
                </div>

                <div class="grid gap-3">
                    <div class="rounded-[1.25rem] border border-white/10 bg-black/15 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Default Rank</p>
                        <p class="mt-2 text-lg font-bold text-white">{{ $defaultRankRole?->name ?? 'Not detected' }}</p>
                    </div>
                    <div class="rounded-[1.25rem] border border-white/10 bg-black/15 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Missing Ranks</p>
                        <p class="mt-2 text-lg font-bold text-white">{{ number_format($assignmentCount) }} members</p>
                    </div>
                    <div class="rounded-[1.25rem] border border-white/10 bg-black/15 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Last Sync</p>
                        <p class="mt-2 text-sm font-semibold text-[#d7edc7]">{{ $lastSyncedAt ? $lastSyncedAt->diffForHumans() : 'Not synced yet' }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if (! $defaultRankRole)
            <div class="rounded-[1.5rem] border border-[#c2a84f]/25 bg-[#c2a84f]/10 px-5 py-4 text-sm text-[#f4d77a]">
                No Private rank role was detected. Run the command with a rank role option, or make sure your Private role is assigned to a category with "Rank" in its name.
            </div>
        @endif

        <section class="rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="border-b border-white/10 px-5 py-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Bulk Default Rank Preview</p>
                        <p class="mt-1 text-sm text-white/55">These are the members the command would rank based on the latest bot sync.</p>
                    </div>
                    <p class="text-sm font-semibold text-white/55">{{ number_format($nationGroups->count()) }} nations</p>
                </div>
            </div>

            <div class="divide-y divide-white/10">
                @forelse ($nationGroups as $nation)
                    <details class="group bg-black/10" @if ($nation['missing_rank_members']->isNotEmpty()) open @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-4 px-5 py-4 transition hover:bg-white/[0.03]">
                            <span class="h-3.5 w-3.5 shrink-0 rounded-full border border-white/15" style="background-color: {{ $nation['color'] }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-semibold text-white">{{ $nation['label'] }}</span>
                                <span class="mt-0.5 block text-xs text-white/45">{{ number_format($nation['members']->count()) }} nation members</span>
                            </span>
                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-white/65">{{ number_format($nation['missing_rank_members']->count()) }} missing ranks</span>
                            <i class="fa-solid fa-chevron-down text-xs text-white/45 transition group-open:rotate-180"></i>
                        </summary>

                        <div class="border-t border-white/10 px-5 py-5">
                            @if ($nation['missing_rank_members']->isNotEmpty())
                                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($nation['missing_rank_members'] as $entry)
                                        @php($member = $entry['member'])
                                        <div class="flex min-w-0 items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-3 py-2.5">
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
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-white/50">Everyone in this nation already has a synced rank role.</p>
                            @endif
                        </div>
                    </details>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-white/55">
                        No nation roles were found. Assign nation roles to a category with "Nation" in its name, then sync the Discord bot.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
