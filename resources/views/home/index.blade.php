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
                    <div class="mx-auto w-full max-w-[42rem] overflow-hidden rounded-[1.5rem] border border-white/10 bg-[#0a0f0c] shadow-inner shadow-black/40">
                        <div
                            class="grid w-full gap-px bg-white/10"
                            style="grid-template-columns: repeat(10, minmax(0, 1fr)); aspect-ratio: 1 / 1;"
                        >
                        @foreach ($gridRows as $row)
                            @foreach ($row as $cell)
                                <div class="flex min-h-0 min-w-0 flex-col p-1.5 text-center {{ $cell['status'] === 'complete' ? 'bg-[#1f3a22]' : ($cell['status'] === 'building' ? 'bg-[#4a3b19]' : 'bg-[#101713]') }}">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-white/35">{{ $cell['x'] }},{{ $cell['y'] }}</p>
                                    @if ($cell['building'])
                                        <div class="mt-1.5 flex min-h-0 flex-1 flex-col justify-between">
                                            <p class="font-['Teko'] text-sm uppercase leading-none text-[#f4ecd0] sm:text-base">{{ $cell['is_anchor'] ? $cell['building']->item->name : '' }}</p>
                                            <p class="text-[10px] uppercase tracking-[0.16em] {{ $cell['status'] === 'complete' ? 'text-[#d7edc7]' : 'text-[#f4ecd0]' }}">{{ $cell['status'] === 'complete' ? 'Ready' : 'Building' }}</p>
                                        </div>
                                    @else
                                        <div class="mt-1.5 flex min-h-0 flex-1 items-center justify-center text-white/15">
                                            <i class="fa-regular fa-square"></i>
                                        </div>
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
