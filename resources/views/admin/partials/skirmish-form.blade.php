<form method="POST" action="{{ $action }}" class="grid gap-4 p-5 md:grid-cols-2">
    @csrf
    @if ($method)
        @method($method)
    @endif
    <label class="{{ $labelClass }}">
        <span>Title</span>
        <input class="{{ $fieldClass }} w-full" name="title" value="{{ old('title', $skirmish?->title) }}" required>
    </label>
    <label class="{{ $labelClass }}">
        <span>Slug</span>
        <input class="{{ $fieldClass }} w-full" name="slug" value="{{ old('slug', $skirmish?->slug) }}" required>
    </label>
    <label class="{{ $labelClass }}">
        <span>Status</span>
        <select class="{{ $fieldClass }} w-full" name="status" required>
            @foreach (['draft', 'open', 'active', 'resolved', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $skirmish?->status ?? 'draft') === $status)>{{ str($status)->title() }}</option>
            @endforeach
        </select>
    </label>
    <label class="{{ $labelClass }}">
        <span>Starts At</span>
        <input class="{{ $fieldClass }} w-full" name="starts_at" type="datetime-local" value="{{ old('starts_at', $skirmish?->starts_at?->format('Y-m-d\TH:i')) }}">
    </label>
    <label class="{{ $labelClass }}">
        <span>Ends At</span>
        <input class="{{ $fieldClass }} w-full" name="ends_at" type="datetime-local" value="{{ old('ends_at', $skirmish?->ends_at?->format('Y-m-d\TH:i')) }}">
    </label>
    <label class="{{ $labelClass }} md:col-span-2">
        <span>Description</span>
        <textarea class="{{ $fieldClass }} min-h-28 w-full" name="description">{{ old('description', $skirmish?->description) }}</textarea>
    </label>
    <div class="flex justify-end gap-2 border-t border-white/10 pt-3 md:col-span-2">
        <button type="button" @click="{{ $close }}" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
        <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Save</button>
    </div>
</form>
