<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Land</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage your personal plot, place buildings, and wait for construction to finish before use.</p>
        </div>
    </x-slot>

    @php($completedBuildings = $character->completedLandBuildings())
    @php($activeBuildings = $character->landBuildings)

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <section class="space-y-6">
            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top,rgba(126,173,89,0.12),rgba(255,255,255,0.02)_45%,rgba(255,255,255,0.03)_100%)] p-6 shadow-2xl shadow-black/30">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Personal Plot</p>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/70">
                            Land ownership unlocks this page. Buy building items in the store, place them on your 10x10 plot, and wait for construction to complete before they become usable.
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-black/25 px-4 py-3 text-right">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Completed Buildings</p>
                        <p class="font-['Teko'] text-4xl uppercase text-[#d7edc7]">{{ $completedBuildings->count() }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/25 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Grid Size</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">10 x 10</p>
                        <p class="mt-2 text-sm text-white/60">One hundred buildable cells for tents and future structures.</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/25 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Faction Zone</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">{{ $character->faction->name }}</p>
                        <p class="mt-2 text-sm text-white/60">Your land remains linked to your current character and faction context.</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/25 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Buildings In Progress</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">{{ $activeBuildings->count() - $completedBuildings->count() }}</p>
                        <p class="mt-2 text-sm text-white/60">Construction timers must finish before the placed structure can be used.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Recovery</p>
                        <p class="mt-2 text-sm text-white/60">Sleeping requires at least one completed building on your land.</p>
                    </div>
                    <form method="POST" action="{{ route('home.sleep') }}">
                        @csrf
                        <button class="amow-action-button rounded-full px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] {{ $character->stamina_points >= 100 || $completedBuildings->isEmpty() ? 'cursor-not-allowed border border-white/10 bg-white/5 text-white/35' : 'border border-[#7ead59]/35 bg-[#7ead59]/12 text-[#d7edc7]' }}" @disabled($character->stamina_points >= 100 || $completedBuildings->isEmpty())>Sleep</button>
                    </form>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Health</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $character->health_points }}/100</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Stamina</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $character->stamina_points }}/100</p>
                        <p class="mt-2 text-xs uppercase tracking-[0.18em] text-[#7ead59]">{{ $completedBuildings->isEmpty() ? 'Complete a building to sleep here' : 'Sleep restores stamina to full' }}</p>
                        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-white/10">
                            <div class="amow-progress-fill h-full rounded-full bg-[linear-gradient(90deg,#7ead59_0%,#b7d680_100%)]" style="width: {{ max(0, min(100, $character->stamina_points)) }}%;"></div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Armor</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $character->armor_points }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Credits</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ number_format($character->plastic_credits) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Building Inventory</p>
                        <p class="mt-2 text-sm text-white/60">Owned building items can be placed onto your land grid from here.</p>
                    </div>
                    <a href="{{ route('store.index') }}" class="rounded-full border border-white/10 bg-black/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Open Store</a>
                </div>
                <div class="mt-5 space-y-4">
                    @forelse ($buildingItems as $item)
                        <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-black/20 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-[#f4ecd0]">{{ $item->name }}</p>
                                    <p class="mt-2 text-sm leading-6 text-white/65">{{ $item->description }}</p>
                                    <p class="mt-3 text-xs uppercase tracking-[0.2em] text-[#7ead59]">Owned: {{ $item->pivot->quantity }} | Footprint: {{ $item->footprint_width }}x{{ $item->footprint_height }} | Build: {{ $item->build_time_minutes }} min</p>
                                </div>
                                <form method="POST" action="{{ route('home.buildings.place') }}" class="grid gap-3 rounded-2xl border border-white/10 bg-black/25 p-4 sm:grid-cols-2">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="grid_x">
                                        @foreach (range(1, 10) as $x)
                                            <option value="{{ $x }}">X {{ $x }}</option>
                                        @endforeach
                                    </select>
                                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="grid_y">
                                        @foreach (range(1, 10) as $y)
                                            <option value="{{ $y }}">Y {{ $y }}</option>
                                        @endforeach
                                    </select>
                                    <div class="sm:col-span-2 flex justify-end">
                                        <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Place Building</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No building items owned yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Land Grid</p>
                        <p class="mt-2 text-sm text-white/60">Cells marked amber are still building. Green cells are complete.</p>
                    </div>
                    <span class="rounded-full border border-[#7ead59]/30 bg-[#7ead59]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">{{ $activeBuildings->count() }} placed</span>
                </div>

                <div class="mt-5">
                    <div class="mx-auto w-full max-w-[60rem] overflow-hidden rounded-[1.25rem] border border-[#d7edc7]/20 bg-[radial-gradient(circle_at_center,rgba(92,160,120,0.16),rgba(15,24,18,0.96)_70%)] p-4 shadow-inner shadow-black/40">
                        <div
                            class="grid w-full border border-[#d7edc7]/35 bg-[#d7edc7]/20"
                            style="grid-template-columns: repeat(10, minmax(0, 1fr)); grid-template-rows: repeat(10, minmax(0, 1fr)); aspect-ratio: 1 / 1;"
                        >
                        @foreach ($gridRows as $row)
                            @foreach ($row as $cell)
                                <div class="relative flex min-h-0 min-w-0 items-center justify-center border border-[#d7edc7]/30 {{ $cell['status'] === 'complete' ? 'bg-[#274737]/78' : ($cell['status'] === 'building' ? 'bg-[#5a4a23]/78' : 'bg-[#14221a]/88') }}">
                                    <p class="absolute left-1 top-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-[#d7edc7]/55 sm:left-1.5 sm:top-1.5">{{ $cell['x'] }},{{ $cell['y'] }}</p>
                                    @if ($cell['building'])
                                        <div class="flex h-full w-full flex-col items-center justify-center p-2">
                                            @if ($cell['is_anchor'])
                                                @php($buildStartedAt = $cell['building']->build_started_at)
                                                @php($buildCompleteAt = $cell['building']->build_complete_at)
                                                @php($buildDurationSeconds = max(1, (int) $buildStartedAt?->diffInSeconds($buildCompleteAt, true)))
                                                @php($elapsedSeconds = $cell['building']->isComplete() ? $buildDurationSeconds : max(0, min($buildDurationSeconds, (int) $buildStartedAt?->diffInSeconds(now(), true))))
                                                @php($progressPercent = (int) round(($elapsedSeconds / $buildDurationSeconds) * 100))

                                                <div class="flex h-12 w-12 items-center justify-center rounded-lg border border-[#d7edc7]/35 bg-black/20 text-[#f4ecd0] sm:h-16 sm:w-16">
                                                    <i class="{{ $cell['building']->item->display_icon_class }} text-2xl sm:text-3xl"></i>
                                                </div>
                                                <p class="mt-1.5 line-clamp-2 text-center font-['Teko'] text-sm uppercase leading-none text-[#f4ecd0] sm:text-base">{{ $cell['building']->item->name }}</p>
                                                <p class="mt-1 text-[10px] uppercase tracking-[0.16em] {{ $cell['status'] === 'complete' ? 'text-[#d7edc7]' : 'text-[#f4ecd0]' }}">{{ $cell['status'] === 'complete' ? 'Ready' : 'Building' }}</p>
                                                <div class="mt-1.5 w-full max-w-[5.5rem] sm:max-w-[6.5rem]">
                                                    <div class="h-1.5 overflow-hidden rounded-full bg-black/35 sm:h-2">
                                                        <div
                                                            class="h-full rounded-full {{ $cell['status'] === 'complete' ? 'bg-[linear-gradient(90deg,#7ead59_0%,#d7edc7_100%)]' : 'bg-[linear-gradient(90deg,#c2a84f_0%,#f4ecd0_100%)]' }}"
                                                            style="width: {{ max(0, min(100, $progressPercent)) }}%;"
                                                        ></div>
                                                    </div>
                                                    <p class="mt-1 text-center text-[9px] uppercase tracking-[0.14em] text-[#d7edc7]/75">
                                                        {{ $cell['status'] === 'complete' ? '100%' : $progressPercent.'%' }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="pointer-events-none h-full w-full bg-[radial-gradient(circle_at_center,rgba(215,237,199,0.05),transparent_62%)]"></div>
                                        <span class="absolute bottom-1 right-1 h-2.5 w-2.5 rounded-full border border-[#d7edc7]/25 bg-[#d7edc7]/10 sm:h-3 sm:w-3"></span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Construction Queue</p>
                <div class="mt-4 space-y-3">
                    @forelse ($activeBuildings as $building)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $building->item->name }}</p>
                                    <p class="text-sm text-white/70">Placed at {{ $building->grid_x }}, {{ $building->grid_y }} | {{ $building->item->footprint_width }}x{{ $building->item->footprint_height }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-['Teko'] text-2xl uppercase {{ $building->isComplete() ? 'text-[#7ead59]' : 'text-[#c2a84f]' }}">{{ $building->isComplete() ? 'Ready' : 'Building' }}</p>
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">
                                        {{ $building->isComplete() ? 'Completed' : 'Finishes '.$building->build_complete_at?->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No buildings have been placed yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
