<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Items</p></x-slot>

    @include('admin.partials.nav')

    <div
        x-data="{
            openItemId: null,
            openLicenceId: null,
            showItemCreate: false,
            showLicenceCreate: false,
            itemQuery: '',
            licenceQuery: '',
            filterRows(refName, query) {
                if (!this.$refs[refName]) return;
                [...this.$refs[refName].querySelectorAll('[data-admin-row]')].forEach((row) => row.toggleAttribute('hidden', query && !row.dataset.search.includes(query.toLowerCase())));
            }
        }"
        x-effect="filterRows('itemRows', itemQuery); filterRows('licenceRows', licenceQuery)"
        class="space-y-5"
    >
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_auto_auto] xl:items-end">
                <label class="space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                    <span>Search Items</span>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                        <input x-model.debounce.150ms="itemQuery" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, type, requirements">
                    </div>
                </label>
                <button type="button" @click="showItemCreate = !showItemCreate" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                    <i class="fa-solid" :class="showItemCreate ? 'fa-minus' : 'fa-plus'"></i>
                    <span x-text="showItemCreate ? 'Close Item' : 'Create Item'"></span>
                </button>
                <button type="button" @click="showLicenceCreate = !showLicenceCreate" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#c2a84f] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                    <i class="fa-solid" :class="showLicenceCreate ? 'fa-minus' : 'fa-plus'"></i>
                    <span x-text="showLicenceCreate ? 'Close Licence' : 'Create Licence'"></span>
                </button>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <form x-show="showItemCreate" x-cloak method="POST" action="{{ route('admin.items.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Item</p>
                <p class="mt-2 text-sm text-white/60">Create general inventory items or mark them as buildings that can be placed on land.</p>
                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="type" placeholder="Type" value="utility" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" placeholder="Font Awesome icon class">
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="checkbox" name="is_building" value="1">
                        Building item
                    </label>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="build_time_minutes" placeholder="Build time (minutes)" min="0" value="0">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="footprint_width" placeholder="Footprint width" min="1" max="10" value="1">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="footprint_height" placeholder="Footprint height" min="1" max="10" value="1">
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="checkbox" name="is_home" value="1">
                        Legacy home item
                    </label>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="inventory_slot_bonus" placeholder="Extra inventory slots" min="0" value="0">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="price" placeholder="Price" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="stock" placeholder="Stock">
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                        <option value="">No rank requirement</option>
                        @foreach ($ranks as $rank)
                            <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                        @endforeach
                    </select>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_role_type">
                        <option value="">Any role</option>
                        <option value="civilian">Civilian</option>
                        <option value="military">Military</option>
                    </select>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="required_licence_id">
                        <option value="">No licence requirement</option>
                        @foreach ($licences as $licence)
                            <option value="{{ $licence->id }}">{{ $licence->name }}</option>
                        @endforeach
                    </select>
                    <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="description" placeholder="Description" required></textarea>
                </div>
                <div class="mt-5 flex justify-end">
                    <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Item</button>
                </div>
            </form>

            <form x-show="showLicenceCreate" x-cloak method="POST" action="{{ route('admin.licences.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Licence</p>
                <p class="mt-2 text-sm text-white/60">Manage purchasable licences here, including the new Land unlock.</p>
                <div class="mt-5 grid gap-4">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="cost" placeholder="Cost" min="1" required>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                        <option value="">No rank requirement</option>
                        @foreach ($ranks as $rank)
                            <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                        @endforeach
                    </select>
                    <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Description" required></textarea>
                </div>
                <div class="mt-5 flex justify-end">
                    <button class="rounded-full bg-[#c2a84f] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Licence</button>
                </div>
            </form>
        </div>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="border-b border-white/10 px-5 py-4">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Items</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Type</th>
                            <th class="px-5 py-4 text-left">Building</th>
                            <th class="px-5 py-4 text-left">Footprint</th>
                            <th class="px-5 py-4 text-left">Build Time</th>
                            <th class="px-5 py-4 text-left">Price</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="itemRows" class="divide-y divide-white/10">
                        @foreach ($items as $item)
                            <tr data-admin-row data-search="{{ str($item->name.' '.$item->slug.' '.$item->type.' '.$item->description.' '.$item->requiredRank?->name.' '.$item->required_role_type.' '.$item->requiredLicence?->name)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $item->name }}</td>
                                <td class="px-5 py-4">{{ $item->type }}</td>
                                <td class="px-5 py-4">{{ $item->is_building ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">{{ $item->footprint_width }}x{{ $item->footprint_height }}</td>
                                <td class="px-5 py-4">{{ $item->build_time_minutes }} min</td>
                                <td class="px-5 py-4">{{ number_format($item->price) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openItemId = openItemId === {{ $item->id }} ? null : {{ $item->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.items.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openItemId === {{ $item->id }}" x-cloak>
                                <td colspan="7" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.items.update', $item) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $item->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $item->slug }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="type" value="{{ $item->type }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" value="{{ $item->icon_class }}" placeholder="Font Awesome icon class">
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_building" value="1" @checked($item->is_building)>
                                            Building item
                                        </label>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="build_time_minutes" value="{{ $item->build_time_minutes }}" min="0">
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="footprint_width" value="{{ $item->footprint_width }}" min="1" max="10">
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="footprint_height" value="{{ $item->footprint_height }}" min="1" max="10">
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_home" value="1" @checked($item->is_home)>
                                            Legacy home item
                                        </label>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="inventory_slot_bonus" value="{{ $item->inventory_slot_bonus }}" min="0">
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="price" value="{{ $item->price }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="stock" value="{{ $item->stock }}" placeholder="Stock">
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                                            <option value="">No rank requirement</option>
                                            @foreach ($ranks as $rank)
                                                <option value="{{ $rank->id }}" @selected($item->required_rank_id === $rank->id)>{{ $rank->name }}</option>
                                            @endforeach
                                        </select>
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_role_type">
                                            <option value="">Any role</option>
                                            <option value="civilian" @selected($item->required_role_type === 'civilian')>Civilian</option>
                                            <option value="military" @selected($item->required_role_type === 'military')>Military</option>
                                        </select>
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="required_licence_id">
                                            <option value="">No licence requirement</option>
                                            @foreach ($licences as $licence)
                                                <option value="{{ $licence->id }}" @selected($item->required_licence_id === $licence->id)>{{ $licence->name }}</option>
                                            @endforeach
                                        </select>
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="description" required>{{ $item->description }}</textarea>
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

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="border-b border-white/10 px-5 py-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Licences</p>
                    <label class="space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45 lg:min-w-[24rem]">
                        <span>Search Licences</span>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                            <input x-model.debounce.150ms="licenceQuery" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, slug, required rank">
                        </div>
                    </label>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Slug</th>
                            <th class="px-5 py-4 text-left">Cost</th>
                            <th class="px-5 py-4 text-left">Required Rank</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="licenceRows" class="divide-y divide-white/10">
                        @foreach ($licences as $licence)
                            <tr data-admin-row data-search="{{ str($licence->name.' '.$licence->slug.' '.$licence->description.' '.$licence->requiredRank?->name)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $licence->name }}</td>
                                <td class="px-5 py-4">{{ $licence->slug }}</td>
                                <td class="px-5 py-4">{{ number_format($licence->cost) }}</td>
                                <td class="px-5 py-4">{{ $licence->requiredRank?->name ?? 'None' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openLicenceId = openLicenceId === {{ $licence->id }} ? null : {{ $licence->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.licences.destroy', $licence) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openLicenceId === {{ $licence->id }}" x-cloak>
                                <td colspan="5" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.licences.update', $licence) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $licence->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $licence->slug }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="cost" value="{{ $licence->cost }}" min="1" required>
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                                            <option value="">No rank requirement</option>
                                            @foreach ($ranks as $rank)
                                                <option value="{{ $rank->id }}" @selected($licence->required_rank_id === $rank->id)>{{ $rank->name }}</option>
                                            @endforeach
                                        </select>
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="description" required>{{ $licence->description }}</textarea>
                                        <div class="lg:col-span-2 flex justify-end">
                                            <button class="rounded-full bg-[#c2a84f] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
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
