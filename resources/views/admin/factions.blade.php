<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Factions</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <form method="POST" action="{{ route('admin.factions.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Faction</p>
            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="short_description" placeholder="Short description" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="flag_image" placeholder="Flag image path">
                <textarea class="min-h-32 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="lore" placeholder="Lore"></textarea>
            </div>
            <div class="mt-5 flex justify-end">
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Faction</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Slug</th>
                            <th class="px-5 py-4 text-left">Summary</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($factions as $faction)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-white">{{ $faction->name }}</td>
                                <td class="px-5 py-4">{{ $faction->slug }}</td>
                                <td class="px-5 py-4">{{ $faction->short_description }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $faction->id }} ? null : {{ $faction->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.factions.destroy', $faction) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $faction->id }}" x-cloak>
                                <td colspan="4" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.factions.update', $faction) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $faction->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $faction->slug }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="short_description" value="{{ $faction->short_description }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="flag_image" value="{{ $faction->flag_image }}" placeholder="Flag image path">
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2" name="lore">{{ $faction->lore }}</textarea>
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
