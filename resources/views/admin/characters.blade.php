<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Characters</p></x-slot>

    @include('admin.partials.nav')

    @php($canViewPlayerEmails = auth()->user()?->loadMissing('permissions')->hasPermission('developer'))

    <div
        x-data="{
            openId: null,
            query: '',
            filterRows() {
                if (!this.$refs.rows) return;
                [...this.$refs.rows.querySelectorAll('[data-admin-row]')].forEach((row) => {
                    row.toggleAttribute('hidden', this.query && !row.dataset.search.includes(this.query.toLowerCase()));
                });
            }
        }"
        x-effect="filterRows()"
        class="space-y-5"
    >
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <label class="space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45 lg:min-w-[28rem]">
                    <span>Search</span>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                        <input x-model.debounce.150ms="query" class="w-full rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 pl-9 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35" placeholder="Name, user, faction, rank, job">
                    </div>
                </label>
                <a href="{{ route('admin.character-logs.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/10 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#f4d77a]">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Character Logs
                </a>
            </div>
        </section>

        <div class="hidden">
            <a href="{{ route('admin.character-logs.index') }}" class="rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/10 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#f4d77a]">Open Character Logs</a>
        </div>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">User</th>
                            <th class="px-5 py-4 text-left">Faction</th>
                            <th class="px-5 py-4 text-left">Rank</th>
                            <th class="px-5 py-4 text-left">Nation Leader</th>
                            <th class="px-5 py-4 text-left">Job</th>
                            <th class="px-5 py-4 text-left">Credits</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @foreach ($characters as $character)
                            <tr data-admin-row data-search="{{ str($character->name.' '.$character->user->name.' '.($canViewPlayerEmails ? $character->user->email : '').' '.$character->faction->name.' '.$character->rank->name.' '.$character->displayed_job_name)->lower() }}">
                                <td class="px-5 py-4 font-semibold text-white">{{ $character->name }}</td>
                                <td class="px-5 py-4">{{ $canViewPlayerEmails ? $character->user->email : ($character->user->name ?? 'User #'.$character->user_id) }}</td>
                                <td class="px-5 py-4">{{ $character->faction->name }}</td>
                                <td class="px-5 py-4">{{ $character->rank->name }}</td>
                                <td class="px-5 py-4">{{ $character->is_nation_leader ? 'Yes' : 'No' }}</td>
                                <td class="px-5 py-4">{{ $character->displayed_job_name }}</td>
                                <td class="px-5 py-4">{{ number_format($character->plastic_credits) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.character-logs.index', ['character_id' => $character->id]) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/10 text-[#f4d77a] transition hover:bg-[#c2a84f]/20" title="Logs">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                        </a>
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $character->id }}" />
                                        <form method="POST" action="{{ route('admin.characters.destroy', $character) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openId === {{ $character->id }}" close="openId = null" title="Edit {{ $character->name }}" subtitle="{{ $character->faction->name }} | {{ $character->rank->name }}" max-width="64rem">
                                    <form method="POST" action="{{ route('admin.characters.update', $character) }}" class="grid gap-4 p-5 lg:grid-cols-2 xl:grid-cols-4">
                                        @csrf
                                        @method('PATCH')
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Character Name</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $character->name }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Age</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="age" type="number" min="16" max="80" value="{{ $character->age }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Faction</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id" required>
                                                @foreach ($factions as $faction)
                                                    <option value="{{ $faction->id }}" @selected($character->faction_id === $faction->id)>{{ $faction->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Rank</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="rank_id" required>
                                                @foreach ($ranks as $rank)
                                                    <option value="{{ $rank->id }}" @selected($character->rank_id === $rank->id)>{{ $rank->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Starting Occupation</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="starting_occupation" required>
                                                @foreach (['Laborer', 'Merchant', 'Mechanic'] as $occupation)
                                                    <option value="{{ $occupation }}" @selected($character->starting_occupation === $occupation)>{{ $occupation }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Active Job</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="current_job_id">
                                                <option value="">No active job</option>
                                                @foreach ($jobs as $job)
                                                    <option value="{{ $job->id }}" @selected($character->current_job_id === $job->id)>{{ $job->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Role Type</span>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="role_type" required>
                                                <option value="civilian" @selected($character->role_type === 'civilian')>Civilian</option>
                                                <option value="military" @selected($character->role_type === 'military')>Military</option>
                                            </select>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Credits</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="plastic_credits" type="number" min="0" value="{{ $character->plastic_credits }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Level</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="level" type="number" min="0" value="{{ $character->level }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Experience</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="experience_points" type="number" min="0" value="{{ $character->experience_points }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Health</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="health_points" type="number" min="0" max="100" value="{{ $character->health_points ?? 100 }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Stamina</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="stamina_points" type="number" min="0" max="100" value="{{ $character->stamina_points ?? 100 }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Armor</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="armor_points" type="number" min="0" max="100" value="{{ $character->armor_points ?? 0 }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Influence Score</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="influence_score" type="number" min="0" value="{{ $character->influence_score }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Military Score</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="military_score" type="number" min="0" value="{{ $character->military_score }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Economic Score</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="economic_score" type="number" min="0" value="{{ $character->economic_score }}" required>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Job Change Timestamp</span>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="job_changed_at" type="datetime-local" value="{{ $character->job_changed_at?->format('Y-m-d\\TH:i') }}">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_business_owner" value="1" @checked($character->is_business_owner)>
                                            <span>
                                                <span class="block uppercase tracking-[0.18em] text-white/45">Business Owner</span>
                                                <span class="block text-xs text-white/45">Marks this character as owning a business.</span>
                                            </span>
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_nation_leader" value="1" @checked($character->is_nation_leader)>
                                            <span>
                                                <span class="block uppercase tracking-[0.18em] text-white/45">Nation Leader</span>
                                                <span class="block text-xs text-white/45">Only one nation leader is kept per faction at a time.</span>
                                            </span>
                                        </label>
                                        <label class="grid gap-2 text-sm text-white/70 xl:col-span-4">
                                            <span class="uppercase tracking-[0.18em] text-white/45">Biography</span>
                                            <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="biography" required>{{ $character->biography }}</textarea>
                                        </label>
                                        <div class="flex justify-end gap-2 border-t border-white/10 pt-3 xl:col-span-4">
                                            <button type="button" @click="openId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                                            <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Save</button>
                                        </div>
                                    </form>
                            </x-admin.modal>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
