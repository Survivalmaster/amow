<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <p class="font-['Teko'] text-4xl uppercase tracking-[0.16em] text-[#f4ecd0]">Deployment Briefing</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Step 1: Choose a faction. Step 2: Create your character.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Army Men of War Roleplay App</p>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-white/70">
                Your account is ready. Choose a nation, build your identity in Plastica, and enter a persistent world shaped by military careers,
                trade routes, political licences, and location-based text roleplay.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('factions.index') }}" class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Select Faction</a>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.16em] text-[#c2a84f]">Available Nations</p>
            <div class="mt-4 grid gap-3">
                @foreach ($factions as $faction)
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="font-['Teko'] text-2xl uppercase tracking-[0.1em]">{{ $faction->name }}</p>
                        <p class="text-sm text-white/65">{{ $faction->short_description }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
