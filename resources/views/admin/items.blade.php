<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Items</p></x-slot>
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
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                    <p class="text-sm text-white/70">{{ $item->description }}</p>
                    <p class="mt-2 text-xs uppercase tracking-[0.22em] text-white/45">{{ ucfirst($item->type) }} • {{ number_format($item->price) }} credits</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
