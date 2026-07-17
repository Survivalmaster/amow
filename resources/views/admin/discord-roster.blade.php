<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Discord Roster</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Nation membership grouped by synced rank roles.</p>
        </div>
    </x-slot>

    <div
        class="space-y-6"
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
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.discord-management.index') }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Roles</a>
            <a href="{{ route('admin.discord-management.roster') }}" class="rounded-full border border-[#7ead59]/40 bg-[#7ead59]/15 px-5 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Roster</a>
        </div>

        @if ($nations->isNotEmpty())
            @foreach ($nations as $nation)
                <section
                    x-show="isSelected('{{ $nation['key'] }}')"
                    x-cloak
                    class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-[linear-gradient(135deg,rgba(30,39,50,0.96),rgba(19,46,39,0.9))] p-6 shadow-2xl shadow-black/30"
                >
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-center">
                        <div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-semibold text-white/80">
                                    <span class="h-2.5 w-2.5 rounded-full border border-white/20" style="background-color: {{ $nation['color'] }}"></span>
                                    Nation Roster
                                </span>
                                <span class="rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-semibold text-white/80">Discord Sync</span>
                            </div>

                            <p class="mt-5 text-3xl font-bold tracking-normal text-[#f4ecd0]">{{ $nation['label'] }} Roster</p>
                            <p class="mt-2 max-w-2xl text-sm text-white/60">Active synced members for this nation, grouped by their highest Discord rank role.</p>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <span class="rounded-md border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-white/80">{{ number_format($nation['members']->count()) }} Members</span>
                                <span class="rounded-md border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-white/80">{{ number_format($nation['rank_groups']->count()) }} Ranks</span>
                                <span class="rounded-md border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-white/80">{{ $lastSyncedAt ? 'Synced '.$lastSyncedAt->diffForHumans() : 'Not synced yet' }}</span>
                            </div>
                        </div>

                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Nation</span>
                            <select
                                x-model="selectedNation"
                                @change="selectNation(selectedNation)"
                                class="rounded-xl border border-white/10 bg-[#24262b] px-4 py-3 text-sm font-semibold text-white/90"
                            >
                                @foreach ($nations as $optionNation)
                                    <option value="{{ $optionNation['key'] }}">{{ $optionNation['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </section>
            @endforeach

            <div class="hidden flex-wrap gap-2 lg:flex">
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

        @forelse ($nations as $nation)
            <div x-show="isSelected('{{ $nation['key'] }}')" x-cloak class="space-y-6">
                @foreach ($nation['rank_groups'] as $rankGroup)
                    @php($rank = $rankGroup['rank'])
                    <section class="rounded-[1.5rem] border border-white/10 bg-white/[0.06] p-6 shadow-xl shadow-black/20">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-3">
                                @if ($rankGroup['badge_file'])
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center p-1">
                                        <img src="{{ asset('images/military_rankings/'.$rankGroup['badge_file']) }}" alt="{{ $rankGroup['label'] }} insignia" class="max-h-full max-w-full object-contain" onerror="this.hidden = true">
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold uppercase tracking-[0.12em] text-[#e4edf8]">{{ $rankGroup['label'] }}</p>
                                    @if ($rankGroup['is_nation_leadership'])
                                        <span class="mt-2 inline-flex rounded-md border border-[#7ead59]/25 bg-[#7ead59]/10 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#d7edc7]">Nation Leadership</span>
                                    @endif
                                    @if ($rank)
                                        <p class="mt-1 text-xs text-white/45">Discord role position {{ $rank->position }}</p>
                                    @endif
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-white/55">{{ number_format($rankGroup['members']->count()) }} members</p>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @foreach ($rankGroup['members'] as $entry)
                                @php($member = $entry['member'])
                                <div class="flex min-w-0 items-center gap-4 rounded-[1.25rem] bg-black/10 p-4">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#17271e] text-xl font-bold text-[#f4ecd0] shadow-lg shadow-black/20">
                                        @if ($member->avatar_url)
                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->display_name ?? $member->username ?? 'Discord member' }} avatar" class="h-full w-full object-cover">
                                        @else
                                            {{ Str::upper(Str::substr($member->display_name ?? $member->username ?? '?', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-bold text-white">{{ $member->display_name ?? $member->username ?? 'Unknown member' }}</p>
                                        <p class="mt-1 truncate text-sm text-white/45">{{ $rankGroup['label'] }}</p>
                                        <p class="mt-3 truncate text-xs text-white/80"><span class="font-bold text-white">Discord:</span> {{ $member->username ?? $member->discord_user_id }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @empty
            <section class="rounded-[2rem] border border-white/10 bg-white/5 px-5 py-10 text-center text-sm text-white/55">
                No nation roles were found. Assign nation roles to a category with "Nation" in its name, then restart or sync the Discord bot.
            </section>
        @endforelse
    </div>
</x-app-layout>
