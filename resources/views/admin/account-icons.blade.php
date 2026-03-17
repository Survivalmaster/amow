<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Account Icons</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="mb-5">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Create Account Icon</p>
                <p class="text-sm text-white/55">Create badge icons here, then link them to permissions so they show on the character card automatically.</p>
            </div>

            <form method="POST" action="{{ route('admin.account-icons.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                <div class="flex items-end xl:col-span-3">
                    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Icon</button>
                </div>
            </form>
        </section>

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
                    <tbody class="divide-y divide-white/10">
                        @foreach ($accountIcons as $accountIcon)
                            <tr class="align-top">
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
                                        <button type="button" @click="openId = openId === {{ $accountIcon->id }} ? null : {{ $accountIcon->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('admin.account-icons.destroy', $accountIcon) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $accountIcon->id }}" x-cloak>
                                <td colspan="6" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.account-icons.update', $accountIcon) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 md:grid-cols-2 xl:grid-cols-3">
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
                                        <div class="flex items-end xl:col-span-3">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save Icon</button>
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
