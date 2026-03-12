<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $character->name }}</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ $character->faction->name }} • {{ $character->rank->name }}</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Character File</p>
                <div class="mt-4 space-y-3 text-sm text-white/70">
                    <p><span class="text-white/45">Age:</span> {{ $character->age }}</p>
                    <p><span class="text-white/45">Role:</span> {{ ucfirst($character->role_type) }}</p>
                    <p><span class="text-white/45">Occupation:</span> {{ $character->starting_occupation }}</p>
                    <p><span class="text-white/45">Credits:</span> {{ number_format($character->plastic_credits) }}</p>
                    <p><span class="text-white/45">Business owner:</span> {{ $character->is_business_owner ? 'Yes' : 'No' }}</p>
                </div>
                <p class="mt-5 text-sm leading-7 text-white/70">{{ $character->biography }}</p>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Licences</p>
                <div class="mt-4 space-y-3">
                    @forelse ($character->licences as $licence)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $licence->name }}</p>
                            <p class="text-sm text-white/70">{{ $licence->description }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No licences acquired.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Inventory</p>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse ($character->inventory as $item)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $item->name }}</p>
                            <p class="text-sm text-white/70">{{ $item->description }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.22em] text-[#c2a84f]">Qty {{ $item->pivot->quantity }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/45">No items in inventory.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Transactions</p>
                <div class="mt-4 space-y-3">
                    @foreach ($character->transactions as $transaction)
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ str_replace('_', ' ', $transaction->type) }}</p>
                                <p class="font-['Teko'] text-2xl uppercase {{ $transaction->amount >= 0 ? 'text-[#7ead59]' : 'text-[#c65b3f]' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount) }}</p>
                            </div>
                            <p class="text-sm text-white/70">{{ $transaction->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
