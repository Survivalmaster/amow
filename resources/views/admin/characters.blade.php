<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Characters</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">User</th>
                            <th class="px-5 py-4 text-left">Faction</th>
                            <th class="px-5 py-4 text-left">Rank</th>
                            <th class="px-5 py-4 text-left">Credits</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($characters as $character)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-white">{{ $character->name }}</td>
                                <td class="px-5 py-4">{{ $character->user->email }}</td>
                                <td class="px-5 py-4">{{ $character->faction->name }}</td>
                                <td class="px-5 py-4">{{ $character->rank->name }}</td>
                                <td class="px-5 py-4">{{ number_format($character->plastic_credits) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $character->id }} ? null : {{ $character->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.characters.destroy', $character) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $character->id }}" x-cloak>
                                <td colspan="6" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.characters.update', $character) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2 xl:grid-cols-4">
                                        @csrf
                                        @method('PATCH')
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
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="health_points" type="number" min="0" max="100" value="{{ $character->health_points ?? 100 }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="armor_points" type="number" min="0" max="100" value="{{ $character->armor_points ?? 0 }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="influence_score" type="number" min="0" value="{{ $character->influence_score }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="military_score" type="number" min="0" value="{{ $character->military_score }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 xl:col-start-2" name="economic_score" type="number" min="0" value="{{ $character->economic_score }}" required>
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 xl:col-span-3" name="biography" required>{{ $character->biography }}</textarea>
                                        <div class="xl:col-span-4 flex justify-end">
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
