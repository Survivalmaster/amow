<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="background-color: #08090b; background-image: none;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $nation['label'] }} Roster - {{ config('app.name', 'AMOW') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=rajdhani:500,600,700|teko:500,600,700&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-[#08090b] font-sans text-[#f4ecd0] antialiased" style="background-color: #08090b; background-image: none;">
        <main class="mx-auto min-h-screen w-full max-w-7xl px-4 py-5 sm:px-6 sm:py-8 lg:px-8">
            <section class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-[#111317] p-5 shadow-2xl shadow-black/30 sm:p-7">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-start">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-semibold text-white/80">
                                <span class="h-2.5 w-2.5 rounded-full border border-white/20" style="background-color: {{ $nation['color'] }}"></span>
                                Nation Roster
                            </span>
                            <span class="rounded-md border border-white/10 bg-white/5 px-2 py-1 text-[11px] font-semibold text-white/80">Discord Sync</span>
                        </div>

                        <h1 class="mt-5 font-['Teko'] text-5xl uppercase leading-none tracking-[0.1em] text-[#f4ecd0] sm:text-6xl">{{ $nation['label'] }}</h1>
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-white/60 sm:text-base">Active Discord personnel grouped by rank, ordered from highest command down through the nation structure.</p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-[1.1rem] border border-white/10 bg-black/15 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Personnel</p>
                                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em] text-white">{{ number_format($stats['personnel_count']) }}</p>
                            </div>
                            <div class="rounded-[1.1rem] border border-white/10 bg-black/15 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Filled Ranks</p>
                                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em] text-white">{{ number_format($stats['filled_rank_count']) }}<span class="text-xl text-white/35">/{{ number_format($stats['tracked_rank_count']) }}</span></p>
                            </div>
                            <div class="rounded-[1.1rem] border border-white/10 bg-black/15 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Leadership</p>
                                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em] text-white">{{ number_format($stats['leadership_count']) }}</p>
                            </div>
                            <div class="rounded-[1.1rem] border border-white/10 bg-black/15 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Linked AMOW</p>
                                <p class="mt-2 font-['Teko'] text-4xl uppercase tracking-[0.08em] text-white">{{ number_format($stats['linked_account_count']) }}</p>
                            </div>
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-[1.1rem] border border-white/10 bg-black/15 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Top Rank</p>
                                <p class="mt-1 truncate text-sm font-bold text-white/85">{{ $stats['top_rank'] }}</p>
                            </div>
                            <div class="rounded-[1.1rem] border border-white/10 bg-black/15 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Unranked</p>
                                <p class="mt-1 text-sm font-bold text-white/85">{{ number_format($stats['unranked_count']) }} members</p>
                            </div>
                            <div class="rounded-[1.1rem] border border-white/10 bg-black/15 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40">Last Sync</p>
                                <p class="mt-1 text-sm font-bold text-white/85">{{ $lastSyncedAt ? $lastSyncedAt->diffForHumans() : 'Not synced yet' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-[1.25rem] border border-white/10 bg-black/15 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Rank Strength</p>
                                <p class="text-xs font-semibold text-white/45">{{ number_format($stats['personnel_count']) }} total</p>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach ($stats['rank_distribution']->take(7) as $rankStat)
                                    <div>
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="min-w-0 truncate font-semibold text-white/82">{{ $rankStat['label'] }}</span>
                                            <span class="shrink-0 text-white/55">{{ number_format($rankStat['count']) }}</span>
                                        </div>
                                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-white/10">
                                            <div class="h-full rounded-full bg-white/55" style="width: {{ max(4, min(100, $rankStat['percent'])) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-[1.25rem] border border-white/10 bg-black/15 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Unfilled Ranks</p>
                            @if ($stats['unfilled_ranks']->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($stats['unfilled_ranks']->take(8) as $unfilledRank)
                                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-white/70">{{ $unfilledRank->name }}</span>
                                    @endforeach
                                    @if ($stats['unfilled_rank_count'] > 8)
                                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-white/45">+{{ number_format($stats['unfilled_rank_count'] - 8) }} more</span>
                                    @endif
                                </div>
                            @else
                                <p class="mt-2 text-sm text-white/55">Every tracked rank currently has at least one member.</p>
                            @endif
                        </div>

                        <div class="rounded-[1.25rem] border border-white/10 bg-black/15 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Newest Synced Member</p>
                            @if ($stats['newest_member'])
                                @php($newest = $stats['newest_member']['member'])
                                <p class="mt-2 truncate text-sm font-bold text-white/85">{{ $newest->display_name ?? $newest->username ?? 'Unknown member' }}</p>
                                <p class="mt-1 text-xs text-white/45">Joined Discord {{ $newest->joined_at->diffForHumans() }}</p>
                            @else
                                <p class="mt-2 text-sm text-white/55">Join dates have not been synced yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            @if ($nations->count() > 1 && $canSwitchNationRosters)
                <nav class="mt-5 flex gap-2 overflow-x-auto pb-2" aria-label="Nation rosters">
                    @foreach ($nations as $optionNation)
                        <a
                            href="{{ route('discord-roster.show', $optionNation['key']) }}"
                            class="inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] transition {{ $optionNation['key'] === $nation['key'] ? 'border-white/55 bg-white/10 text-white' : 'border-white/10 bg-white/5 text-white/65 hover:bg-white/10' }}"
                        >
                            <span class="h-2.5 w-2.5 rounded-full border border-white/15" style="background-color: {{ $optionNation['color'] }}"></span>
                            {{ $optionNation['label'] }}
                        </a>
                    @endforeach
                </nav>
            @endif

            <div class="mt-6 space-y-5 sm:space-y-6">
                @forelse ($nation['rank_groups'] as $rankGroup)
                    @php($rank = $rankGroup['rank'])
                    <section class="overflow-hidden rounded-[1.5rem] border border-white/10 bg-[#111317] shadow-xl shadow-black/20">
                        <div class="border-b border-white/10 p-4 sm:p-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($rankGroup['badge_file'])
                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center p-1 sm:h-14 sm:w-14">
                                            <img src="{{ asset('images/military_rankings/'.$rankGroup['badge_file']) }}" alt="{{ $rankGroup['label'] }} insignia" class="max-h-full max-w-full object-contain" onerror="this.hidden = true">
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="break-words text-lg font-bold uppercase tracking-[0.12em] text-[#e4edf8] sm:text-xl">{{ $rankGroup['label'] }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            @if ($rankGroup['is_nation_leadership'])
                                                <span class="inline-flex rounded-md border border-white/15 bg-white/10 px-2 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/85">Nation Leadership</span>
                                            @endif
                                            @if ($rank)
                                                <span class="text-xs text-white/45">Discord role position {{ $rank->position }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-white/55">{{ number_format($rankGroup['members']->count()) }} members</p>
                            </div>
                        </div>

                        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5 xl:grid-cols-3">
                            @foreach ($rankGroup['members'] as $entry)
                                @php($member = $entry['member'])
                                <article class="flex min-w-0 items-center gap-3 rounded-[1.25rem] border border-white/10 bg-black/15 p-3 sm:gap-4 sm:p-4">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#181b20] text-lg font-bold text-[#f4ecd0] shadow-lg shadow-black/20 sm:h-16 sm:w-16">
                                        @if ($member->avatar_url)
                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->display_name ?? $member->username ?? 'Discord member' }} avatar" class="h-full w-full object-cover">
                                        @else
                                            {{ Str::upper(Str::substr($member->display_name ?? $member->username ?? '?', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="break-words text-base font-bold leading-tight text-white">{{ $member->display_name ?? $member->username ?? 'Unknown member' }}</p>
                                        <p class="mt-1 text-sm text-white/45">{{ $rankGroup['label'] }}</p>
                                        <p class="mt-2 break-words text-xs text-white/75"><span class="font-bold text-white">Discord:</span> {{ $member->username ?? $member->discord_user_id }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <section class="rounded-[1.5rem] border border-white/10 bg-white/5 px-5 py-10 text-center text-sm text-white/55">
                        No synced personnel were found for this nation.
                    </section>
                @endforelse
            </div>
        </main>
    </body>
</html>
