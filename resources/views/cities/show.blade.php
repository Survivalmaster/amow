<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $city->name }}</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ $city->faction->name }} territorial map</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">City Overview</p>
            <p class="mt-4 text-sm leading-7 text-white/70">{{ $city->description }}</p>
            <div class="mt-6 rounded-3xl border border-white/10 bg-black/20 p-5">
                <p class="text-xs uppercase tracking-[0.24em] text-white/45">Current character</p>
                <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ $character->name }}</p>
                <p class="text-sm text-white/70">{{ $character->rank->name }} • {{ ucfirst($character->role_type) }}</p>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Available Locations</p>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($city->locations as $location)
                    @php
                        $blocked = ($location->requiredRank && $character->rank->order_index < $location->requiredRank->order_index)
                            || ($location->requiredLicence && ! $character->licences->contains('id', $location->required_licence_id));
                    @endphp
                    <a href="{{ route('locations.show', $location) }}" class="rounded-3xl border {{ $blocked ? 'border-[#c65b3f]/25 bg-[#c65b3f]/10' : 'border-white/10 bg-black/20' }} p-5">
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $location->name }}</p>
                        <p class="mt-2 text-sm text-white/70">{{ $location->description }}</p>
                        @if ($location->requiredRank)
                            <p class="mt-4 text-xs uppercase tracking-[0.22em] text-[#c2a84f]">Requires {{ $location->requiredRank->name }}</p>
                        @endif
                        @if ($location->requiredLicence)
                            <p class="mt-2 text-xs uppercase tracking-[0.22em] text-[#c2a84f]">Requires {{ $location->requiredLicence->name }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
