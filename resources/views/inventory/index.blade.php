<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Inventory</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Runescape-style slot management for everything your character is carrying.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Carry Load</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Used Slots</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase text-[#f4ecd0]">{{ $character->inventorySlotsUsed() }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Max Slots</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase text-[#d7edc7]">{{ $character->inventorySlotCapacity() }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Free Slots</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase text-[#7ead59]">{{ $character->inventorySlotsRemaining() }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Building Items</p>
                        <p class="mt-2 font-['Teko'] text-4xl uppercase text-[#c2a84f]">{{ $character->buildingItems()->sum(fn ($item) => (int) $item->pivot->quantity) }}</p>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-7 text-white/65">Base capacity is 12 slots. Backpack items add more room. Item quantities split across slots based on each item's max-per-slot setting.</p>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Capacity Boosts</p>
                <div class="mt-4 space-y-3">
                    @php($capacityItems = $character->inventory->filter(fn ($item) => $item->inventory_slot_bonus > 0))
                    @forelse ($capacityItems as $item)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-black/30 text-lg text-[#d7edc7]">
                                    <i class="{{ $item->display_icon_class }}"></i>
                                </span>
                                <div>
                                    <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                                    <p class="text-sm text-white/60">{{ $item->description }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">+{{ $item->inventory_slot_bonus * max(1, (int) $item->pivot->quantity) }} slots</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No backpack or capacity items equipped yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Inventory Grid</p>
                    <p class="mt-2 text-sm text-white/60">A compact slot layout inspired by classic RPG inventory pages.</p>
                </div>
                <a href="{{ route('store.index') }}" class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Visit Marketplace</a>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-4 xl:grid-cols-4">
                @foreach ($slots as $slot)
                    <div class="aspect-square rounded-[1.4rem] border border-white/10 bg-[linear-gradient(180deg,rgba(8,12,10,0.96),rgba(19,28,23,0.92))] p-2 shadow-inner shadow-black/30">
                        @if ($slot)
                            <div class="flex h-full flex-col rounded-[1rem] border border-[#7ead59]/20 bg-black/20 p-2">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-black/35 text-lg text-[#d7edc7]">
                                        <i class="{{ $slot->display_icon_class }}"></i>
                                    </span>
                                    <span class="rounded-full bg-[#c2a84f]/15 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#f4ecd0]">x{{ $slot->slot_stack_quantity ?? $slot->pivot->quantity }}</span>
                                </div>
                                <div class="mt-3 min-h-0 flex-1">
                                    <p class="font-['Teko'] text-xl uppercase leading-none tracking-[0.05em] text-[#f4ecd0]">{{ $slot->name }}</p>
                                    <p class="mt-2 line-clamp-3 text-xs leading-5 text-white/58">{{ $slot->description }}</p>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @if ($slot->is_building)
                                        <span class="rounded-full border border-[#7ead59]/30 bg-[#7ead59]/10 px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.16em] text-[#d7edc7]">Building</span>
                                    @endif
                                    @if ($slot->inventory_slot_bonus > 0)
                                        <span class="rounded-full border border-[#c2a84f]/30 bg-[#c2a84f]/10 px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.16em] text-[#f4ecd0]">+{{ $slot->inventory_slot_bonus }}</span>
                                    @endif
                                    @if (($slot->slot_stack_max ?? $slot->max_stack_per_slot ?? 1) > 1)
                                        <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-[9px] font-semibold uppercase tracking-[0.16em] text-white/55">Max {{ $slot->slot_stack_max ?? $slot->max_stack_per_slot }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="flex h-full flex-col items-center justify-center rounded-[1rem] border border-dashed border-white/10 bg-black/10 text-center">
                                <i class="fa-regular fa-square text-xl text-white/18"></i>
                                <p class="mt-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/28">Empty Slot</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
