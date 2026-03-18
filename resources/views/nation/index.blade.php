<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $faction->name }}</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Nation overview, members, treasury, and leadership tools.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Nation Bank</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase text-[#c2a84f]">{{ number_format($faction->nation_bank_credits) }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Members</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $faction->characters->count() }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Nation Leader</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ optional($faction->characters->firstWhere('is_nation_leader', true))->name ?? 'Unassigned' }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-[1.5rem] border border-white/10 bg-black/20 p-5">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">Donate Credits</p>
                    <p class="mt-2 text-sm text-white/65">Support the nation treasury with any amount your character can afford.</p>
                    <form method="POST" action="{{ route('nation.donate') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                        @csrf
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 sm:max-w-xs" type="number" min="1" max="{{ $character->plastic_credits }}" name="amount" placeholder="Amount" required>
                        <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">Donate to Nation</button>
                    </form>
                </div>
            </div>

            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">Nation Members</p>
                        <p class="mt-2 text-sm text-white/65">See who is in the nation, their role type, and their current rank.</p>
                    </div>
                    @if ($character->canLeadNation())
                        <span class="rounded-full border border-[#7ead59]/30 bg-[#7ead59]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Leader Controls Enabled</span>
                    @endif
                </div>

                <div class="mt-5 space-y-3">
                    @foreach ($faction->characters->sortByDesc('is_nation_leader') as $member)
                        <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $member->name }}</p>
                                    <p class="text-xs uppercase tracking-[0.18em] text-white/45">{{ $member->role_type }} | {{ $member->rank?->name ?? 'Unranked' }} | {{ $member->user->name }}</p>
                                    @if ($member->is_nation_leader)
                                        <p class="mt-2 text-xs uppercase tracking-[0.18em] text-[#c2a84f]">Nation leader</p>
                                    @endif
                                </div>

                                @if ($character->canLeadNation() && $member->role_type === 'military')
                                    <form method="POST" action="{{ route('nation.members.update-rank', $member) }}" class="flex flex-col gap-3 sm:flex-row">
                                        @csrf
                                        @method('PATCH')
                                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="rank_id">
                                            @foreach ($militaryRanks as $rank)
                                                <option value="{{ $rank->id }}" @selected($member->rank_id === $rank->id)>{{ $rank->name }}</option>
                                            @endforeach
                                        </select>
                                        <button class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Update Rank</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">Nation Stats</p>
                <div class="mt-4 grid gap-3">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Influence Total</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ number_format($faction->characters->sum('influence_score')) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Military Total</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ number_format($faction->characters->sum('military_score')) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Economic Total</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ number_format($faction->characters->sum('economic_score')) }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">Recent Requisitions</p>
                        <p class="mt-2 text-sm text-white/65">Leaders can review the latest nation request history here.</p>
                    </div>
                    @if ($character->canLeadNation())
                        <a href="{{ route('nation.requisitions.index') }}" class="rounded-full border border-white/10 bg-black/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Open Requisitions</a>
                    @endif
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($faction->requisitions as $requisition)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                            <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $requisition->title }}</p>
                            <p class="text-xs uppercase tracking-[0.18em] text-white/45">{{ str($requisition->status)->replace('_', ' ')->title() }}</p>
                            @if ($requisition->admin_reason)
                                <p class="mt-2 text-sm text-white/65">{{ $requisition->admin_reason }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No requisitions yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
