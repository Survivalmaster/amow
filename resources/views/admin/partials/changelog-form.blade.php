@php($fieldClass = 'rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-white outline-none focus:border-[#7ead59]/60')

@if ($method)
    @method($method)
@endif
@csrf

<div class="space-y-6 p-6">
<div class="grid gap-5 xl:grid-cols-2">
    <label class="grid gap-2 text-sm text-white/70">
        <span class="uppercase tracking-[0.18em] text-white/45">Version</span>
        <input class="{{ $fieldClass }}" name="version" value="{{ old('version', $changelog?->version ?? $nextVersion ?? '0.0.1') }}" placeholder="0.0.1" required>
    </label>
    <label class="grid gap-2 text-sm text-white/70">
        <span class="uppercase tracking-[0.18em] text-white/45">State</span>
        <div class="{{ $fieldClass }} flex min-h-[3.2rem] items-center">
            {{ str($changelog?->status ?? 'draft')->title() }}
        </div>
    </label>
    <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
        <span class="uppercase tracking-[0.18em] text-white/45">Title</span>
        <input class="{{ $fieldClass }}" name="title" value="{{ old('title', $changelog?->title) }}" placeholder="Discord transcripts and quality-of-life updates" required>
    </label>
    <label class="grid gap-2 text-sm text-white/70">
        <span class="uppercase tracking-[0.18em] text-white/45">Release Date</span>
        <input class="{{ $fieldClass }}" name="released_at" type="datetime-local" value="{{ old('released_at', $changelog?->released_at?->format('Y-m-d\TH:i') ?? $defaultReleasedAt ?? now()->format('Y-m-d\TH:i')) }}">
    </label>
    <label class="grid gap-2 text-sm text-white/70">
        <span class="uppercase tracking-[0.18em] text-white/45">Discord Changelog Channel ID</span>
        <span class="text-xs text-white/45">Paste the Discord channel ID where released changelogs should post.</span>
        <input class="{{ $fieldClass }}" name="discord_channel_id" inputmode="numeric" value="{{ old('discord_channel_id', $changelog?->discord_channel_id ?? $defaultDiscordChannelId ?? '') }}" placeholder="123456789012345678">
    </label>
    <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
        <span class="uppercase tracking-[0.18em] text-white/45">Summary</span>
        <textarea class="{{ $fieldClass }} min-h-24" name="summary">{{ old('summary', $changelog?->summary) }}</textarea>
    </label>
    <div class="grid gap-5 lg:col-span-2 xl:grid-cols-3">
        <label class="grid gap-2 text-sm text-white/70">
            <span class="uppercase tracking-[0.18em] text-[#d7edc7]">Added</span>
            <span class="text-xs text-white/45">One new feature per line.</span>
            <textarea class="{{ $fieldClass }} min-h-40 font-mono text-sm" name="added_features_text" placeholder="New transcript export command">{{ old('added_features_text', implode("\n", $changelog?->added_features ?? $changelog?->features ?? [])) }}</textarea>
        </label>
        <label class="grid gap-2 text-sm text-white/70">
            <span class="uppercase tracking-[0.18em] text-[#f4d77a]">Edited</span>
            <span class="text-xs text-white/45">One changed item per line.</span>
            <textarea class="{{ $fieldClass }} min-h-40 font-mono text-sm" name="edited_features_text" placeholder="Improved admin navigation">{{ old('edited_features_text', implode("\n", $changelog?->edited_features ?? [])) }}</textarea>
        </label>
        <label class="grid gap-2 text-sm text-white/70">
            <span class="uppercase tracking-[0.18em] text-[#f0b29f]">Removed</span>
            <span class="text-xs text-white/45">One removed item per line.</span>
            <textarea class="{{ $fieldClass }} min-h-40 font-mono text-sm" name="removed_features_text" placeholder="Removed old placeholder text">{{ old('removed_features_text', implode("\n", $changelog?->removed_features ?? [])) }}</textarea>
        </label>
    </div>
    <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
        <span class="uppercase tracking-[0.18em] text-white/45">Summary</span>
        <textarea class="{{ $fieldClass }} min-h-40" name="body">{{ old('body', $changelog?->body) }}</textarea>
    </label>
</div>

<div class="flex justify-end gap-3">
    <button type="button" @click="{{ $close }}" class="rounded-full border border-white/10 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white/70">Cancel</button>
    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">Save Changelog</button>
</div>
</div>
