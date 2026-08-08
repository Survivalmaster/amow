<div class="overflow-hidden rounded-xl border border-white/10 bg-black/20">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-white/75">
            <thead class="bg-black/30 text-[10px] uppercase tracking-[0.16em] text-white/40">
                <tr>
                    <th class="px-3 py-3 text-left">Item</th>
                    <th class="w-24 px-3 py-3 text-left">From</th>
                    <th class="w-24 px-3 py-3 text-left">To</th>
                    <th class="w-24 px-3 py-3 text-left">Min</th>
                    <th class="w-24 px-3 py-3 text-left">Max</th>
                    <th class="w-28 px-3 py-3 text-left">Chance</th>
                    <th class="w-14 px-3 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                <template x-for="(row, index) in rows" :key="row.key">
                    <tr class="align-top">
                        <td class="min-w-[18rem] px-3 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-lg text-[#d7edc7]">
                                    <i :class="itemFor(row)?.icon_class || 'fa-solid fa-box'"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <select class="{{ $fieldClass }} w-full" x-model="row.item_id" :name="`drop_rules[${index}][item_id]`">
                                        <option value="">Select item</option>
                                        @foreach ($dropItems as $dropItem)
                                            <option value="{{ $dropItem->id }}">{{ $dropItem->name }} ({{ $dropItem->slug }})</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 truncate text-xs text-white/38" x-text="itemFor(row)?.slug || 'No item selected'"></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <input class="{{ $fieldClass }} w-full" type="number" min="0" max="20" x-model="row.min_tier" :name="`drop_rules[${index}][min_tier]`" title="Tier from">
                        </td>
                        <td class="px-3 py-3">
                            <input class="{{ $fieldClass }} w-full" type="number" min="0" max="20" x-model="row.max_tier" :name="`drop_rules[${index}][max_tier]`" title="Tier to">
                        </td>
                        <td class="px-3 py-3">
                            <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.min_quantity" :name="`drop_rules[${index}][min_quantity]`" title="Minimum quantity">
                        </td>
                        <td class="px-3 py-3">
                            <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.max_quantity" :name="`drop_rules[${index}][max_quantity]`" title="Maximum quantity">
                        </td>
                        <td class="px-3 py-3">
                            <input class="{{ $fieldClass }} w-full" type="number" min="0" max="100" step="0.01" x-model="row.drop_chance_percent" :name="`drop_rules[${index}][drop_chance_percent]`" title="Drop chance percent">
                        </td>
                        <td class="px-3 py-3 text-right">
                            <button type="button" @click="removeRule(index)" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f]" title="Remove drop">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
