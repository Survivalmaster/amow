<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $character->name }}</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ $character->faction->name }} | {{ $character->rank->name }}</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Character File</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-[#c2a84f]/20 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-[#c2a84f]">Level</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">Lv. <span data-character-field="level">{{ $character->level }}</span></p>
                        <div class="mt-3 flex items-center justify-between text-xs uppercase tracking-[0.18em] text-white/55">
                            <span>Experience</span>
                            <span data-character-field="experience_label">{{ $character->experience_points }}/{{ $character->experienceRequiredForNextLevel() }}</span>
                        </div>
                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/10">
                            <div class="h-full rounded-full bg-[linear-gradient(90deg,#c2a84f_0%,#f4d77a_100%)]" data-character-width="experience_progress_percent" style="width: {{ min(100, (int) round(($character->experience_points / max(1, $character->experienceRequiredForNextLevel())) * 100)) }}%;"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4 text-sm text-white/70">
                        <p><span class="text-white/45">Age:</span> {{ $character->age }}</p>
                        <p class="mt-2"><span class="text-white/45">Role:</span> {{ ucfirst($character->role_type) }}</p>
                        <p class="mt-2"><span class="text-white/45">Job:</span> <span data-character-field="displayed_job_name">{{ $character->displayed_job_name }}</span></p>
                        <p class="mt-2"><span class="text-white/45">Credits:</span> <span data-character-field="formatted_credits">{{ number_format($character->plastic_credits) }}</span></p>
                        <p class="mt-2"><span class="text-white/45">Business owner:</span> {{ $character->is_business_owner ? 'Yes' : 'No' }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 text-sm text-white/70">
                    <p><span class="text-white/45">Starting occupation:</span> {{ $character->starting_occupation }}</p>
                </div>
                <p class="mt-5 text-sm leading-7 text-white/70">{{ $character->biography }}</p>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Licences</p>
                <div class="mt-4 space-y-3">
                    @forelse ($character->licences as $licence)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $licence->name }}</p>
                            <p class="text-sm text-white/70">{{ $licence->description }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No licences acquired.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Inventory Summary</p>
                        <p class="mt-2 text-sm text-white/60">A quick overview. Full slot management now lives in the dedicated Inventory page.</p>
                    </div>
                    <a href="{{ route('inventory.index') }}" class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Open Inventory</a>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Used Slots</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $inventorySlotsUsed }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Max Slots</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $inventorySlotCapacity }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Home Assets</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $homeItemCount }}</p>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse ($character->inventory->take(4) as $item)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-black/30 text-lg text-[#d7edc7]">
                                    <i class="{{ $item->display_icon_class }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                                    <p class="text-sm text-white/70">{{ $item->description }}</p>
                                    <p class="mt-2 text-xs uppercase tracking-[0.22em] text-[#c2a84f]">Qty {{ $item->pivot->quantity }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No items in inventory.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Transactions</p>
                <div class="mt-4 space-y-3">
                    @foreach ($character->transactions as $transaction)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ str_replace('_', ' ', $transaction->type) }}</p>
                                <p class="font-['Teko'] text-2xl uppercase {{ $transaction->amount >= 0 ? 'text-[#7ead59]' : 'text-[#c65b3f]' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount) }}</p>
                            </div>
                            <p class="text-sm text-white/70">{{ $transaction->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
