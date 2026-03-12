<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Leaderboards</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Wealth, rank, influence, military, and economy standings.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-3">
        @foreach ([
            'Wealth' => $wealth,
            'Rank' => $rankings,
            'Influence' => $influence,
            'Military' => $military,
            'Economy' => $economy,
        ] as $title => $entries)
            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">{{ $title }}</p>
                <div class="mt-5 space-y-3">
                    @foreach ($entries as $index => $entry)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $index + 1 }}. {{ $entry->name }}</p>
                                    <p class="text-xs uppercase tracking-[0.22em] text-white/45">{{ $entry->faction->name }} • {{ $entry->rank->name }}</p>
                                </div>
                                <p class="font-['Teko'] text-3xl uppercase text-[#7ead59]">
                                    @if ($title === 'Wealth')
                                        {{ number_format($entry->plastic_credits) }}
                                    @elseif ($title === 'Influence')
                                        {{ number_format($entry->influence_score) }}
                                    @elseif ($title === 'Military')
                                        {{ number_format($entry->military_score) }}
                                    @elseif ($title === 'Economy')
                                        {{ number_format($entry->economic_score) }}
                                    @else
                                        {{ $entry->rank->name }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
