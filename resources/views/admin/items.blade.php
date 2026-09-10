<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Items</p></x-slot>

    @include('admin.partials.nav')

    @php
        $fieldClass = 'rounded-2xl border border-white/10 bg-black/25 px-4 py-3';
        $labelClass = 'grid gap-2 text-sm text-white/70';
        $labelTitleClass = 'text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45';
    @endphp

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
                <button type="button" @click="showItemCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]">
                    <i class="fa-solid fa-plus"></i>
                    Create Item
                </button>
                <button type="button" @click="showLicenceCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#c2a84f] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#f4d77a]">
                    <i class="fa-solid fa-plus"></i>
                    Create Licence
                </button>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-admin.modal open="showItemCreate" title="Create Item" subtitle="Create inventory items and building unlocks." max-width="56rem">
            <form method="POST" action="{{ route('admin.items.store') }}" class="p-5">
                @csrf
                <div class="grid gap-4 lg:grid-cols-2">
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Item Name</span>
                        <input class="{{ $fieldClass }}" name="name" placeholder="Plastic Rifle" required>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Slug</span>
                        <input class="{{ $fieldClass }}" name="slug" placeholder="plastic-rifle" required>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Item Type</span>
                        <select class="{{ $fieldClass }}" name="type" required>
                            @foreach ($itemTypes as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'utility')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Icon Class</span>
                        <input class="{{ $fieldClass }}" name="icon_class" placeholder="Font Awesome icon class">
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="checkbox" name="is_building" value="1">
                        Building item
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="hidden" name="is_buyable" value="0">
                        <input type="checkbox" name="is_buyable" value="1" checked>
                        Buyable in store
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Build Time Minutes</span>
                        <input class="{{ $fieldClass }}" type="number" name="build_time_minutes" min="0" value="0">
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Produced By Building</span>
                        <select class="{{ $fieldClass }}" name="produced_by_building_item_id">
                            <option value="">Not produced by a building</option>
                            @foreach ($buildingItems as $buildingItem)
                                <option value="{{ $buildingItem->id }}">{{ $buildingItem->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Footprint Width</span>
                        <input class="{{ $fieldClass }}" type="number" name="footprint_width" min="1" max="10" value="1">
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Footprint Height</span>
                        <input class="{{ $fieldClass }}" type="number" name="footprint_height" min="1" max="10" value="1">
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="checkbox" name="is_home" value="1">
                        Can be slept in
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Inventory Slot Bonus</span>
                        <input class="{{ $fieldClass }}" type="number" name="inventory_slot_bonus" min="0" value="0">
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Max Per Slot</span>
                        <input class="{{ $fieldClass }}" type="number" name="max_stack_per_slot" min="1" value="1">
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Price</span>
                        <input class="{{ $fieldClass }}" type="number" name="price" placeholder="Price" required>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Stock</span>
                        <input class="{{ $fieldClass }}" type="number" name="stock" placeholder="Blank for unlimited">
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Required Level</span>
                        <input class="{{ $fieldClass }}" type="number" name="required_level" min="0" placeholder="No level requirement">
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Required Role</span>
                        <select class="{{ $fieldClass }}" name="required_role_type">
                            <option value="">Any role</option>
                            <option value="civilian">Civilian</option>
                            <option value="military">Military</option>
                        </select>
                    </label>
                    <label class="{{ $labelClass }} lg:col-span-2">
                        <span class="{{ $labelTitleClass }}">Required Licence</span>
                        <select class="{{ $fieldClass }}" name="required_licence_id">
                            <option value="">No licence requirement</option>
                            @foreach ($licences as $licence)
                                <option value="{{ $licence->id }}">{{ $licence->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="{{ $labelClass }} lg:col-span-2">
                        <span class="{{ $labelTitleClass }}">Description</span>
                        <textarea class="min-h-28 {{ $fieldClass }}" name="description" placeholder="What does this item do?" required></textarea>
                    </label>
                </div>
                <div class="mt-5 flex justify-end gap-2 border-t border-white/10 pt-3">
                    <button type="button" @click="showItemCreate = false" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                    <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Create</button>
                </div>
            </form>
            </x-admin.modal>

            <x-admin.modal open="showLicenceCreate" title="Create Licence" subtitle="Manage purchasable licence unlocks.">
            <form method="POST" action="{{ route('admin.licences.store') }}" class="p-5">
                @csrf
                <div class="grid gap-4">
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Licence Name</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Business Owner" required>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Slug</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="business-owner" required>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Cost</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="cost" placeholder="Cost" min="1" required>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Required Level</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="required_level" min="0" placeholder="No level requirement">
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="checkbox" name="grants_business_creation" value="1">
                        Allows player business creation
                    </label>
                    <label class="{{ $labelClass }}">
                        <span class="{{ $labelTitleClass }}">Description</span>
                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Description" required></textarea>
                    </label>
                </div>
                <div class="mt-5 flex justify-end gap-2 border-t border-white/10 pt-3">
                    <button type="button" @click="showLicenceCreate = false" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                    <button class="inline-flex items-center gap-2 rounded-full bg-[#c2a84f] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Create</button>
                </div>
            </form>
            </x-admin.modal>
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
                            <th class="px-5 py-4 text-left">Buyable</th>
                            <th class="px-5 py-4 text-left">Building</th>
                            <th class="px-5 py-4 text-left">Footprint</th>
                            <th class="px-5 py-4 text-left">Build Time</th>
                            <th class="px-5 py-4 text-left">Produced By</th>
                            <th class="px-5 py-4 text-left">Stack</th>
                            <th class="px-5 py-4 text-left">Required Level</th>
                            <th class="px-5 py-4 text-left">Price</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="itemRows" class="divide-y divide-white/10">
                        @foreach ($items as $item)
                            <tr data-admin-row data-search="{{ str($item->name.' '.$item->slug.' '.$item->type.' '.$item->description.' level '.$item->required_level.' '.$item->required_role_type.' '.$item->requiredLicence?->name.' '.$item->producingBuilding?->name)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $item->name }}</td>
                                <td class="px-5 py-4">{{ $item->type_label }}</td>
                                <td class="px-5 py-4">{{ $item->is_buyable ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">{{ $item->is_building ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">{{ $item->footprint_width }}x{{ $item->footprint_height }}</td>
                                <td class="px-5 py-4">{{ $item->build_time_minutes }} min</td>
                                <td class="px-5 py-4">{{ $item->producingBuilding?->name ?? 'None' }}</td>
                                <td class="px-5 py-4">{{ number_format($item->max_stack_per_slot ?? 1) }}/slot</td>
                                <td class="px-5 py-4">{{ $item->required_level !== null ? 'Level '.$item->required_level : 'None' }}</td>
                                <td class="px-5 py-4">{{ number_format($item->price) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openItemId = {{ $item->id }}" />
                                        <form method="POST" action="{{ route('admin.items.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openItemId === {{ $item->id }}" close="openItemId = null" title="Edit {{ $item->name }}" subtitle="{{ $item->type_label }}" max-width="56rem">
                                    <form method="POST" action="{{ route('admin.items.update', $item) }}" class="grid gap-4 p-5 lg:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Item Name</span>
                                            <input class="{{ $fieldClass }}" name="name" value="{{ $item->name }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Slug</span>
                                            <input class="{{ $fieldClass }}" name="slug" value="{{ $item->slug }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Item Type</span>
                                            <select class="{{ $fieldClass }}" name="type" required>
                                                @foreach ($itemTypes as $value => $label)
                                                    <option value="{{ $value }}" @selected($item->type === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Icon Class</span>
                                            <input class="{{ $fieldClass }}" name="icon_class" value="{{ $item->icon_class }}" placeholder="Font Awesome icon class">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_building" value="1" @checked($item->is_building)>
                                            Building item
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="hidden" name="is_buyable" value="0">
                                            <input type="checkbox" name="is_buyable" value="1" @checked($item->is_buyable)>
                                            Buyable in store
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Build Time Minutes</span>
                                            <input class="{{ $fieldClass }}" type="number" name="build_time_minutes" value="{{ $item->build_time_minutes }}" min="0">
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Produced By Building</span>
                                            <select class="{{ $fieldClass }}" name="produced_by_building_item_id">
                                                <option value="">Not produced by a building</option>
                                                @foreach ($buildingItems as $buildingItem)
                                                    @if ($buildingItem->id !== $item->id)
                                                        <option value="{{ $buildingItem->id }}" @selected($item->produced_by_building_item_id === $buildingItem->id)>{{ $buildingItem->name }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Footprint Width</span>
                                            <input class="{{ $fieldClass }}" type="number" name="footprint_width" value="{{ $item->footprint_width }}" min="1" max="10">
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Footprint Height</span>
                                            <input class="{{ $fieldClass }}" type="number" name="footprint_height" value="{{ $item->footprint_height }}" min="1" max="10">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_home" value="1" @checked($item->is_home)>
                                            Can be slept in
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Inventory Slot Bonus</span>
                                            <input class="{{ $fieldClass }}" type="number" name="inventory_slot_bonus" value="{{ $item->inventory_slot_bonus }}" min="0">
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Max Per Slot</span>
                                            <input class="{{ $fieldClass }}" type="number" name="max_stack_per_slot" value="{{ $item->max_stack_per_slot ?? 1 }}" min="1">
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Price</span>
                                            <input class="{{ $fieldClass }}" type="number" name="price" value="{{ $item->price }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Stock</span>
                                            <input class="{{ $fieldClass }}" type="number" name="stock" value="{{ $item->stock }}" placeholder="Blank for unlimited">
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Required Level</span>
                                            <input class="{{ $fieldClass }}" type="number" name="required_level" value="{{ $item->required_level }}" min="0" placeholder="No level requirement">
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Required Role</span>
                                            <select class="{{ $fieldClass }}" name="required_role_type">
                                                <option value="">Any role</option>
                                                <option value="civilian" @selected($item->required_role_type === 'civilian')>Civilian</option>
                                                <option value="military" @selected($item->required_role_type === 'military')>Military</option>
                                            </select>
                                        </label>
                                        <label class="{{ $labelClass }} lg:col-span-2">
                                            <span class="{{ $labelTitleClass }}">Required Licence</span>
                                            <select class="{{ $fieldClass }}" name="required_licence_id">
                                                <option value="">No licence requirement</option>
                                                @foreach ($licences as $licence)
                                                    <option value="{{ $licence->id }}" @selected($item->required_licence_id === $licence->id)>{{ $licence->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="{{ $labelClass }} lg:col-span-2">
                                            <span class="{{ $labelTitleClass }}">Description</span>
                                            <textarea class="min-h-28 {{ $fieldClass }}" name="description" required>{{ $item->description }}</textarea>
                                        </label>
                                        <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                                            <button type="button" @click="openItemId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                                            <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Save</button>
                                        </div>
                                    </form>
                            </x-admin.modal>
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
                            <th class="px-5 py-4 text-left">Required Level</th>
                            <th class="px-5 py-4 text-left">Business</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="licenceRows" class="divide-y divide-white/10">
                        @foreach ($licences as $licence)
                            <tr data-admin-row data-search="{{ str($licence->name.' '.$licence->slug.' '.$licence->description.' level '.$licence->required_level)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $licence->name }}</td>
                                <td class="px-5 py-4">{{ $licence->slug }}</td>
                                <td class="px-5 py-4">{{ number_format($licence->cost) }}</td>
                                <td class="px-5 py-4">{{ $licence->required_level !== null ? 'Level '.$licence->required_level : 'None' }}</td>
                                <td class="px-5 py-4">{{ $licence->grants_business_creation ? 'Creates businesses' : 'No' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openLicenceId = {{ $licence->id }}" />
                                        <form method="POST" action="{{ route('admin.licences.destroy', $licence) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openLicenceId === {{ $licence->id }}" close="openLicenceId = null" title="Edit {{ $licence->name }}" subtitle="{{ $licence->slug }}">
                                    <form method="POST" action="{{ route('admin.licences.update', $licence) }}" class="grid gap-4 p-5 lg:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Licence Name</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $licence->name }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Slug</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $licence->slug }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Cost</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="cost" value="{{ $licence->cost }}" min="1" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span class="{{ $labelTitleClass }}">Required Level</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="required_level" value="{{ $licence->required_level }}" min="0" placeholder="No level requirement">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="grants_business_creation" value="1" @checked($licence->grants_business_creation)>
                                            Allows player business creation
                                        </label>
                                        <label class="{{ $labelClass }} lg:col-span-2">
                                            <span class="{{ $labelTitleClass }}">Description</span>
                                            <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" required>{{ $licence->description }}</textarea>
                                        </label>
                                        <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                                            <button type="button" @click="openLicenceId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                                            <button class="inline-flex items-center gap-2 rounded-full bg-[#c2a84f] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Save</button>
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
