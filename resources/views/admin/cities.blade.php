<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Cities</p></x-slot>

    @include('admin.partials.nav')

    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <form method="POST" action="{{ route('admin.cities.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <div class="grid gap-4">
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id" required>
                    @foreach ($factions as $faction)
                        <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                    @endforeach
                </select>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <textarea class="min-h-32 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Description" required></textarea>
                <div class="grid grid-cols-2 gap-4">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" placeholder="Map X %" value="50" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" placeholder="Map Y %" value="50" required>
                </div>
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create City</button>
            </div>
        </form>
        <div class="space-y-4">
            @foreach ($cities as $city)
                <form method="POST" action="{{ route('admin.cities.update', $city) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4">
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id" required>
                            @foreach ($factions as $faction)
                                <option value="{{ $faction->id }}" @selected($city->faction_id === $faction->id)>{{ $faction->name }}</option>
                            @endforeach
                        </select>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $city->name }}" required>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $city->slug }}" required>
                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" required>{{ $city->description }}</textarea>
                        <div class="grid grid-cols-2 gap-4">
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" value="{{ $city->map_x }}" required>
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" value="{{ $city->map_y }}" required>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-4 text-xs uppercase tracking-[0.22em] text-white/45">
                        <span>{{ $city->faction->name }} • {{ $city->map_x }}%, {{ $city->map_y }}%</span>
                        <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Update</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>
