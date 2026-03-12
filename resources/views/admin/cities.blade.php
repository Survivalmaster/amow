<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Cities</p></x-slot>
    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <form method="POST" action="{{ route('admin.cities.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <div class="grid gap-4">
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id" required>
                    @foreach ($factions as $faction)
                        <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                    @endforeach
                </select>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <textarea class="min-h-32 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Description" required></textarea>
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create City</button>
            </div>
        </form>
        <div class="space-y-4">
            @foreach ($cities as $city)
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $city->name }}</p>
                    <p class="text-xs uppercase tracking-[0.22em] text-white/45">{{ $city->faction->name }}</p>
                    <p class="mt-2 text-sm text-white/70">{{ $city->description }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
