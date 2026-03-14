<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Items</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <form method="POST" action="{{ route('admin.items.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            @csrf
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Item</p>
            <p class="mt-2 text-sm text-white/60">Creates an inventory or shop item, including cost, stock, and any role, rank, or licence requirements.</p>
            <div class="mt-5 grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Name" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="Slug" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="type" placeholder="Type" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="price" placeholder="Price" required>
                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="stock" placeholder="Stock">
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
                <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2 xl:col-span-3" name="description" placeholder="Description" required></textarea>
            </div>
            <div class="mt-5 flex justify-end">
                <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Item</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Type</th>
                            <th class="px-5 py-4 text-left">Price</th>
                            <th class="px-5 py-4 text-left">Restrictions</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($items as $item)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-white">{{ $item->name }}</td>
                                <td class="px-5 py-4">{{ $item->type }}</td>
                                <td class="px-5 py-4">{{ number_format($item->price) }}</td>
                                <td class="px-5 py-4">{{ $item->requiredRank?->name ?? 'Any rank' }} | {{ $item->required_role_type ?: 'Any role' }} | {{ $item->requiredLicence?->name ?? 'No licence' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $item->id }} ? null : {{ $item->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.items.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $item->id }}" x-cloak>
                                <td colspan="5" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.items.update', $item) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2 xl:grid-cols-3">
                                        @csrf
                                        @method('PATCH')
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $item->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $item->slug }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="type" value="{{ $item->type }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="price" value="{{ $item->price }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" name="stock" value="{{ $item->stock }}" placeholder="Stock">
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
                                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 lg:col-span-2 xl:col-span-3" name="description" required>{{ $item->description }}</textarea>
                                        <div class="lg:col-span-2 xl:col-span-3 flex justify-end">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
