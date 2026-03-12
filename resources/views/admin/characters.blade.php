<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Characters</p></x-slot>

    @include('admin.partials.nav')

    <div class="space-y-4">
        @foreach ($characters as $character)
            <form method="POST" action="{{ route('admin.characters.update', $character) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $character->name }}" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="age" type="number" min="16" max="80" value="{{ $character->age }}" required>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id" required>
                        @foreach ($factions as $faction)
                            <option value="{{ $faction->id }}" @selected($character->faction_id === $faction->id)>{{ $faction->name }}</option>
                        @endforeach
                    </select>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="rank_id" required>
                        @foreach ($ranks as $rank)
                            <option value="{{ $rank->id }}" @selected($character->rank_id === $rank->id)>{{ $rank->name }}</option>
                        @endforeach
                    </select>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="starting_occupation" required>
                        @foreach (['Laborer', 'Merchant', 'Mechanic'] as $occupation)
                            <option value="{{ $occupation }}" @selected($character->starting_occupation === $occupation)>{{ $occupation }}</option>
                        @endforeach
                    </select>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="role_type" required>
                        <option value="civilian" @selected($character->role_type === 'civilian')>Civilian</option>
                        <option value="military" @selected($character->role_type === 'military')>Military</option>
                    </select>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="plastic_credits" type="number" min="0" value="{{ $character->plastic_credits }}" required>
                    <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                        <input type="checkbox" name="is_business_owner" value="1" @checked($character->is_business_owner)>
                        Business owner
                    </label>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="influence_score" type="number" min="0" value="{{ $character->influence_score }}" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="military_score" type="number" min="0" value="{{ $character->military_score }}" required>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="economic_score" type="number" min="0" value="{{ $character->economic_score }}" required>
                    <div class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/60">
                        Account: {{ $character->user->email }}
                    </div>
                </div>
                <textarea class="mt-4 min-h-28 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="biography" required>{{ $character->biography }}</textarea>
                <div class="mt-4 flex justify-end">
                    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Update Character</button>
                </div>
            </form>
        @endforeach
    </div>
</x-app-layout>
