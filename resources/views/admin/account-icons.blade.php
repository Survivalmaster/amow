<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Account Icons</p></x-slot>

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
                        <input x-model.debounce.150ms="query" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, slug, tooltip">
                    </div>
                </label>
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]">
                    <i class="fa-solid fa-plus"></i>
                    Create Icon
                </button>
            </div>
        </section>

        <x-admin.modal open="showCreate" title="Create Account Icon" subtitle="Create badge icons for permissions and player cards." max-width="48rem">
            <form method="POST" action="{{ route('admin.account-icons.store') }}" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Name</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ old('name') }}" placeholder="Administrator">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Slug</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ old('slug') }}" placeholder="admin-crown">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Icon Type</span>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_type">
                        <option value="fontawesome">Font Awesome</option>
                    </select>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Icon Class</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_value" value="{{ old('icon_value') }}" placeholder="fa-solid fa-crown">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Color</span>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-2">
                        <input type="color" value="{{ old('color', '#e1ba44') }}" oninput="this.nextElementSibling.value = this.value">
                        <input class="w-full bg-transparent py-1 outline-none" name="color" value="{{ old('color', '#e1ba44') }}" placeholder="#e1ba44">
                    </div>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Tooltip</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="tooltip" value="{{ old('tooltip') }}" placeholder="Administrator">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Sort Order</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', 100) }}">
                </label>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-3 xl:col-span-3">
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
                            <th class="px-5 py-4 text-left">Preview</th>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Slug</th>
                            <th class="px-5 py-4 text-left">Tooltip</th>
                            <th class="px-5 py-4 text-left">Sort</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @foreach ($accountIcons as $accountIcon)
                            <tr class="align-top" data-admin-row data-search="{{ str($accountIcon->name.' '.$accountIcon->slug.' '.$accountIcon->tooltip.' '.$accountIcon->icon_value)->lower() }}">
                                <td class="px-5 py-4">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/25 text-lg" style="color: {{ $accountIcon->color ?: '#f4ecd0' }};">
                                        <i class="{{ $accountIcon->icon_value }}"></i>
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-semibold text-white">{{ $accountIcon->name }}</td>
                                <td class="px-5 py-4">{{ $accountIcon->slug }}</td>
                                <td class="px-5 py-4">{{ $accountIcon->tooltip ?: 'None' }}</td>
                                <td class="px-5 py-4">{{ $accountIcon->sort_order }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $accountIcon->id }}" />
                                        <form method="POST" action="{{ route('admin.account-icons.destroy', $accountIcon) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openId === {{ $accountIcon->id }}" close="openId = null" title="Edit {{ $accountIcon->name }}" subtitle="{{ $accountIcon->slug }}" max-width="48rem">
                                    <form method="POST" action="{{ route('admin.account-icons.update', $accountIcon) }}" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                                        @csrf
                                        @method('PATCH')
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Name</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $accountIcon->name }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Slug</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $accountIcon->slug }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Icon Type</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_type">
                                                <option value="fontawesome" @selected($accountIcon->icon_type === 'fontawesome')>Font Awesome</option>
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Icon Class</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_value" value="{{ $accountIcon->icon_value }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Color</span>
                                            <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-2">
                                                <input type="color" value="{{ $accountIcon->color ?: '#e1ba44' }}" oninput="this.nextElementSibling.value = this.value">
                                                <input class="w-full bg-transparent py-1 outline-none" name="color" value="{{ $accountIcon->color }}">
                                            </div>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Tooltip</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="tooltip" value="{{ $accountIcon->tooltip }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Sort Order</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="sort_order" type="number" min="0" max="9999" value="{{ $accountIcon->sort_order }}">
                                        </label>
                                        <div class="flex justify-end gap-2 border-t border-white/10 pt-3 xl:col-span-3">
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
