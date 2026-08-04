<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Land</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage your personal plot, place buildings, and wait for construction to finish before use.</p>
        </div>
    </x-slot>

    @php($completedBuildings = $character->completedLandBuildings())
    @php($activeBuildings = $character->landBuildings)
    @php($inProgressBuildings = $activeBuildings->reject(fn ($building) => $building->isComplete())->values())

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top,rgba(126,173,89,0.12),rgba(255,255,255,0.02)_45%,rgba(255,255,255,0.03)_100%)] p-6 shadow-2xl shadow-black/30">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Personal Plot</p>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-white/70">
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
        </section>

        <div class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Land Grid</p>
                        <p class="mt-2 text-sm text-white/60">Blocked cells contain salvage obstacles. Clear them to expand your usable building area.</p>
                    </div>
                    <span class="rounded-full border border-[#c2a84f]/30 bg-[#c2a84f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f4ecd0]">Next Clear: {{ number_format($nextTileClearCost) }}</span>
                </div>

                <div class="mt-5">
                    <div class="mx-auto w-full max-w-[88rem] overflow-hidden rounded-[1.25rem] border border-[#d7edc7]/20 bg-[radial-gradient(circle_at_center,rgba(92,160,120,0.16),rgba(15,24,18,0.96)_70%)] p-4 shadow-inner shadow-black/40">
                        <div
                            class="grid w-full border border-[#d7edc7]/35 bg-[#d7edc7]/20"
                            style="grid-template-columns: repeat(10, minmax(0, 1fr)); grid-template-rows: repeat(10, minmax(0, 1fr)); aspect-ratio: 1 / 1;"
                        >
                        @foreach ($gridRows as $row)
                            @foreach ($row as $cell)
                                <div class="relative flex min-h-0 min-w-0 items-center justify-center overflow-hidden border border-[#d7edc7]/30 {{ $cell['status'] === 'complete' ? 'bg-[#274737]/78' : ($cell['status'] === 'building' ? 'bg-[#5a4a23]/78' : ($cell['status'] === 'clearing' ? 'bg-[#4a361f]/90' : ($cell['status'] === 'blocked' ? 'bg-[#2e2d26]/92' : 'bg-[#14221a]/88'))) }}">
                                    <p class="absolute left-1 top-1 text-[9px] font-semibold uppercase tracking-[0.12em] text-[#d7edc7]/55 sm:left-1.5 sm:top-1.5">{{ $cell['x'] }},{{ $cell['y'] }}</p>
                                    @if ($cell['building'])
                                        <div class="flex h-full w-full flex-col items-center justify-center p-2 pt-5 sm:pt-6">
                                            @if ($cell['is_anchor'])
                                                @php($buildStartedAt = $cell['building']->build_started_at)
                                                @php($buildCompleteAt = $cell['building']->build_complete_at)
                                                @php($buildDurationSeconds = max(1, (int) $buildStartedAt?->diffInSeconds($buildCompleteAt, true)))
                                                @php($elapsedSeconds = $cell['building']->isComplete() ? $buildDurationSeconds : max(0, min($buildDurationSeconds, (int) $buildStartedAt?->diffInSeconds(now(), true))))
                                                @php($progressPercent = (int) round(($elapsedSeconds / $buildDurationSeconds) * 100))
                                                @php($remainingSeconds = $cell['building']->isComplete() ? 0 : max(0, (int) now()->diffInSeconds($buildCompleteAt, false)))
                                                @php($remainingHours = intdiv($remainingSeconds, 3600))
                                                @php($remainingMinutes = intdiv($remainingSeconds % 3600, 60))
                                                @php($remainingFormatted = sprintf('%02d:%02d:%02d', $remainingHours, $remainingMinutes, $remainingSeconds % 60))
                                                @php($progressBarStyle = match (true) {
                                                    $progressPercent >= 100 => 'linear-gradient(90deg,#7ead59 0%,#d7edc7 100%)',
                                                    $progressPercent >= 75 => 'linear-gradient(90deg,#8fbe63 0%,#d7edc7 100%)',
                                                    $progressPercent >= 40 => 'linear-gradient(90deg,#c2a84f 0%,#f4ecd0 100%)',
                                                    default => 'linear-gradient(90deg,#c65b3f 0%,#f0b29f 100%)',
                                                })
                                                @if ($cell['building']->isComplete())
                                                    <button
                                                        type="button"
                                                        onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'building-{{ $cell['building']->id }}' }))"
                                                        class="flex h-full w-full flex-col items-center justify-center px-2 py-2 text-center transition hover:bg-black/10"
                                                    >
                                                        <i class="{{ $cell['building']->item->display_icon_class }} text-[1.8rem] leading-none text-[#f4ecd0] sm:text-[2.15rem]"></i>
                                                        <p class="mt-1.5 line-clamp-2 text-center font-['Teko'] text-[0.8rem] uppercase leading-none text-[#f4ecd0] sm:text-[0.95rem]">{{ $cell['building']->item->name }}</p>
                                                    </button>
                                                @else
                                                    <div class="flex h-full w-full flex-col items-center justify-center px-2 py-2 text-center">
                                                        <i class="{{ $cell['building']->item->display_icon_class }} text-[1.8rem] leading-none text-[#f4ecd0] sm:text-[2.15rem]"></i>
                                                        <p class="mt-1 line-clamp-2 text-center font-['Teko'] text-[0.8rem] uppercase leading-none text-[#f4ecd0] sm:text-[0.95rem]">{{ $cell['building']->item->name }}</p>
                                                        <p class="mt-1 text-[9px] uppercase tracking-[0.14em] text-[#f4ecd0]" data-build-status>Building</p>
                                                        <div
                                                            class="mt-1 w-full max-w-[4.25rem] sm:max-w-[5rem]"
                                                            data-construction-timer
                                                            data-build-start="{{ $buildStartedAt?->toIso8601String() }}"
                                                            data-build-complete="{{ $buildCompleteAt?->toIso8601String() }}"
                                                        >
                                                            <div class="h-1 overflow-hidden rounded-full bg-black/35 sm:h-1.5">
                                                                <div
                                                                    class="block h-full rounded-full"
                                                                    data-build-progress-fill
                                                                    style="width: {{ max(0, min(100, $progressPercent)) }}%; background-color: #c2a84f; background-image: {{ $progressBarStyle }};"
                                                                ></div>
                                                            </div>
                                                            <p class="mt-0.5 text-center text-[8px] uppercase tracking-[0.12em] text-[#d7edc7]/75" data-build-progress-percent>
                                                                {{ $progressPercent.'%' }}
                                                            </p>
                                                            <p class="mt-0.5 text-center text-[8px] uppercase tracking-[0.12em] text-[#f4ecd0]" data-build-progress-remaining>
                                                                {{ $remainingFormatted }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @elseif ($cell['status'] === 'clearing')
                                        @php($clearStartedAt = $cell['clear_started_at'])
                                        @php($clearCompleteAt = $cell['clear_complete_at'])
                                        @php($clearDurationSeconds = max(1, (int) $clearStartedAt?->diffInSeconds($clearCompleteAt, true)))
                                        @php($clearElapsedSeconds = max(0, min($clearDurationSeconds, (int) $clearStartedAt?->diffInSeconds(now(), true))))
                                        @php($clearProgressPercent = (int) round(($clearElapsedSeconds / $clearDurationSeconds) * 100))
                                        @php($clearRemainingSeconds = max(0, (int) now()->diffInSeconds($clearCompleteAt, false)))
                                        @php($clearRemainingHours = intdiv($clearRemainingSeconds, 3600))
                                        @php($clearRemainingMinutes = intdiv($clearRemainingSeconds % 3600, 60))
                                        @php($clearRemainingFormatted = sprintf('%02d:%02d:%02d', $clearRemainingHours, $clearRemainingMinutes, $clearRemainingSeconds % 60))
                                        <div class="flex h-full w-full flex-col items-center justify-center px-2 py-2 pt-5 text-center">
                                            <i class="fa-solid fa-hammer text-xl text-[#f0b29f] sm:text-2xl"></i>
                                            <p class="mt-1 font-['Teko'] text-[0.72rem] uppercase leading-none text-[#f4ecd0] sm:text-[0.82rem]">Clearing</p>
                                            <div
                                                class="mt-1 w-full max-w-[4.25rem] sm:max-w-[5rem]"
                                                data-construction-timer
                                                data-build-start="{{ $clearStartedAt?->toIso8601String() }}"
                                                data-build-complete="{{ $clearCompleteAt?->toIso8601String() }}"
                                            >
                                                <div class="h-1 overflow-hidden rounded-full bg-black/35 sm:h-1.5">
                                                    <div
                                                        class="block h-full rounded-full"
                                                        data-build-progress-fill
                                                        style="width: {{ max(0, min(100, $clearProgressPercent)) }}%; background-color: #c65b3f; background-image: linear-gradient(90deg,#c65b3f 0%,#f0b29f 100%);"
                                                    ></div>
                                                </div>
                                                <p class="mt-0.5 text-center text-[8px] uppercase tracking-[0.12em] text-[#d7edc7]/75" data-build-progress-percent>{{ $clearProgressPercent }}%</p>
                                                <p class="mt-0.5 text-center text-[8px] uppercase tracking-[0.12em] text-[#f4ecd0]" data-build-progress-remaining>{{ $clearRemainingFormatted }}</p>
                                            </div>
                                        </div>
                                    @elseif ($cell['status'] === 'blocked')
                                        @php($obstacleIcon = match ($cell['obstacle_type']) {
                                            'rock' => 'fa-solid fa-mountain',
                                            'scrap' => 'fa-solid fa-gears',
                                            'debris' => 'fa-solid fa-industry',
                                            'roots' => 'fa-solid fa-seedling',
                                            default => 'fa-solid fa-triangle-exclamation',
                                        })
                                        <div class="flex h-full w-full flex-col items-center justify-center px-2 py-2 pt-5 text-center">
                                            <i class="{{ $obstacleIcon }} text-xl text-[#c2a84f] sm:text-2xl"></i>
                                            <p class="mt-1 font-['Teko'] text-[0.72rem] uppercase leading-none text-[#f4ecd0] sm:text-[0.82rem]">{{ $cell['obstacle_type'] }}</p>
                                            <p class="mt-1 text-[8px] uppercase tracking-[0.12em] text-white/55">{{ number_format($cell['clear_cost']) }} credits</p>
                                            <form method="POST" action="{{ route('home.tiles.clear') }}" class="mt-1.5">
                                                @csrf
                                                <input type="hidden" name="grid_x" value="{{ $cell['x'] }}">
                                                <input type="hidden" name="grid_y" value="{{ $cell['y'] }}">
                                                <button class="rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/12 px-2.5 py-1 text-[8px] font-semibold uppercase tracking-[0.14em] text-[#f4ecd0]">
                                                    Salvage
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <button
                                            type="button"
                                            onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'empty-tile-{{ $cell['x'] }}-{{ $cell['y'] }}' }))"
                                            class="flex h-full w-full items-center justify-center transition hover:bg-black/10"
                                        >
                                            <span class="absolute bottom-1 right-1 h-2.5 w-2.5 rounded-full border border-[#d7edc7]/25 bg-[#d7edc7]/10 sm:h-3 sm:w-3"></span>
                                        </button>
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
                    @forelse ($inProgressBuildings as $building)
                        @php($buildStartedAt = $building->build_started_at)
                        @php($buildCompleteAt = $building->build_complete_at)
                        @php($buildDurationSeconds = max(1, (int) $buildStartedAt?->diffInSeconds($buildCompleteAt, true)))
                        @php($elapsedSeconds = $building->isComplete() ? $buildDurationSeconds : max(0, min($buildDurationSeconds, (int) $buildStartedAt?->diffInSeconds(now(), true))))
                        @php($progressPercent = (int) round(($elapsedSeconds / $buildDurationSeconds) * 100))
                        @php($remainingSeconds = $building->isComplete() ? 0 : max(0, (int) now()->diffInSeconds($buildCompleteAt, false)))
                        @php($remainingHours = intdiv($remainingSeconds, 3600))
                        @php($remainingMinutes = intdiv($remainingSeconds % 3600, 60))
                        @php($remainingFormatted = sprintf('%02d:%02d:%02d', $remainingHours, $remainingMinutes, $remainingSeconds % 60))
                        @php($progressBarStyle = match (true) {
                            $progressPercent >= 100 => 'linear-gradient(90deg,#7ead59 0%,#d7edc7 100%)',
                            $progressPercent >= 75 => 'linear-gradient(90deg,#8fbe63 0%,#d7edc7 100%)',
                            $progressPercent >= 40 => 'linear-gradient(90deg,#c2a84f 0%,#f4ecd0 100%)',
                            default => 'linear-gradient(90deg,#c65b3f 0%,#f0b29f 100%)',
                        })
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $building->item->name }}</p>
                                    <p class="text-sm text-white/70">Placed at {{ $building->grid_x }}, {{ $building->grid_y }} | {{ $building->item->footprint_width }}x{{ $building->item->footprint_height }}</p>
                                    <div
                                        class="mt-3 max-w-md"
                                        data-construction-timer
                                        data-build-start="{{ $buildStartedAt?->toIso8601String() }}"
                                        data-build-complete="{{ $buildCompleteAt?->toIso8601String() }}"
                                    >
                                        <div class="h-2.5 overflow-hidden rounded-full bg-white/10">
                                            <div class="block h-full rounded-full" data-build-progress-fill style="width: {{ max(0, min(100, $progressPercent)) }}%; background-color: #c2a84f; background-image: {{ $progressBarStyle }};"></div>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between gap-4 text-[11px] uppercase tracking-[0.16em]">
                                            <span class="text-[#d7edc7]/75" data-build-progress-percent>{{ $progressPercent }}%</span>
                                            <span class="{{ $building->isComplete() ? 'text-[#d7edc7]' : 'text-[#f4ecd0]' }}" data-build-progress-remaining>{{ $building->isComplete() ? '00:00:00' : $remainingFormatted }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-['Teko'] text-2xl uppercase text-[#c2a84f]" data-build-status>Building</p>
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">
                                        Time Remaining
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No buildings are currently being constructed.</p>
                    @endforelse
                </div>
            </div>

            @if ($activeClearingTiles->isNotEmpty())
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Salvage Queue</p>
                    <div class="mt-4 space-y-3">
                        @foreach ($activeClearingTiles as $tile)
                            @php($clearStartedAt = $tile->clear_started_at)
                            @php($clearCompleteAt = $tile->clear_complete_at)
                            @php($clearDurationSeconds = max(1, (int) $clearStartedAt?->diffInSeconds($clearCompleteAt, true)))
                            @php($clearElapsedSeconds = max(0, min($clearDurationSeconds, (int) $clearStartedAt?->diffInSeconds(now(), true))))
                            @php($clearProgressPercent = (int) round(($clearElapsedSeconds / $clearDurationSeconds) * 100))
                            @php($clearRemainingSeconds = max(0, (int) now()->diffInSeconds($clearCompleteAt, false)))
                            @php($clearRemainingHours = intdiv($clearRemainingSeconds, 3600))
                            @php($clearRemainingMinutes = intdiv($clearRemainingSeconds % 3600, 60))
                            @php($clearRemainingFormatted = sprintf('%02d:%02d:%02d', $clearRemainingHours, $clearRemainingMinutes, $clearRemainingSeconds % 60))
                            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">Clearing {{ $tile->obstacle_type }}</p>
                                        <p class="text-sm text-white/70">Tile {{ $tile->grid_x }}, {{ $tile->grid_y }}</p>
                                        <div class="mt-3 max-w-md" data-construction-timer data-build-start="{{ $clearStartedAt?->toIso8601String() }}" data-build-complete="{{ $clearCompleteAt?->toIso8601String() }}">
                                            <div class="h-2.5 overflow-hidden rounded-full bg-white/10">
                                                <div class="block h-full rounded-full" data-build-progress-fill style="width: {{ max(0, min(100, $clearProgressPercent)) }}%; background-color: #c65b3f; background-image: linear-gradient(90deg,#c65b3f 0%,#f0b29f 100%);"></div>
                                            </div>
                                            <div class="mt-2 flex items-center justify-between gap-4 text-[11px] uppercase tracking-[0.16em]">
                                                <span class="text-[#d7edc7]/75" data-build-progress-percent>{{ $clearProgressPercent }}%</span>
                                                <span class="text-[#f4ecd0]" data-build-progress-remaining>{{ $clearRemainingFormatted }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-['Teko'] text-2xl uppercase text-[#f0b29f]" data-build-status>Clearing</p>
                                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Time Remaining</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @foreach ($completedBuildings as $building)
        <x-modal name="building-{{ $building->id }}" maxWidth="lg">
            <div class="border border-white/10 bg-[linear-gradient(180deg,rgba(16,29,21,0.98),rgba(7,12,9,0.98))] p-6 text-[#f4ecd0]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ $building->item->name }}</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.18em] text-white/45">Placed at {{ $building->grid_x }}, {{ $building->grid_y }}</p>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'building-{{ $building->id }}')" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.18em] text-white/60">Close</button>
                </div>

                <div class="mt-5 rounded-[1.4rem] border border-white/10 bg-black/20 p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl border border-[#d7edc7]/35 bg-black/20 text-[#f4ecd0]">
                            <i class="{{ $building->item->display_icon_class }} text-3xl"></i>
                        </div>
                        <div>
                            <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $building->item->name }}</p>
                            <p class="text-sm text-white/65">{{ $building->item->description }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">Available Actions</p>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @if (str_contains($building->item->slug, 'tent'))
                            <form method="POST" action="{{ route('home.sleep') }}">
                                @csrf
                                <button class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]" @disabled($character->stamina_points >= 100)>
                                    Sleep
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('home.buildings.destroy', $building) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">
                                Remove Building
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-5 border-t border-white/10 pt-5">
                    <p class="text-xs uppercase tracking-[0.2em] text-white/45">Move Building</p>
                    <p class="mt-2 text-sm text-white/60">Moving the building restarts its construction timer.</p>
                    <form method="POST" action="{{ route('home.buildings.move', $building) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @csrf
                        @method('PATCH')
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="grid_x">
                            @foreach (range(1, 10) as $x)
                                <option value="{{ $x }}" @selected($building->grid_x === $x)>X {{ $x }}</option>
                            @endforeach
                        </select>
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="grid_y">
                            @foreach (range(1, 10) as $y)
                                <option value="{{ $y }}" @selected($building->grid_y === $y)>Y {{ $y }}</option>
                            @endforeach
                        </select>
                        <div class="sm:col-span-2 flex justify-end">
                            <button class="rounded-full border border-[#d7edc7]/30 bg-[#d7edc7]/10 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#f4ecd0]">
                                Move And Rebuild
                            </button>
                        </div>
                    </form>
                </div>

                @if (! str_contains($building->item->slug, 'tent'))
                    <div class="mt-5 rounded-full border border-white/10 bg-white/5 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white/45 text-center">
                        No special building actions yet
                    </div>
                @endif
            </div>
        </x-modal>
    @endforeach

    @for ($y = 1; $y <= 10; $y++)
        @for ($x = 1; $x <= 10; $x++)
            <x-modal name="empty-tile-{{ $x }}-{{ $y }}" maxWidth="lg">
                <div class="border border-white/10 bg-[linear-gradient(180deg,rgba(16,29,21,0.98),rgba(7,12,9,0.98))] p-6 text-[#f4ecd0]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em]">Tile {{ $x }}, {{ $y }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-white/45">Place a building on this open plot.</p>
                        </div>
                        <button type="button" x-on:click="$dispatch('close-modal', 'empty-tile-{{ $x }}-{{ $y }}')" class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.18em] text-white/60">Close</button>
                    </div>

                    <div class="mt-5">
                        @if ($buildingItems->isNotEmpty())
                            <div class="space-y-4">
                                @foreach ($buildingItems as $item)
                                    <form method="POST" action="{{ route('home.buildings.place') }}" class="rounded-[1.4rem] border border-white/10 bg-black/20 p-4">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        <input type="hidden" name="grid_x" value="{{ $x }}">
                                        <input type="hidden" name="grid_y" value="{{ $y }}">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                                                <p class="text-sm text-white/65">{{ $item->description }}</p>
                                                <p class="mt-2 text-xs uppercase tracking-[0.18em] text-[#d7edc7]">Owned: {{ $item->pivot->quantity }} | {{ $item->footprint_width }}x{{ $item->footprint_height }} | {{ $item->build_time_minutes }} min build</p>
                                            </div>
                                            <button class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">
                                                Place
                                            </button>
                                        </div>
                                    </form>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-[1.4rem] border border-white/10 bg-black/20 p-5">
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">No Building Items</p>
                                <p class="mt-2 text-sm text-white/65">You do not currently own any buildings to place on this tile.</p>
                                <a href="{{ route('store.index') }}" class="mt-4 inline-flex rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Go To Marketplace</a>
                            </div>
                        @endif
                    </div>
                </div>
            </x-modal>
        @endfor
    @endfor

</x-app-layout>
