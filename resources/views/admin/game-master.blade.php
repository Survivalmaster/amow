<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Game Master</p></x-slot>

    @include('admin.partials.nav')

    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="mb-5">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Create Event</p>
                <p class="text-sm text-white/55">Active events appear beneath the header on every non-admin page.</p>
            </div>

            <form method="POST" action="{{ route('admin.game-master.events.store') }}" class="grid gap-4 lg:grid-cols-2">
                @csrf
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Title</span>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="title" value="{{ old('title') }}" required>
                </label>
                <label class="grid gap-2 text-sm text-white/70">
                    <span class="uppercase tracking-[0.18em] text-white/45">Faction Scope</span>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                        <option value="">Global event</option>
                        @foreach ($factions as $faction)
                            <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                    <span class="uppercase tracking-[0.18em] text-white/45">Event Details</span>
                    <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="body" required>{{ old('body') }}</textarea>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                    <input type="checkbox" name="is_enabled" value="1" checked>
                    Enabled and visible
                </label>
                <div class="flex items-end">
                    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Event</button>
                </div>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($events as $event)
                <form method="POST" action="{{ route('admin.game-master.events.update', $event) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 lg:grid-cols-2">
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Title</span>
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="title" value="{{ $event->title }}" required>
                        </label>
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Faction Scope</span>
                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                                <option value="">Global event</option>
                                @foreach ($factions as $faction)
                                    <option value="{{ $faction->id }}" @selected($event->faction_id === $faction->id)>{{ $faction->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                            <span class="uppercase tracking-[0.18em] text-white/45">Event Details</span>
                            <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="body" required>{{ $event->body }}</textarea>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                            <input type="checkbox" name="is_enabled" value="1" @checked($event->is_enabled)>
                            Enabled and visible
                        </label>
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/40">Created by {{ $event->creator->name }}{{ $event->faction ? ' | '.$event->faction->name : ' | Global' }}</p>
                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save Event</button>
                        </div>
                    </div>
                </form>
            @empty
                <div class="rounded-[2rem] border border-dashed border-white/10 bg-white/[0.03] p-6 text-sm text-white/55">No events created yet.</div>
            @endforelse
        </section>
    </div>
</x-app-layout>
