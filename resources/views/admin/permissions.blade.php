<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Permissions</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="mb-5">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Create Permission</p>
                <p class="text-sm text-white/55">Permissions control account access and can optionally display a badge icon on the character card.</p>
            </div>

            <form method="POST" action="{{ route('admin.permissions.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                    <span class="uppercase tracking-[0.18em] text-white/45">Linked Icon</span>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="account_icon_id">
                        <option value="">No icon</option>
                        @foreach ($accountIcons as $accountIcon)
                            <option value="{{ $accountIcon->id }}" @selected((string) old('account_icon_id') === (string) $accountIcon->id)>{{ $accountIcon->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-2 text-sm text-white/70 md:col-span-2">
                    <span class="uppercase tracking-[0.18em] text-white/45">Description</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" value="{{ old('description') }}" placeholder="Developer account badge.">
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Sort Order</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', 100) }}">
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                    <input type="checkbox" name="grants_admin_access" value="1" @checked(old('grants_admin_access'))>
                    Grants admin area access
                </label>
                <div class="flex items-end xl:col-span-3">
                    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Permission</button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Permission</th>
                            <th class="px-5 py-4 text-left">Slug</th>
                            <th class="px-5 py-4 text-left">Icon</th>
                            <th class="px-5 py-4 text-left">Admin Access</th>
                            <th class="px-5 py-4 text-left">Sort</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($permissions as $permission)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-white">{{ $permission->name }}</p>
                                    <p class="mt-1 text-xs text-white/45">{{ $permission->description ?: 'No description' }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $permission->slug }}</td>
                                <td class="px-5 py-4">
                                    @if ($permission->accountIcon)
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/25 text-lg" title="{{ $permission->accountIcon->name }}" style="color: {{ $permission->accountIcon->color ?: '#f4ecd0' }};">
                                            <i class="{{ $permission->accountIcon->icon_value }}"></i>
                                        </span>
                                    @else
                                        <span class="text-white/40">None</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">{{ $permission->grants_admin_access ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">{{ $permission->sort_order }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $permission->id }} ? null : {{ $permission->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $permission->id }}" x-cloak>
                                <td colspan="6" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 md:grid-cols-2 xl:grid-cols-3">
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
                                            <span class="uppercase tracking-[0.18em] text-white/45">Linked Icon</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="account_icon_id">
                                                <option value="">No icon</option>
                                                @foreach ($accountIcons as $accountIcon)
                                                    <option value="{{ $accountIcon->id }}" @selected((string) $permission->account_icon_id === (string) $accountIcon->id)>{{ $accountIcon->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70 md:col-span-2">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Description</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" value="{{ $permission->description }}">
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Sort Order</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="sort_order" type="number" min="0" max="9999" value="{{ $permission->sort_order }}">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="grants_admin_access" value="1" @checked($permission->grants_admin_access)>
                                            Grants admin area access
                                        </label>
                                        <div class="flex items-end xl:col-span-3">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save Permission</button>
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
