<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Cities</p></x-slot>

    @include('admin.partials.nav')

    <div
        x-data="{
            openId: null,
            showCreate: false,
            query: '',
            filterRows() {
                if (!this.$refs.rows) return;
                [...this.$refs.rows.querySelectorAll('[data-admin-row]')].forEach((row) => row.toggleAttribute('hidden', this.query && !row.dataset.search.includes(this.query.toLowerCase())));
            }
        }"
        x-effect="filterRows()"
        class="space-y-5"
    >
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <label class="space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45 lg:min-w-[28rem]">
                    <span>Search</span>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                        <input x-model.debounce.150ms="query" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, faction, slug">
                    </div>
                </label>
                <button type="button" @click="showCreate = !showCreate" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                    <i class="fa-solid" :class="showCreate ? 'fa-minus' : 'fa-plus'"></i>
                    <span x-text="showCreate ? 'Close Create' : 'Create City'"></span>
                </button>
            </div>
        </section>

        <form x-show="showCreate" x-cloak method="POST" action="{{ route('admin.cities.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create City</p>
            <p class="mt-2 text-sm text-white/60">Creates a city on the Plastica map and assigns it to the faction that controls it.</p>
            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id" required>
                    @foreach ($factions as $faction)
                        <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                    @endforeach
                </select>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <div class="grid grid-cols-2 gap-4">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" placeholder="Map X %" value="50" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" placeholder="Map Y %" value="50" required>
                </div>
                <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="description" placeholder="Description" required></textarea>
            </div>
            <div class="mt-5 flex justify-end">
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create City</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Faction</th>
                            <th class="px-5 py-4 text-left">Slug</th>
                            <th class="px-5 py-4 text-left">Map</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @foreach ($cities as $city)
                            <tr data-admin-row data-search="{{ str($city->name.' '.$city->faction->name.' '.$city->slug.' '.$city->description)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $city->name }}</td>
                                <td class="px-5 py-4">{{ $city->faction->name }}</td>
                                <td class="px-5 py-4">{{ $city->slug }}</td>
                                <td class="px-5 py-4">{{ $city->map_x }}%, {{ $city->map_y }}%</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $city->id }} ? null : {{ $city->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.cities.destroy', $city) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $city->id }}" x-cloak>
                                <td colspan="5" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.cities.update', $city) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id" required>
                                            @foreach ($factions as $faction)
                                                <option value="{{ $faction->id }}" @selected($city->faction_id === $faction->id)>{{ $faction->name }}</option>
                                            @endforeach
                                        </select>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $city->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $city->slug }}" required>
                                        <div class="grid grid-cols-2 gap-4">
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" value="{{ $city->map_x }}" required>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" value="{{ $city->map_y }}" required>
                                        </div>
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="description" required>{{ $city->description }}</textarea>
                                        <div class="lg:col-span-2 flex justify-end">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
