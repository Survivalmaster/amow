<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-4xl uppercase tracking-[0.16em]">Faction Selection</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Choose the nation your character will serve.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($factions as $faction)
            <form method="POST" action="{{ route('factions.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <input type="hidden" name="faction_id" value="{{ $faction->id }}">
                <div class="rounded-3xl border border-dashed border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-8 text-center">
                    <p class="text-xs uppercase tracking-[0.28em] text-white/50">Flag Placeholder</p>
                </div>
                <p class="mt-5 font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ $faction->name }}</p>
                <p class="mt-3 text-sm leading-7 text-white/70">{{ $faction->short_description }}</p>
                <p class="mt-4 text-sm text-white/55">{{ $faction->lore }}</p>
                <button class="mt-6 w-full rounded-full {{ $selectedFactionId === $faction->id ? 'bg-[#c2a84f] text-[#07100c]' : 'bg-[#7ead59] text-[#07100c]' }} px-5 py-3 text-sm font-semibold uppercase tracking-[0.2em]">
                    {{ $selectedFactionId === $faction->id ? 'Selected' : 'Select Faction' }}
                </button>
            </form>
        @endforeach
    </div>
</x-app-layout>
