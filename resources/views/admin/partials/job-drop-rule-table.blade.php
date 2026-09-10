<div class="overflow-hidden rounded-xl border border-white/10 bg-black/20">
    <div class="divide-y divide-white/10">
        <template x-for="(row, index) in rows" :key="row.key">
            <div>
                <div class="flex items-center justify-between gap-3 p-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-lg text-[#d7edc7]">
                            <i :class="itemFor(row)?.icon_class || 'fa-solid fa-box'"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-white" x-text="itemFor(row)?.name || 'Select item'"></p>
                            <p class="mt-1 truncate text-xs text-white/38">
                                <span x-text="itemFor(row)?.slug || 'No item selected'"></span>
                                <span class="text-white/22"> | </span>
                                <span x-text="`Tier ${row.min_tier}-${row.max_tier}`"></span>
                                <span class="text-white/22"> | </span>
                                <span x-text="`${row.min_quantity}-${row.max_quantity} qty`"></span>
                                <span class="text-white/22"> | </span>
                                <span x-text="`${row.drop_chance_percent}%`"></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" @click="row.editing = !row.editing" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/75 transition hover:border-[#7ead59]/35 hover:text-[#d7edc7]" title="Edit drop">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button type="button" @click="removeRule(index)" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f]" title="Remove drop">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div x-show="row.editing" class="grid gap-3 border-t border-white/10 bg-black/15 p-3 lg:grid-cols-[minmax(12rem,1.4fr)_repeat(5,minmax(5rem,0.7fr))]">
                    <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                        <span>Item</span>
                        <select class="{{ $fieldClass }} w-full" x-model="row.item_id" :name="`drop_rules[${index}][item_id]`">
                            <option value="">Select item</option>
                            @foreach ($dropItems as $dropItem)
                                <option value="{{ $dropItem->id }}">{{ $dropItem->name }} ({{ $dropItem->slug }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                        <span>Tier From</span>
                        <input class="{{ $fieldClass }} w-full" type="number" min="0" max="20" x-model="row.min_tier" :name="`drop_rules[${index}][min_tier]`">
                    </label>
                    <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                        <span>Tier To</span>
                        <input class="{{ $fieldClass }} w-full" type="number" min="0" max="20" x-model="row.max_tier" :name="`drop_rules[${index}][max_tier]`">
                    </label>
                    <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                        <span>Min Qty</span>
                        <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.min_quantity" :name="`drop_rules[${index}][min_quantity]`">
                    </label>
                    <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                        <span>Max Qty</span>
                        <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.max_quantity" :name="`drop_rules[${index}][max_quantity]`">
                    </label>
                    <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                        <span>Chance %</span>
                        <input class="{{ $fieldClass }} w-full" type="number" min="0" max="100" step="0.01" x-model="row.drop_chance_percent" :name="`drop_rules[${index}][drop_chance_percent]`">
                    </label>
                </div>
            </div>
        </template>
    </div>
</div>
