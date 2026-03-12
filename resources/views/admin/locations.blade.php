<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Locations</p></x-slot>

    @include('admin.partials.nav')

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
                <form method="POST" action="{{ route('admin.locations.update', $location) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4">
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="city_id" required>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected($location->city_id === $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $location->name }}" required>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $location->slug }}" required>
                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" required>{{ $location->description }}</textarea>
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                            <option value="">No rank requirement</option>
                            @foreach ($ranks as $rank)
                                <option value="{{ $rank->id }}" @selected($location->required_rank_id === $rank->id)>{{ $rank->name }}</option>
                            @endforeach
                        </select>
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_licence_id">
                            <option value="">No licence requirement</option>
                            @foreach ($licences as $licence)
                                <option value="{{ $licence->id }}" @selected($location->required_licence_id === $licence->id)>{{ $licence->name }}</option>
                            @endforeach
                        </select>
                        <label class="flex items-center gap-3 text-sm text-white/70"><input type="checkbox" name="is_public" value="1" @checked($location->is_public)> Public</label>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Update</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>
