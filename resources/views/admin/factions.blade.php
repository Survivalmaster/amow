<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Factions</p></x-slot>

    @include('admin.partials.nav')

    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <form method="POST" action="{{ route('admin.factions.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <div class="grid gap-4">
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="short_description" placeholder="Short description" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="flag_image" placeholder="Flag image path">
                <textarea class="min-h-40 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="lore" placeholder="Lore"></textarea>
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Faction</button>
            </div>
        </form>
        <div class="space-y-4">
            @foreach ($factions as $faction)
                <form method="POST" action="{{ route('admin.factions.update', $faction) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4">
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $faction->name }}" required>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $faction->slug }}" required>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="short_description" value="{{ $faction->short_description }}" required>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="flag_image" value="{{ $faction->flag_image }}" placeholder="Flag image path">
                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="lore">{{ $faction->lore }}</textarea>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Update</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>
