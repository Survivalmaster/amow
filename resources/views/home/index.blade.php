<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Home</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Manage your shelter, your recovery space, and the gear you keep close.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="space-y-6">
            <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-[radial-gradient(circle_at_top,rgba(126,173,89,0.12),rgba(255,255,255,0.02)_45%,rgba(255,255,255,0.03)_100%)] p-6 shadow-2xl shadow-black/30">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Home Base</p>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/70">
                            Your home area is now active because you own at least one home item. This page is where recovery, storage, and future home upgrades will live as the system grows.
                        </p>
                    </div>
                    <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-black/25 px-4 py-3 text-right">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Active Home Assets</p>
                        <p class="font-['Teko'] text-4xl uppercase text-[#d7edc7]">{{ $homeItems->count() }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/25 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Shelter Status</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">Established</p>
                        <p class="mt-2 text-sm text-white/60">You have the basics in place. Better home items can expand what you can do here later.</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/25 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Faction Zone</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">{{ $character->faction->name }}</p>
                        <p class="mt-2 text-sm text-white/60">Your shelter is tied to your current faction and character progression.</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/25 p-4">
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Current Role</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">{{ $character->displayed_job_name }}</p>
                        <p class="mt-2 text-sm text-white/60">Work, recovery, and inventory systems can all eventually branch out from here.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Home Assets</p>
                        <p class="mt-2 text-sm text-white/60">These are the items currently qualifying your character for home management.</p>
                    </div>
                    <span class="rounded-full border border-[#7ead59]/30 bg-[#7ead59]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Upgradeable later</span>
                </div>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($homeItems as $item)
                        <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-black/20 p-4">
                            <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-[#f4ecd0]">{{ $item->name }}</p>
                            <p class="mt-2 text-sm leading-6 text-white/65">{{ $item->description }}</p>
                            <p class="mt-3 text-xs uppercase tracking-[0.2em] text-[#7ead59]">Owned: {{ $item->pivot->quantity }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">At Home</p>
                        <p class="mt-2 text-sm text-white/60">A quick read on the character stats you will eventually manage through shelter systems.</p>
                    </div>
                    <span class="rounded-full border border-white/10 bg-black/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Future recovery hub</span>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Health</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $character->health_points }}/100</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Stamina</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $character->stamina_points }}/100</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Armor</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ $character->armor_points }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Credits</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase">{{ number_format($character->plastic_credits) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Stored Inventory</p>
                        <p class="mt-2 text-sm text-white/60">Everything your character currently owns, including home assets and general supplies.</p>
                    </div>
                    <span class="rounded-full border border-white/10 bg-black/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/60">{{ $character->inventory->sum(fn ($item) => (int) $item->pivot->quantity) }} total items</span>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse ($character->inventory as $item)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                                    <p class="text-sm text-white/70">{{ $item->description }}</p>
                                </div>
                                @if ($item->is_home)
                                    <span class="rounded-full border border-[#7ead59]/30 bg-[#7ead59]/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#d7edc7]">Home</span>
                                @endif
                            </div>
                            <p class="mt-3 text-xs uppercase tracking-[0.22em] text-[#c2a84f]">Qty {{ $item->pivot->quantity }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No stored items yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[2rem] border border-dashed border-white/10 bg-white/[0.03] p-6 shadow-2xl shadow-black/20">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Coming Next</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Recovery Actions</p>
                        <p class="mt-2 text-sm leading-6 text-white/65">Sleep, food, and drink systems can restore depleted stats from one place.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Shelter Upgrades</p>
                        <p class="mt-2 text-sm leading-6 text-white/65">Higher-tier homes can later unlock stronger bonuses, storage, or crafting-style actions.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
