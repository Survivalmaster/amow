@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const marketRoot = document.querySelector('[data-market-root]');

            if (!marketRoot) {
                return;
            }

            const stateUrl = marketRoot.dataset.marketStateUrl;
            let isFetching = false;

            const applyState = (payload) => {
                if (!Array.isArray(payload?.companies)) {
                    return;
                }

                payload.companies.forEach((company) => {
                    const card = marketRoot.querySelector(`[data-company-id="${company.id}"]`);

                    if (!card) {
                        return;
                    }

                    const priceElement = card.querySelector('[data-company-price]');
                    const updatedElement = card.querySelector('[data-company-updated]');

                    if (priceElement) {
                        priceElement.textContent = company.formatted_price;
                    }

                    if (updatedElement && company.last_price_updated_at) {
                        updatedElement.textContent = `Updated ${new Date(company.last_price_updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
                    }
                });
            };

            const fetchState = async () => {
                if (isFetching) {
                    return;
                }

                isFetching = true;

                try {
                    const response = await fetch(stateUrl, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        return;
                    }

                    applyState(await response.json());
                } finally {
                    isFetching = false;
                }
            };

            window.setInterval(fetchState, 10000);
        });
    </script>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Plastica Stock Market</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ number_format($character->plastic_credits) }} Plastic Credits liquid | Prices react to trades and scheduled drift</p>
        </div>
    </x-slot>

    <div data-market-root data-market-state-url="{{ route('market.state') }}" class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Listed Companies</p>
            <p class="mt-2 text-sm text-white/60">Values drift about once a minute. Player buying adds upward pressure, while heavy selling can knock prices down hard.</p>
            <div class="mt-5 space-y-4">
                @foreach ($companies as $company)
                    @php
                        $ownedShares = (int) optional($character->holdings->firstWhere('company_id', $company->id))->shares;
                        $remainingCap = $company->max_shares_per_character === null
                            ? null
                            : max(0, $company->max_shares_per_character - $ownedShares);
                    @endphp
                    <div data-company-id="{{ $company->id }}" class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $company->name }}</p>
                                <p class="mt-2 text-sm text-white/70">{{ $company->description }}</p>
                                <p class="mt-3 text-xs uppercase tracking-[0.18em] text-white/45">
                                    You hold {{ number_format($ownedShares) }}
                                    @if ($company->max_shares_per_character)
                                        / {{ number_format($company->max_shares_per_character) }} shares
                                    @else
                                        shares
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-['Teko'] text-4xl uppercase text-[#7ead59]" data-company-price>{{ number_format($company->current_price, 2) }}</p>
                                <p class="text-xs uppercase tracking-[0.22em] text-white/45">Share price</p>
                                <p class="mt-1 text-[10px] uppercase tracking-[0.18em] text-white/35" data-company-updated>
                                    Updated {{ optional($company->last_price_updated_at)->format('H:i') ?? 'now' }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <form method="POST" action="{{ route('market.buy', $company) }}" class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                @csrf
                                <label class="text-xs uppercase tracking-[0.22em] text-white/45">Buy shares
                                    <input class="mt-2 w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" min="1" @if ($remainingCap !== null) max="{{ min(1000, $remainingCap) }}" @endif name="shares" value="{{ $remainingCap === 0 ? 0 : 1 }}" required @disabled($remainingCap === 0)>
                                </label>
                                <button class="mt-3 w-full rounded-full bg-[#7ead59] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]" @disabled($remainingCap === 0)>{{ $remainingCap === 0 ? 'Limit Reached' : 'Buy' }}</button>
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
