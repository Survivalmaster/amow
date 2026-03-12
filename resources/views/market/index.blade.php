<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Plastica Stock Market</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ number_format($character->plastic_credits) }} Plastic Credits liquid</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Listed Companies</p>
            <div class="mt-5 space-y-4">
                @foreach ($companies as $company)
                    <div class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $company->name }}</p>
                                <p class="mt-2 text-sm text-white/70">{{ $company->description }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-['Teko'] text-4xl uppercase text-[#7ead59]">{{ number_format($company->current_price, 2) }}</p>
                                <p class="text-xs uppercase tracking-[0.22em] text-white/45">Share price</p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <form method="POST" action="{{ route('market.buy', $company) }}" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                @csrf
                                <label class="text-xs uppercase tracking-[0.22em] text-white/45">Buy shares
                                    <input class="mt-2 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" min="1" name="shares" value="1" required>
                                </label>
                                <button class="mt-3 w-full rounded-full bg-[#7ead59] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Buy</button>
                            </form>
                            <form method="POST" action="{{ route('market.sell', $company) }}" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                @csrf
                                <label class="text-xs uppercase tracking-[0.22em] text-white/45">Sell shares
                                    <input class="mt-2 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" min="1" name="shares" value="1" required>
                                </label>
                                <button class="mt-3 w-full rounded-full bg-[#c2a84f] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Sell</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Your Holdings</p>
            <div class="mt-5 space-y-4">
                @forelse ($character->holdings as $holding)
                    <div class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $holding->company->name }}</p>
                        <p class="mt-2 text-sm text-white/70">{{ number_format($holding->shares) }} shares</p>
                        <p class="text-xs uppercase tracking-[0.22em] text-white/45">Average buy price {{ number_format($holding->average_buy_price, 2) }}</p>
                    </div>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 px-4 py-10 text-center text-sm uppercase tracking-[0.2em] text-white/45">
                        No holdings yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
