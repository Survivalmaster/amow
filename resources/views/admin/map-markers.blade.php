<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Map Markers</p></x-slot>

    @include('admin.partials.nav')

    <div
        x-data="{
            x: 50,
            y: 50,
            setCoords(event) {
                const rect = event.currentTarget.getBoundingClientRect();
                this.x = Math.max(0, Math.min(100, Math.round(((event.clientX - rect.left) / rect.width) * 100)));
                this.y = Math.max(0, Math.min(100, Math.round(((event.clientY - rect.top) / rect.height) * 100)));
            }
        }"
        class="space-y-6"
    >
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Place Markers on the Map</p>
                    <p class="mt-2 text-sm leading-7 text-white/70">Click anywhere on the map to set the coordinates for the marker form below.</p>
                </div>
                <div class="text-xs uppercase tracking-[0.24em] text-white/45">Click to capture X/Y</div>
            </div>
            <div
                @click="setCoords($event)"
                class="relative mt-5 cursor-crosshair overflow-hidden rounded-[2rem] border border-white/10 bg-black/20"
            >
                <img src="{{ asset('images/plastica_map.jpg') }}" alt="Map of Plastica" class="block w-full">
                @foreach ($markers as $marker)
                    <button
                        type="button"
                        class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full border border-white/10 bg-black/80 p-2 shadow-lg shadow-black/40"
                        style="left: {{ $marker->map_x }}%; top: {{ $marker->map_y }}%; color: {{ $marker->color ?: '#c2a84f' }};"
                        title="{{ $marker->name }}"
                        @click.stop="x = {{ (int) $marker->map_x }}; y = {{ (int) $marker->map_y }}"
                    >
                        <i class="{{ $marker->icon_class }} text-sm"></i>
                    </button>
                @endforeach
                <div
                    class="pointer-events-none absolute -translate-x-1/2 -translate-y-1/2"
                    :style="`left: ${x}%; top: ${y}%;`"
                >
                    <div class="flex h-6 w-6 items-center justify-center rounded-full border border-[#7ead59]/40 bg-[#07100c]/90 text-[#7ead59] shadow-lg shadow-black/40">
                        <i class="fa-solid fa-crosshairs text-[10px]"></i>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
            <form method="POST" action="{{ route('admin.map-markers.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <div class="grid gap-4">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Marker name" required>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                        <option value="">Visible to all factions</option>
                        @foreach ($factions as $faction)
                            <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                        @endforeach
                    </select>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" placeholder="Font Awesome class, e.g. fa-solid fa-tower-observation" required>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs uppercase tracking-[0.2em] text-white/50">Map X %</label>
                            <input x-model="x" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs uppercase tracking-[0.2em] text-white/50">Map Y %</label>
                            <input x-model="y" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" required>
                        </div>
                    </div>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="color" placeholder="Hex color, e.g. #d94a3a">
                    <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Marker description"></textarea>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4 text-sm text-white/65">
                        Click the map above to fill X/Y. Suggested icons: `fa-solid fa-flag`, `fa-solid fa-industry`, `fa-solid fa-coins`, `fa-solid fa-skull-crossbones`, `fa-solid fa-landmark`.
                    </div>
                    <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Marker</button>
                </div>
            </form>

            <div x-data="{ openId: null }" class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-white/75">
                        <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                            <tr>
                                <th class="px-5 py-4 text-left">Name</th>
                                <th class="px-5 py-4 text-left">Faction</th>
                                <th class="px-5 py-4 text-left">Coords</th>
                                <th class="px-5 py-4 text-left">Icon</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach ($markers as $marker)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-white">{{ $marker->name }}</td>
                                    <td class="px-5 py-4">{{ $marker->faction?->name ?? 'All factions' }}</td>
                                    <td class="px-5 py-4">{{ $marker->map_x }}%, {{ $marker->map_y }}%</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/20" style="color: {{ $marker->color ?: '#c2a84f' }};">
                                            <i class="{{ $marker->icon_class }}"></i>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="openId = openId === {{ $marker->id }} ? null : {{ $marker->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                            <form method="POST" action="{{ route('admin.map-markers.destroy', $marker) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr x-show="openId === {{ $marker->id }}" x-cloak>
                                    <td colspan="5" class="px-5 pb-5">
                                        <form method="POST" action="{{ route('admin.map-markers.update', $marker) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5">
                                            @csrf
                                            @method('PATCH')
                                            <div class="grid gap-4 xl:grid-cols-[1fr_220px]">
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $marker->name }}" required>
                                                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                                                    <option value="">Visible to all factions</option>
                                                    @foreach ($factions as $faction)
                                                        <option value="{{ $faction->id }}" @selected($marker->faction_id === $faction->id)>{{ $faction->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="grid gap-4 xl:grid-cols-[1fr_120px_120px_160px]">
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" value="{{ $marker->icon_class }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" value="{{ $marker->map_x }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" value="{{ $marker->map_y }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="color" value="{{ $marker->color }}" placeholder="#7ead59">
                                            </div>
                                            <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description">{{ $marker->description }}</textarea>
                                            <div class="flex justify-end">
                                                <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
