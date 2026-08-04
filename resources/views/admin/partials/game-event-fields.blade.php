@php($event ??= null)
@php($endsAtValue = old('ends_at', $event?->ends_at?->format('Y-m-d\TH:i')))

<label class="{{ $labelClass }}">
    <span>Title</span>
    <input class="{{ $fieldClass }} w-full" name="title" value="{{ old('title', $event?->title) }}" required>
</label>

<label class="{{ $labelClass }}">
    <span>Faction Scope</span>
    <select class="{{ $fieldClass }} w-full" name="faction_id">
        <option value="">Global event</option>
        @foreach ($factions as $faction)
            <option value="{{ $faction->id }}" @selected((int) old('faction_id', $event?->faction_id) === $faction->id)>{{ $faction->name }}</option>
        @endforeach
    </select>
</label>

<label class="{{ $labelClass }}">
    <span>Deadline</span>
    <input class="{{ $fieldClass }} w-full" name="ends_at" type="datetime-local" value="{{ $endsAtValue }}">
</label>

<label class="{{ $toggleClass }}">
    <input type="checkbox" name="is_enabled" value="1" class="accent-[#7ead59]" @checked(old('is_enabled', $event?->is_enabled ?? true))>
    Enabled and visible
</label>

<label class="{{ $labelClass }} lg:col-span-2">
    <span>Event Details</span>
    <textarea class="{{ $fieldClass }} min-h-28 w-full" name="body" required>{{ old('body', $event?->body) }}</textarea>
</label>

<div class="grid gap-3 md:grid-cols-2">
    <label class="{{ $toggleClass }}">
        <input type="checkbox" name="xp_multiplier_enabled" value="1" class="accent-[#7ead59]" x-model="xpBoost" @checked(old('xp_multiplier_enabled', $event?->xp_multiplier_enabled ?? false))>
        XP multiplier
    </label>
    <input class="{{ $fieldClass }} w-full disabled:opacity-40" name="xp_multiplier" type="number" min="1" max="5" step="0.1" value="{{ old('xp_multiplier', $event?->xp_multiplier ?? 2) }}" :disabled="!xpBoost">
</div>

<div class="grid gap-3 md:grid-cols-2">
    <label class="{{ $toggleClass }}">
        <input type="checkbox" name="credit_multiplier_enabled" value="1" class="accent-[#7ead59]" x-model="creditBoost" @checked(old('credit_multiplier_enabled', $event?->credit_multiplier_enabled ?? false))>
        Credit multiplier
    </label>
    <input class="{{ $fieldClass }} w-full disabled:opacity-40" name="credit_multiplier" type="number" min="1" max="5" step="0.1" value="{{ old('credit_multiplier', $event?->credit_multiplier ?? 2) }}" :disabled="!creditBoost">
</div>
