<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord Roster</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Nation membership grouped by synced rank roles.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.discord-management.index') }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Roles</a>
            <a href="{{ route('admin.discord-management.roster') }}" class="rounded-full border border-[#7ead59]/40 bg-[#7ead59]/15 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Roster</a>
        </div>

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Nations</p>
                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ number_format($nationRoleCount) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Rank Roles</p>
                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ number_format($rankRoleCount) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Roster Entries</p>
                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ number_format($memberCount) }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Last Sync</p>
                <p class="mt-3 text-sm font-semibold text-[#d7edc7]">{{ $lastSyncedAt ? $lastSyncedAt->diffForHumans() : 'Not synced yet' }}</p>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30"
            x-data="{
                selectedNation: localStorage.getItem('discordRosterSelectedNation') || @js($nations->first()['key'] ?? ''),
                selectNation(key) {
                    this.selectedNation = key;
                    localStorage.setItem('discordRosterSelectedNation', key);
                },
                isSelected(key) {
                    return this.selectedNation === key;
                }
            }"
        >
            <div class="border-b border-white/10 px-5 py-4">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Nation Roster</p>
                        <p class="mt-1 text-sm text-white/55">Pick one nation to view its rank hierarchy.</p>
                    </div>

                    @if ($nations->isNotEmpty())
                        <label class="grid gap-2 text-sm text-white/70 sm:min-w-64 xl:hidden">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Nation</span>
                            <select
                                x-model="selectedNation"
                                @change="selectNation(selectedNation)"
                                class="rounded-xl border border-white/10 bg-black/25 px-3 py-2 text-sm font-semibold text-white/80"
                            >
                                @foreach ($nations as $nation)
                                    <option value="{{ $nation['key'] }}">{{ $nation['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                </div>

                @if ($nations->isNotEmpty())
                    <div class="mt-5 hidden flex-wrap gap-2 xl:flex">
                        @foreach ($nations as $nation)
                            <button
                                type="button"
                                @click="selectNation('{{ $nation['key'] }}')"
                                class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] transition"
                                :class="isSelected('{{ $nation['key'] }}') ? 'border-[#7ead59]/45 bg-[#7ead59]/15 text-[#d7edc7]' : 'border-white/10 bg-white/5 text-white/65 hover:bg-white/10'"
                            >
                                <span class="h-2.5 w-2.5 rounded-full border border-white/15" style="background-color: {{ $nation['color'] }}"></span>
                                {{ $nation['label'] }}
                                <span class="text-white/35">{{ number_format($nation['members']->count()) }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                @forelse ($nations as $nation)
                    <div x-show="isSelected('{{ $nation['key'] }}')" x-cloak>
                        <div class="border-b border-white/10 bg-black/10 px-5 py-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="h-5 w-5 shrink-0 rounded-full border border-white/15" style="background-color: {{ $nation['color'] }}"></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-white">{{ $nation['label'] }}</p>
                                        <p class="mt-0.5 text-xs text-white/45">
                                            {{ number_format($nation['members']->count()) }} members across {{ number_format($nation['rank_groups']->count()) }} ranks
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-black/10 px-5 py-5">
                            <div class="relative space-y-4 border-l border-[#7ead59]/25 pl-5">
                            @foreach ($nation['rank_groups'] as $rankGroup)
                                @php($rank = $rankGroup['rank'])
                                <div class="relative">
                                    <span class="absolute -left-[1.65rem] top-4 h-3 w-3 rounded-full border border-[#7ead59]/40 bg-[#07100c]"></span>
                                    <div class="rounded-[1.25rem] border border-white/10 bg-[#07100c]/70 p-4">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-white">{{ $rankGroup['label'] }}</p>
                                                <p class="mt-1 text-xs text-white/45">
                                                    {{ number_format($rankGroup['members']->count()) }} members
                                                    @if ($rank)
                                                        <span class="ml-2">Position {{ $rank->position }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                                            @foreach ($rankGroup['members'] as $entry)
                                                @php($member = $entry['member'])
                                                <div class="flex min-w-0 items-center gap-3 rounded-xl border border-white/10 bg-black/20 px-3 py-3">
                                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#17271e] text-sm font-bold text-[#f4ecd0]">
                                                        @if ($member->avatar_url)
                                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->display_name ?? $member->username ?? 'Discord member' }} avatar" class="h-full w-full object-cover">
                                                        @else
                                                            {{ Str::upper(Str::substr($member->display_name ?? $member->username ?? '?', 0, 1)) }}
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-white">{{ $member->display_name ?? $member->username ?? 'Unknown member' }}</p>
                                                        <p class="truncate text-xs text-white/45">{{ $member->username ?? $member->discord_user_id }}</p>
                                                        <p class="mt-1 truncate text-xs text-[#d7edc7]">{{ $rankGroup['label'] }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-white/55">
                        No nation roles were found. Assign nation roles to a category with "Nation" in its name, then restart or sync the Discord bot.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
