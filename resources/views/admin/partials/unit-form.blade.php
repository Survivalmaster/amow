<form method="POST" action="{{ $action }}" class="grid gap-4 p-5 md:grid-cols-2">
    @csrf
    @if ($method)
        @method($method)
    @endif
    <label class="{{ $labelClass }}"><span>Name</span><input class="{{ $fieldClass }} w-full" name="name" value="{{ old('name', $unit?->name) }}" required></label>
    <label class="{{ $labelClass }}"><span>Slug</span><input class="{{ $fieldClass }} w-full" name="slug" value="{{ old('slug', $unit?->slug) }}" required></label>
    <label class="{{ $labelClass }}"><span>Category</span><input class="{{ $fieldClass }} w-full" name="category" value="{{ old('category', $unit?->category ?? 'infantry') }}" placeholder="infantry, armour, air"></label>
    <label class="{{ $labelClass }}"><span>Cost</span><input class="{{ $fieldClass }} w-full" name="cost" type="number" min="0" value="{{ old('cost', $unit?->cost ?? 0) }}" required></label>
    <label class="{{ $labelClass }}"><span>Firepower</span><input class="{{ $fieldClass }} w-full" name="firepower" type="number" min="0" value="{{ old('firepower', $unit?->firepower ?? 0) }}" required></label>
    <label class="{{ $toggleClass }}"><input type="checkbox" name="is_active" value="1" class="accent-[#7ead59]" @checked(old('is_active', $unit?->is_active ?? true))>Active</label>
    <label class="{{ $labelClass }} md:col-span-2"><span>Description</span><textarea class="{{ $fieldClass }} min-h-24 w-full" name="description">{{ old('description', $unit?->description) }}</textarea></label>
    <div class="flex justify-end gap-2 border-t border-white/10 pt-3 md:col-span-2">
        <button type="button" @click="{{ $close }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
        <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Save</button>
    </div>
</form>
