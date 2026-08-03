<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Locations</p></x-slot>

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
                        <input x-model.debounce.150ms="query" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, city, requirements">
                    </div>
                </label>
                <button type="button" @click="showCreate = !showCreate" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                    <i class="fa-solid" :class="showCreate ? 'fa-minus' : 'fa-plus'"></i>
                    <span x-text="showCreate ? 'Close Create' : 'Create Location'"></span>
                </button>
            </div>
        </section>

        <form x-show="showCreate" x-cloak method="POST" action="{{ route('admin.locations.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Location</p>
            <p class="mt-2 text-sm text-white/60">Adds a visitable place inside a city, with optional rank or licence restrictions for access.</p>
            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="city_id" required>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                    <option value="">No rank requirement</option>
                    @foreach ($ranks as $rank)
                        <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                    @endforeach
                </select>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_licence_id">
                    <option value="">No licence requirement</option>
                    @foreach ($licences as $licence)
                        <option value="{{ $licence->id }}">{{ $licence->name }}</option>
                    @endforeach
                </select>
                <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="description" placeholder="Description" required></textarea>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70 lg:col-span-2"><input type="checkbox" name="is_public" value="1" checked> Public</label>
            </div>
            <div class="mt-5 flex justify-end">
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Location</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">City</th>
                            <th class="px-5 py-4 text-left">Requirements</th>
                            <th class="px-5 py-4 text-left">Public</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @foreach ($locations as $location)
                            <tr data-admin-row data-search="{{ str($location->name.' '.$location->city->name.' '.$location->requiredRank?->name.' '.$location->requiredLicence?->name.' '.$location->description)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $location->name }}</td>
                                <td class="px-5 py-4">{{ $location->city->name }}</td>
                                <td class="px-5 py-4">{{ $location->requiredRank?->name ?? 'Any rank' }} | {{ $location->requiredLicence?->name ?? 'No licence' }}</td>
                                <td class="px-5 py-4">{{ $location->is_public ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $location->id }} ? null : {{ $location->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.locations.destroy', $location) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $location->id }}" x-cloak>
                                <td colspan="5" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.locations.update', $location) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="city_id" required>
                                            @foreach ($cities as $city)
                                                <option value="{{ $city->id }}" @selected($location->city_id === $city->id)>{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $location->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $location->slug }}" required>
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                                            <option value="">No rank requirement</option>
                                            @foreach ($ranks as $rank)
                                                <option value="{{ $rank->id }}" @selected($location->required_rank_id === $rank->id)>{{ $rank->name }}</option>
                                            @endforeach
                                        </select>
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_licence_id">
                                            <option value="">No licence requirement</option>
                                            @foreach ($licences as $licence)
                                                <option value="{{ $licence->id }}" @selected($location->required_licence_id === $licence->id)>{{ $licence->name }}</option>
                                            @endforeach
                                        </select>
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="description" required>{{ $location->description }}</textarea>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70 lg:col-span-2"><input type="checkbox" name="is_public" value="1" @checked($location->is_public)> Public</label>
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
