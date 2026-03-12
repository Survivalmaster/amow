<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $character->faction->name }} Lobby</p>
                <p class="text-sm uppercase tracking-[0.22em] text-white/55">Welcome, {{ $character->name }}. Plastica is active.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('store.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em]">Store</a>
                <a href="{{ route('leaderboards.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em]">Leaderboards</a>
                <a href="{{ route('market.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em]">Stock Market</a>
                <a href="{{ route('characters.show') }}" class="rounded-full bg-[#7ead59] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Profile</a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Faction Intel</p>
                <p class="mt-3 text-sm leading-7 text-white/70">{{ $character->faction->lore }}</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/45">Character</p>
                    <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ $character->name }}</p>
                    <p class="text-sm text-white/70">{{ $character->rank->name }} • {{ ucfirst($character->role_type) }}</p>
                </div>
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/45">Wealth</p>
                    <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ number_format($character->plastic_credits) }}</p>
                    <p class="text-sm text-white/70">Plastic Credits</p>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Map of Plastica</p>
                    <p class="text-xs uppercase tracking-[0.24em] text-white/45">Interactive city links</p>
                </div>
                <div class="relative mt-5 overflow-hidden rounded-[2rem] border border-white/10 bg-black/20">
                    <img src="{{ asset('images/plastica_map.jpg') }}" alt="Map of Plastica" class="block w-full">
                    @foreach ($cities as $city)
                        <a
                            href="{{ route('cities.show', $city->slug) }}"
                            class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full border border-[#7ead59]/40 bg-[#07100c]/85 px-3 py-2 text-center shadow-lg shadow-black/40 transition hover:scale-105 hover:border-[#7ead59]"
                            style="left: {{ $city->map_x }}%; top: {{ $city->map_y }}%;"
                        >
                            <span class="block font-['Teko'] text-xl uppercase tracking-[0.08em] text-[#f4ecd0]">{{ $city->name }}</span>
                            <span class="block text-[10px] uppercase tracking-[0.24em] text-[#c2a84f]">Travel</span>
                        </a>
                    @endforeach
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($cities as $city)
                        <a href="{{ route('cities.show', $city->slug) }}" class="rounded-3xl border border-[#7ead59]/25 bg-[linear-gradient(180deg,rgba(126,173,89,0.14),rgba(0,0,0,0.15))] p-5 transition hover:-translate-y-0.5 hover:border-[#7ead59]/45">
                            <p class="font-['Teko'] text-3xl uppercase tracking-[0.1em]">{{ $city->name }}</p>
                            <p class="mt-2 text-sm text-white/70">{{ $city->description }}</p>
                            <p class="mt-4 text-xs uppercase tracking-[0.24em] text-[#c2a84f]">Enter city</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
