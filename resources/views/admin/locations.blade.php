<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Locations</p></x-slot>
    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <form method="POST" action="{{ route('admin.locations.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <div class="grid gap-4">
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="city_id" required>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <textarea class="min-h-32 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Description" required></textarea>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                    <option value="">No rank requirement</option>
                    @foreach ($ranks as $rank)
                        <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                    @endforeach
                </select>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_licence_id">
                    <option value="">No licence requirement</option>
                    @foreach ($licences as $licence)
                        <option value="{{ $licence->id }}">{{ $licence->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-3 text-sm text-white/70"><input type="checkbox" name="is_public" value="1" checked> Public</label>
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Location</button>
            </div>
        </form>
        <div class="space-y-4">
            @foreach ($locations as $location)
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $location->name }}</p>
                    <p class="text-xs uppercase tracking-[0.22em] text-white/45">{{ $location->city->name }}</p>
                    <p class="mt-2 text-sm text-white/70">{{ $location->description }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
