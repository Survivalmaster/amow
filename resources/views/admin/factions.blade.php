<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Factions</p></x-slot>

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
                        <input x-model.debounce.150ms="query" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, slug, summary">
                    </div>
                </label>
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]">
                    <i class="fa-solid fa-plus"></i>
                    Create Faction
                </button>
            </div>
        </section>

        <x-admin.modal open="showCreate" title="Create Faction" subtitle="Adds a new major army or civilian bloc.">
            <form method="POST" action="{{ route('admin.factions.store') }}" class="grid gap-4 p-5 lg:grid-cols-2">
                @csrf
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Faction Name</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Slug</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                    <span class="uppercase tracking-[0.18em] text-white/45">Short Description</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="short_description" placeholder="Short description" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Flag Image</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="flag_image" placeholder="Flag image path">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Faction Color</span>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-2">
                        <input type="color" value="#7ead59" oninput="this.nextElementSibling.value = this.value">
                        <input class="w-full bg-transparent py-1 outline-none" name="color" value="#7ead59" placeholder="#7ead59">
                    </div>
                </label>
                <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                    <span class="uppercase tracking-[0.18em] text-white/45">Lore</span>
                    <textarea class="min-h-32 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="lore" placeholder="Lore"></textarea>
                </label>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                    <button type="button" @click="showCreate = false" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                    <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                        <i class="fa-solid fa-check"></i>
                        Create
                    </button>
                </div>
            </form>
        </x-admin.modal>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Slug</th>
                            <th class="px-5 py-4 text-left">Color</th>
                            <th class="px-5 py-4 text-left">Summary</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @foreach ($factions as $faction)
                            <tr data-admin-row data-search="{{ str($faction->name.' '.$faction->slug.' '.$faction->short_description.' '.$faction->color)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $faction->name }}</td>
                                <td class="px-5 py-4">{{ $faction->slug }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="h-6 w-6 rounded-full border border-white/10" style="background: {{ $faction->color ?: '#7ead59' }};"></span>
                                        <span>{{ $faction->color ?: 'Default' }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">{{ $faction->short_description }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $faction->id }}" />
                                        <form method="POST" action="{{ route('admin.factions.destroy', $faction) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openId === {{ $faction->id }}" close="openId = null" title="Edit {{ $faction->name }}" subtitle="{{ $faction->slug }}">
                                <form method="POST" action="{{ route('admin.factions.update', $faction) }}" class="grid gap-4 p-5 lg:grid-cols-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Faction Name</span>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $faction->name }}" required>
                                    </label>
                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Slug</span>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $faction->slug }}" required>
                                    </label>
                                    <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Short Description</span>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="short_description" value="{{ $faction->short_description }}" required>
                                    </label>
                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Flag Image</span>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="flag_image" value="{{ $faction->flag_image }}" placeholder="Flag image path">
                                    </label>
                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Faction Color</span>
                                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-2">
                                            <input type="color" value="{{ $faction->color ?: '#7ead59' }}" oninput="this.nextElementSibling.value = this.value">
                                            <input class="w-full bg-transparent py-1 outline-none" name="color" value="{{ $faction->color ?: '#7ead59' }}">
                                        </div>
                                    </label>
                                    <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Lore</span>
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="lore">{{ $faction->lore }}</textarea>
                                    </label>
                                    <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                                        <button type="button" @click="openId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                                        <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                                            <i class="fa-solid fa-check"></i>
                                            Save
                                        </button>
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
