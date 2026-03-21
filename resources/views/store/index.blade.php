<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Faction Store</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ number_format($character->plastic_credits) }} Plastic Credits available</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Licences</p>
                <p class="mt-2 text-sm text-white/60">Land is now the unlock for your personal plot. Once purchased, you can place tents and other buildings on a 10x10 grid.</p>
                <div class="mt-5 space-y-4">
                    @foreach ($licences as $licence)
                        @php($owned = $character->licences->contains('id', $licence->id))
                        <form method="POST" action="{{ route('store.purchase') }}" class="rounded-3xl border border-white/10 bg-black/20 p-4">
                            @csrf
                            <input type="hidden" name="purchase_type" value="licence">
                            <input type="hidden" name="id" value="{{ $licence->id }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $licence->name }}</p>
                                        @if ($licence->slug === 'land')
                                            <span class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Unlocks Land</span>
                                        @endif
                                        @if ($owned)
                                            <span class="rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/12 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#f4ecd0]">Owned</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm text-white/70">{{ $licence->description }}</p>
                                    <p class="mt-2 text-xs uppercase tracking-[0.2em] text-white/40">{{ $licence->requiredRank?->name ?? 'No rank requirement' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-['Teko'] text-3xl uppercase text-[#7ead59]">{{ number_format($licence->cost) }}</p>
                                    <p class="text-xs uppercase tracking-[0.22em] text-white/45">Credits</p>
                                </div>
                            </div>
                            <button class="mt-4 rounded-full bg-[#c2a84f] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]" @disabled($owned)>Purchase Licence</button>
                        </form>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Buildings</p>
                <p class="mt-2 text-sm text-white/60">Building items stay in inventory until placed on your land. Once placed, construction begins immediately.</p>
                <div class="mt-5 space-y-4">
                    @foreach ($buildingItems as $item)
                        <form method="POST" action="{{ route('store.purchase') }}" class="rounded-3xl border border-white/10 bg-black/20 p-4">
                            @csrf
                            <input type="hidden" name="purchase_type" value="item">
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                                        <span class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">{{ $item->footprint_width }}x{{ $item->footprint_height }}</span>
                                        @if ($item->build_time_minutes > 0)
                                            <span class="rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/12 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#f4ecd0]">{{ $item->build_time_minutes }} min build</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex items-start gap-3">
                                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-black/30 text-lg text-[#d7edc7]">
                                            <i class="{{ $item->display_icon_class }}"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm text-white/70">{{ $item->description }}</p>
                                            <p class="mt-2 text-xs uppercase tracking-[0.2em] text-white/40">{{ $item->type }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-['Teko'] text-3xl uppercase text-[#7ead59]">{{ number_format($item->price) }}</p>
                                    <p class="text-xs uppercase tracking-[0.22em] text-white/45">Credits</p>
                                </div>
                            </div>
                            <button class="mt-4 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Purchase Building</button>
                        </form>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Items</p>
                <p class="mt-2 text-sm text-white/60">General equipment, tools, trade gear, and anything else your character carries.</p>
                <div class="mt-4 rounded-[1.5rem] border border-white/10 bg-black/20 px-4 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-white/45">Inventory Capacity</p>
                            <p class="mt-1 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">{{ $character->inventorySlotsUsed() }}/{{ $character->inventorySlotCapacity() }}</p>
                        </div>
                        <p class="max-w-xs text-right text-sm text-white/60">Base capacity is 12 slots. Backpack items increase that cap for your character.</p>
                    </div>
                </div>
                <div class="mt-5 space-y-4">
                    @foreach ($gearItems as $item)
                        <form method="POST" action="{{ route('store.purchase') }}" class="rounded-3xl border border-white/10 bg-black/20 p-4">
                            @csrf
                            <input type="hidden" name="purchase_type" value="item">
                            <input type="hidden" name="id" value="{{ $item->id }}">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                                        @if ($item->inventory_slot_bonus > 0)
                                            <span class="rounded-full border border-[#c2a84f]/35 bg-[#c2a84f]/12 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-[#f4ecd0]">+{{ $item->inventory_slot_bonus }} Slots</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 flex items-start gap-3">
                                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-black/30 text-lg text-[#d7edc7]">
                                            <i class="{{ $item->display_icon_class }}"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm text-white/70">{{ $item->description }}</p>
                                            <p class="mt-2 text-xs uppercase tracking-[0.2em] text-white/40">{{ $item->type }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-['Teko'] text-3xl uppercase text-[#7ead59]">{{ number_format($item->price) }}</p>
                                    <p class="text-xs uppercase tracking-[0.22em] text-white/45">Credits</p>
                                </div>
                            </div>
                            <button class="mt-4 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Purchase Item</button>
                        </form>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
