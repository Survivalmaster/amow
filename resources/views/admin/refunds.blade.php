<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Refunds</p>
                <p class="mt-1 text-sm text-white/55">Issue audited XP and Plastic Credit refunds to player characters.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($characters->count()) }}</p>
                    <p>Characters</p>
                </div>
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">Audit</p>
                    <p>Enabled</p>
                </div>
            </div>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')
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
            <label class="space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45 lg:block lg:max-w-xl">
                <span>Search</span>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                    <input x-model.debounce.150ms="query" class="{{ $fieldClass }} w-full pl-9" placeholder="Name, user, faction, rank, job">
                </div>
            </label>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Character</th>
                            <th class="px-5 py-4 text-left">User</th>
                            <th class="px-5 py-4 text-left">Faction</th>
                            <th class="px-5 py-4 text-left">Level</th>
                            <th class="px-5 py-4 text-left">XP</th>
                            <th class="px-5 py-4 text-left">Credits</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @foreach ($characters as $character)
                            @php($characterUserLabel = $canViewPlayerEmails ? $character->user?->email : ($character->user?->name ?? 'User #'.$character->user_id))
                            <tr data-admin-row data-search="{{ str($character->name.' '.$characterUserLabel.' '.$character->faction?->name.' '.$character->rank?->name.' '.$character->displayed_job_name)->lower() }}">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-white">{{ $character->name }}</p>
                                    <p class="mt-1 text-xs text-white/45">{{ $character->rank?->name ?? 'Unknown rank' }} | {{ $character->displayed_job_name }}</p>
                                </td>
                                <td class="px-5 py-4">{{ $characterUserLabel }}</td>
                                <td class="px-5 py-4">{{ $character->faction?->name ?? 'Unknown' }}</td>
                                <td class="px-5 py-4">Lv {{ number_format($character->level) }}</td>
                                <td class="px-5 py-4">{{ number_format($character->experience_points) }}/{{ number_format($character->experienceRequiredForNextLevel()) }}</td>
                                <td class="px-5 py-4">{{ number_format($character->plastic_credits) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" x-on:click="openId = {{ $character->id }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Refund
                                    </button>
                                </td>
                            </tr>

                            <x-admin.modal open="openId === {{ $character->id }}" close="openId = null" title="Refund {{ $character->name }}" subtitle="Current: Lv {{ $character->level }} | {{ number_format($character->experience_points) }}/{{ number_format($character->experienceRequiredForNextLevel()) }} XP | {{ number_format($character->plastic_credits) }} credits" max-width="44rem">
                                <form method="POST" action="{{ route('admin.refunds.store') }}" class="grid gap-4 p-5 sm:grid-cols-2">
                                    @csrf
                                    <input type="hidden" name="character_id" value="{{ $character->id }}">

                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Plastic Credits</span>
                                        <input class="{{ $fieldClass }}" name="plastic_credits" type="number" min="0" value="0" required>
                                    </label>

                                    <label class="grid gap-2 text-sm text-white/70">
                                        <span class="uppercase tracking-[0.18em] text-white/45">XP</span>
                                        <input class="{{ $fieldClass }}" name="experience_points" type="number" min="0" value="0" required>
                                    </label>

                                    <label class="grid gap-2 text-sm text-white/70 sm:col-span-2">
                                        <span class="uppercase tracking-[0.18em] text-white/45">Reason</span>
                                        <textarea class="{{ $fieldClass }} min-h-28" name="reason" maxlength="500" required></textarea>
                                    </label>

                                    <div class="flex justify-end gap-2 border-t border-white/10 pt-3 sm:col-span-2">
                                        <button type="button" @click="openId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                                        <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                                            <i class="fa-solid fa-check"></i>
                                            Issue Refund
                                        </button>
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
