<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Items</p></x-slot>

    @include('admin.partials.nav')

    <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <form method="POST" action="{{ route('admin.items.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <div class="grid gap-4">
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <textarea class="min-h-32 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Description" required></textarea>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="type" placeholder="Type" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="price" placeholder="Price" required>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                    <option value="">No rank requirement</option>
                    @foreach ($ranks as $rank)
                        <option value="{{ $rank->id }}">{{ $rank->name }}</option>
                    @endforeach
                </select>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_role_type">
                    <option value="">Any role</option>
                    <option value="civilian">Civilian</option>
                    <option value="military">Military</option>
                </select>
                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_licence_id">
                    <option value="">No licence requirement</option>
                    @foreach ($licences as $licence)
                        <option value="{{ $licence->id }}">{{ $licence->name }}</option>
                    @endforeach
                </select>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="stock" placeholder="Stock">
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Item</button>
            </div>
        </form>
        <div class="space-y-4">
            @foreach ($items as $item)
                <form method="POST" action="{{ route('admin.items.update', $item) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4">
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $item->name }}" required>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $item->slug }}" required>
                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" required>{{ $item->description }}</textarea>
                        <div class="grid gap-4 md:grid-cols-2">
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="type" value="{{ $item->type }}" required>
                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="price" value="{{ $item->price }}" required>
                        </div>
                        <div class="grid gap-4 md:grid-cols-3">
                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_rank_id">
                                <option value="">No rank requirement</option>
                                @foreach ($ranks as $rank)
                                    <option value="{{ $rank->id }}" @selected($item->required_rank_id === $rank->id)>{{ $rank->name }}</option>
                                @endforeach
                            </select>
                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_role_type">
                                <option value="">Any role</option>
                                <option value="civilian" @selected($item->required_role_type === 'civilian')>Civilian</option>
                                <option value="military" @selected($item->required_role_type === 'military')>Military</option>
                            </select>
                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_licence_id">
                                <option value="">No licence requirement</option>
                                @foreach ($licences as $licence)
                                    <option value="{{ $licence->id }}" @selected($item->required_licence_id === $licence->id)>{{ $licence->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="stock" value="{{ $item->stock }}" placeholder="Stock">
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Update</button>
                    </div>
                </form>
            @endforeach
        </div>
    </div>
</x-app-layout>
