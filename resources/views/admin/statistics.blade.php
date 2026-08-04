@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-admin-statistics]');

            if (!root) return;

            const stateUrl = root.dataset.statisticsStateUrl;
            let isFetching = false;

            const formatNumber = (value) => new Intl.NumberFormat().format(Number(value || 0));
            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
            const maxOf = (items, key) => Math.max(1, ...items.map((item) => Number(item[key] || 0)));

            const renderCards = (items, target) => {
                target.innerHTML = items.map((item) => `
                    <div class="rounded-lg border border-slate-800 bg-slate-950/45 p-5 shadow-xl shadow-black/20">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">${escapeHtml(item.label)}</p>
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 text-slate-300">
                                <i class="fa-solid ${escapeHtml(item.icon || 'fa-chart-simple')}"></i>
                            </span>
                        </div>
                        <p class="mt-4 font-['Teko'] text-4xl leading-none tracking-normal text-slate-50">${formatNumber(item.value)}</p>
                    </div>
                `).join('');
            };

            const renderActivity = (activity) => {
                const target = root.querySelector('[data-stat-activity]');
                const max = maxOf(activity, 'transactions');
                const points = activity.map((item, index) => {
                    const x = activity.length === 1 ? 0 : (index / (activity.length - 1)) * 100;
                    const y = 100 - ((Number(item.transactions || 0) / max) * 82) - 8;
                    return `${x.toFixed(2)},${y.toFixed(2)}`;
                }).join(' ');

                target.innerHTML = `
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="h-64 w-full overflow-visible">
                        <polyline points="${points}" fill="none" stroke="#7ead59" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
                        ${activity.map((item, index) => {
                            const x = activity.length === 1 ? 0 : (index / (activity.length - 1)) * 100;
                            const y = 100 - ((Number(item.transactions || 0) / max) * 82) - 8;
                            return `<circle cx="${x.toFixed(2)}" cy="${y.toFixed(2)}" r="2.4" fill="#f4ecd0"></circle>`;
                        }).join('')}
                    </svg>
                    <div class="mt-3 grid grid-cols-7 gap-2 text-center text-[11px] uppercase tracking-[0.12em] text-slate-500">
                        ${activity.map((item) => `<span>${escapeHtml(item.label)}</span>`).join('')}
                    </div>
                `;
            };

            const renderBars = (items, target, key, suffix = '') => {
                const max = maxOf(items, key);
                target.innerHTML = items.map((item) => {
                    const width = Math.max(4, (Number(item[key] || 0) / max) * 100);
                    return `
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="truncate text-slate-200">${escapeHtml(item.label)}</span>
                                <span class="font-semibold text-slate-400">${formatNumber(item[key])}${suffix}</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full rounded-full" style="width:${width}%; background:${escapeHtml(item.color || '#7ead59')};"></div>
                            </div>
                        </div>
                    `;
                }).join('');
            };

            const renderValueList = (items, target) => {
                target.innerHTML = items.map((item) => `
                    <div class="flex items-center justify-between gap-4 rounded-lg border border-slate-800 bg-slate-950/35 px-4 py-3">
                        <span class="inline-flex items-center gap-3 text-sm text-slate-300">
                            <i class="fa-solid ${escapeHtml(item.icon || 'fa-circle')} w-5 text-center text-slate-500"></i>
                            ${escapeHtml(item.label)}
                        </span>
                        <span class="font-['Teko'] text-2xl leading-none text-slate-100">${formatNumber(item.value)}</span>
                    </div>
                `).join('');
            };

            const renderEconomy = (economy) => {
                const items = [
                    ['Total Earned', economy.earned, '#7ead59'],
                    ['Total Spent', economy.spent, '#f0b29f'],
                    ['Work Earned', economy.work_earned, '#a9d6e5'],
                    ['Marketplace Spend', economy.marketplace_spend, '#c2a84f'],
                    ['Bank Transfers', economy.bank_transfers, '#7ec6ff'],
                    ['Nation Donations', economy.nation_donations, '#d7edc7'],
                    ['Stock Volume', economy.stock_volume, '#b9ddff'],
                    ['Refunds', economy.refunds, '#86efac'],
                ].map(([label, value, color]) => ({ label, value, color }));
                const max = Math.max(1, ...items.map((item) => Number(item.value || 0)));
                const target = root.querySelector('[data-stat-economy]');

                target.innerHTML = items.map((item) => `
                    <div class="rounded-lg border border-slate-800 bg-slate-950/35 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">${escapeHtml(item.label)}</p>
                            <p class="font-['Teko'] text-3xl leading-none text-slate-50">${formatNumber(item.value)}</p>
                        </div>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-800">
                            <div class="h-full rounded-full" style="width:${Math.max(4, (Number(item.value || 0) / max) * 100)}%; background:${item.color};"></div>
                        </div>
                    </div>
                `).join('');
            };

            const renderTerritory = (territory) => {
                const ring = root.querySelector('[data-stat-territory-ring]');
                const types = root.querySelector('[data-stat-territory-types]');
                ring.style.setProperty('--territory-claimed', `${Number(territory.claimed_percent || 0)}%`);
                ring.querySelector('[data-territory-percent]').textContent = `${territory.claimed_percent || 0}%`;
                ring.querySelector('[data-territory-subtitle]').textContent = `${formatNumber(territory.claimed)} / ${formatNumber(territory.total)} claimed`;
                renderBars(territory.types || [], types, 'value');
            };

            const render = (payload) => {
                root.querySelector('[data-stat-generated-at]').textContent = payload.generated_at || '-';
                renderCards(payload.summary || [], root.querySelector('[data-stat-summary]'));
                renderActivity(payload.activity || []);
                renderEconomy(payload.economy || {});
                renderBars(payload.factions || [], root.querySelector('[data-stat-factions]'), 'characters');
                renderValueList(payload.world || [], root.querySelector('[data-stat-world]'));
                renderValueList(payload.content || [], root.querySelector('[data-stat-content]'));
                renderTerritory(payload.territory || {});
            };

            const fetchState = async () => {
                if (isFetching) return;
                isFetching = true;

                try {
                    const response = await fetch(stateUrl, {
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (response.ok) {
                        render(await response.json());
                    }
                } finally {
                    isFetching = false;
                }
            };

            render(JSON.parse(root.dataset.initialStatistics));
            window.setInterval(fetchState, 5000);
        });
    </script>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Statistics</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Live operational totals across players, economy, territory, content, and world activity.</p>
        </div>
    </x-slot>

    <div
        data-admin-statistics
        data-statistics-state-url="{{ route('admin.statistics.state') }}"
        data-initial-statistics='@json($statistics)'
        class="space-y-6"
    >
        <div class="flex items-center justify-between gap-4">
            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                Updates every 5 seconds
            </div>
            <div class="rounded-lg border border-slate-800 bg-slate-950/45 px-4 py-2 text-xs uppercase tracking-[0.16em] text-slate-400">
                Last refresh <span class="text-slate-100" data-stat-generated-at>{{ $statistics['generated_at'] }}</span>
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-stat-summary></section>

        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5 shadow-xl shadow-black/20">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-slate-100">Seven Day Pulse</p>
                        <p class="mt-1 text-sm text-slate-400">Transaction volume by day, refreshed live.</p>
                    </div>
                    <i class="fa-solid fa-wave-square text-2xl text-[#7ead59]"></i>
                </div>
                <div class="mt-5" data-stat-activity></div>
            </section>

            <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5 shadow-xl shadow-black/20">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-slate-100">Territory Control</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-[12rem_minmax(0,1fr)] xl:grid-cols-1">
                    <div
                        data-stat-territory-ring
                        class="grid aspect-square place-items-center rounded-full border border-slate-800"
                        style="background: conic-gradient(#7ead59 0 var(--territory-claimed, 0%), rgba(15,23,42,0.9) var(--territory-claimed, 0%) 100%);"
                    >
                        <div class="grid h-[72%] w-[72%] place-items-center rounded-full bg-slate-950 text-center">
                            <div>
                                <p class="font-['Teko'] text-5xl leading-none text-slate-50" data-territory-percent>0%</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500" data-territory-subtitle>0 / 0 claimed</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4" data-stat-territory-types></div>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5 shadow-xl shadow-black/20">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-slate-100">Economy Movement</p>
                <div class="mt-5 grid gap-3" data-stat-economy></div>
            </section>

            <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5 shadow-xl shadow-black/20">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-slate-100">Faction Spread</p>
                <p class="mt-1 text-sm text-slate-400">Character distribution by nation.</p>
                <div class="mt-5 space-y-4" data-stat-factions></div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5 shadow-xl shadow-black/20">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-slate-100">World Systems</p>
                <div class="mt-5 grid gap-3 md:grid-cols-2" data-stat-world></div>
            </section>

            <section class="rounded-lg border border-slate-800 bg-slate-900/35 p-5 shadow-xl shadow-black/20">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-slate-100">Content & Combat</p>
                <div class="mt-5 grid gap-3 md:grid-cols-2" data-stat-content></div>
            </section>
        </div>
    </div>
</x-app-layout>
