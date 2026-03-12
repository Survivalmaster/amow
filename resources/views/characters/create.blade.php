<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-4xl uppercase tracking-[0.16em]">Create Character</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Faction: {{ $faction->name }}</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-4xl uppercase tracking-[0.1em]">{{ $faction->name }}</p>
            <p class="mt-4 text-sm leading-7 text-white/70">{{ $faction->lore }}</p>
            <div class="mt-6 rounded-3xl border border-white/10 bg-black/20 p-5">
                <p class="text-sm uppercase tracking-[0.22em] text-white/55">Starter package</p>
                <ul class="mt-4 space-y-3 text-sm text-white/70">
                    <li>100 Plastic Credits on spawn.</li>
                    <li>One character per account.</li>
                    <li>Starting occupations: {{ implode(', ', $occupations) }}.</li>
                    <li>Military starts as Recruit. Civilian starts as Civilian.</li>
                </ul>
            </div>
        </section>

        <form method="POST" action="{{ route('characters.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <label class="block text-sm uppercase tracking-[0.18em] text-white/55">Name
                <input class="mt-2 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="text" name="name" value="{{ old('name') }}" required>
            </label>
            <label class="mt-5 block text-sm uppercase tracking-[0.18em] text-white/55">Age
                <input class="mt-2 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="age" value="{{ old('age') }}" min="16" max="80" required>
            </label>
            <label class="mt-5 block text-sm uppercase tracking-[0.18em] text-white/55">Biography
                <textarea class="mt-2 min-h-40 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="biography" required>{{ old('biography') }}</textarea>
            </label>
            <label class="mt-5 block text-sm uppercase tracking-[0.18em] text-white/55">Occupation
                <select class="mt-2 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="starting_occupation" required>
                    @foreach ($occupations as $occupation)
                        <option value="{{ $occupation }}" @selected(old('starting_occupation') === $occupation)>{{ $occupation }}</option>
                    @endforeach
                </select>
            </label>
            <label class="mt-5 block text-sm uppercase tracking-[0.18em] text-white/55">Role Type
                <select class="mt-2 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="role_type" required>
                    <option value="civilian" @selected(old('role_type') === 'civilian')>Civilian</option>
                    <option value="military" @selected(old('role_type') === 'military')>Military</option>
                </select>
            </label>
            <button class="mt-6 w-full rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Enter Plastica</button>
        </form>
    </div>
</x-app-layout>
