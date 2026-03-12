<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Map Markers</p></x-slot>

    @include('admin.partials.nav')

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
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" placeholder="Map X %" value="50" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" placeholder="Map Y %" value="50" required>
                </div>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="color" placeholder="Hex color, e.g. #d94a3a">
                <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Marker description"></textarea>
                <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4 text-sm text-white/65">
                    Suggested icons: `fa-solid fa-flag`, `fa-solid fa-industry`, `fa-solid fa-coins`, `fa-solid fa-skull-crossbones`, `fa-solid fa-landmark`.
                </div>
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Marker</button>
            </div>
        </form>

        <div class="space-y-4">
            @foreach ($markers as $marker)
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <form method="POST" action="{{ route('admin.map-markers.update', $marker) }}" class="grid gap-4">
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
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3 text-sm text-white/60">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/20" style="color: {{ $marker->color ?: '#c2a84f' }};">
                                    <i class="{{ $marker->icon_class }}"></i>
                                </span>
                                <span>{{ $marker->faction?->name ?? 'All factions' }}</span>
                            </div>
                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Update</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.map-markers.destroy', $marker) }}" class="mt-3 flex justify-end">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-full bg-[#c65b3f] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-white">Delete</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
