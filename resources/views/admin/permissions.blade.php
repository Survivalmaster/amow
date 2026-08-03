<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Permissions</p></x-slot>

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
                        <input x-model.debounce.150ms="query" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, slug, sections">
                    </div>
                </label>
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]">
                    <i class="fa-solid fa-plus"></i>
                    Create Permission
                </button>
            </div>
        </section>

        <x-admin.modal open="showCreate" title="Create Permission" subtitle="Controls admin access and account badge metadata." max-width="56rem">
            <form method="POST" action="{{ route('admin.permissions.store') }}" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                @csrf
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Name</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ old('name') }}" placeholder="Developer">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Slug</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ old('slug') }}" placeholder="developer">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Icon Class</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_value" value="{{ old('icon_value') }}" placeholder="fa-solid fa-code">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Icon Color</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_color" value="{{ old('icon_color') }}" placeholder="#7ec6ff">
                </label>
                <label class="grid gap-2 text-sm text-white/70 md:col-span-2 xl:col-span-4">
                    <span class="uppercase tracking-[0.18em] text-white/45">Description</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" value="{{ old('description') }}" placeholder="Developer account badge.">
                </label>
                <label class="grid gap-2 text-sm text-white/70 md:col-span-2">
                    <span class="uppercase tracking-[0.18em] text-white/45">Icon Tooltip</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_tooltip" value="{{ old('icon_tooltip') }}" placeholder="Developer">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Sort Order</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', 100) }}">
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                    <input type="checkbox" name="grants_admin_access" value="1" @checked(old('grants_admin_access'))>
                    Grants admin area access
                </label>
                <label class="grid gap-2 text-sm text-white/70 md:col-span-2 xl:col-span-4">
                    <span class="uppercase tracking-[0.18em] text-white/45">Admin Sections</span>
                    <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($adminSections as $section => $definition)
                            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                                <input type="checkbox" name="admin_sections[]" value="{{ $section }}" @checked(collect(old('admin_sections', []))->contains($section))>
                                <span>{{ $definition['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </label>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-3 xl:col-span-4">
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
                            <th class="px-5 py-4 text-left">Permission</th>
                            <th class="px-5 py-4 text-left">Slug</th>
                            <th class="px-5 py-4 text-left">Icon</th>
                            <th class="px-5 py-4 text-left">Admin Access</th>
                            <th class="px-5 py-4 text-left">Sections</th>
                            <th class="px-5 py-4 text-left">Sort</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @foreach ($permissions as $permission)
                            <tr class="align-top" data-admin-row data-search="{{ str($permission->name.' '.$permission->slug.' '.$permission->description.' '.collect($permission->admin_sections ?? [])->implode(' '))->lower() }}">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-white">{{ $permission->name }}</p>
                                    <p class="mt-1 text-xs text-white/45">{{ $permission->description ?: 'No description' }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $permission->slug }}</td>
                                <td class="px-5 py-4">
                                    @if ($permission->icon_value)
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/25 text-lg" title="{{ $permission->icon_tooltip ?: $permission->name }}" style="color: {{ $permission->icon_color ?: '#f4ecd0' }};">
                                            <i class="{{ $permission->icon_value }}"></i>
                                        </span>
                                    @else
                                        <span class="text-white/40">None</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">{{ $permission->grants_admin_access ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse ($permission->admin_sections ?? [] as $section)
                                            <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs uppercase tracking-[0.18em]">{{ $adminSections[$section]['label'] ?? $section }}</span>
                                        @empty
                                            <span class="text-white/40">None</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4">{{ $permission->sort_order }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $permission->id }}" />
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openId === {{ $permission->id }}" close="openId = null" title="Edit {{ $permission->name }}" subtitle="{{ $permission->slug }}" max-width="56rem">
                                    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
                                        @csrf
                                        @method('PATCH')
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Name</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $permission->name }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Slug</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $permission->slug }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Icon Class</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_value" value="{{ $permission->icon_value }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Icon Color</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_color" value="{{ $permission->icon_color }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70 md:col-span-2 xl:col-span-4">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Description</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" value="{{ $permission->description }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70 md:col-span-2">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Icon Tooltip</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_tooltip" value="{{ $permission->icon_tooltip }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Sort Order</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="sort_order" type="number" min="0" max="9999" value="{{ $permission->sort_order }}">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="grants_admin_access" value="1" @checked($permission->grants_admin_access)>
                                            Grants admin area access
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70 md:col-span-2 xl:col-span-4">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Admin Sections</span>
                                            <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-4">
                                                @foreach ($adminSections as $section => $definition)
                                                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3">
                                                        <input type="checkbox" name="admin_sections[]" value="{{ $section }}" @checked(in_array($section, $permission->admin_sections ?? [], true))>
                                                        <span>{{ $definition['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </label>
                                        <div class="flex justify-end gap-2 border-t border-white/10 pt-3 xl:col-span-4">
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
