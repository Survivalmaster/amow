<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Faction Store</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ number_format($character->plastic_credits) }} Plastic Credits available</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Items</p>
            <div class="mt-5 space-y-4">
                @foreach ($items as $item)
                    <form method="POST" action="{{ route('store.purchase') }}" class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        @csrf
                        <input type="hidden" name="purchase_type" value="item">
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                                <p class="text-sm text-white/70">{{ $item->description }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-['Teko'] text-3xl uppercase text-[#7ead59]">{{ number_format($item->price) }}</p>
                                <p class="text-xs uppercase tracking-[0.22em] text-white/45">Credits</p>
                            </div>
                        </div>
                        <button class="mt-4 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Purchase Item</button>
                    </form>
                @endforeach
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Licences</p>
            <div class="mt-5 space-y-4">
                @foreach ($licences as $licence)
                    <form method="POST" action="{{ route('store.purchase') }}" class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        @csrf
                        <input type="hidden" name="purchase_type" value="licence">
                        <input type="hidden" name="id" value="{{ $licence->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $licence->name }}</p>
                                <p class="text-sm text-white/70">{{ $licence->description }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-['Teko'] text-3xl uppercase text-[#7ead59]">{{ number_format($licence->cost) }}</p>
                                <p class="text-xs uppercase tracking-[0.22em] text-white/45">Credits</p>
                            </div>
                        </div>
                        <button class="mt-4 rounded-full bg-[#c2a84f] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Purchase Licence</button>
                    </form>
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
