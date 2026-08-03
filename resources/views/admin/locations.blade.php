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
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]">
                    <i class="fa-solid fa-plus"></i>
                    Create Location
                </button>
            </div>
        </section>

        <x-admin.modal open="showCreate" title="Create Location" subtitle="Adds a visitable place inside a city.">
            <form method="POST" action="{{ route('admin.locations.store') }}" class="grid gap-4 p-5 lg:grid-cols-2">
                @csrf
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
                <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                    <button type="button" @click="showCreate = false" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                    <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Create</button>
                </div>
            </form>
        </x-admin.modal>

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
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $location->id }}" />
                                        <form method="POST" action="{{ route('admin.locations.destroy', $location) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openId === {{ $location->id }}" close="openId = null" title="Edit {{ $location->name }}" subtitle="{{ $location->city->name }}">
                                <form method="POST" action="{{ route('admin.locations.update', $location) }}" class="grid gap-4 p-5 lg:grid-cols-2">
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
                                    <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                                        <button type="button" @click="openId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                                        <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Save</button>
                                    </div>
                                </form>
                            </x-admin.modal>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
