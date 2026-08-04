@push('styles')
    <style>
        .stats-panel {
            border: 1px solid rgba(51, 65, 85, 0.9);
            border-radius: 0.75rem;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(2, 6, 23, 0.72)),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 34%);
            box-shadow: 0 20px 55px rgba(0, 0, 0, 0.22);
        }

        .stats-kpi {
            border: 1px solid rgba(51, 65, 85, 0.85);
            border-radius: 0.7rem;
            background: rgba(2, 6, 23, 0.58);
        }

        .stats-bar {
            height: 0.45rem;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(30, 41, 59, 0.9);
        }

        .stats-grid-bg {
            background-image:
                linear-gradient(rgba(148, 163, 184, 0.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.055) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .stats-type-chip {
            border: 1px solid rgba(51, 65, 85, 0.8);
            border-radius: 0.65rem;
            background: rgba(2, 6, 23, 0.36);
        }

        .stats-activity-chart {
            border: 1px solid rgba(51, 65, 85, 0.85);
            border-radius: 0.7rem;
            background:
                linear-gradient(rgba(148, 163, 184, 0.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148, 163, 184, 0.055) 1px, transparent 1px),
                rgba(2, 6, 23, 0.38);
            background-size: 28px 28px;
            padding: 0.85rem;
        }

        .stats-day-bars {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: end;
            gap: 0.2rem;
            height: 8.75rem;
        }

        .stats-activity-bar {
            min-height: 0.2rem;
            border-radius: 0.45rem 0.45rem 0.15rem 0.15rem;
        }

        .stats-territory-meter {
            height: 0.85rem;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(30, 41, 59, 0.9);
            outline: 1px solid rgba(51, 65, 85, 0.75);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.querySelector('[data-admin-statistics]');
            const initialState = document.querySelector('[data-initial-statistics-json]');

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
            const maxOf = (items, keys) => Math.max(1, ...items.flatMap((item) => keys.map((key) => Number(item[key] || 0))));
            const widthFor = (value, max) => Math.max(Number(value || 0) > 0 ? 5 : 0, (Number(value || 0) / max) * 100);

            const iconClass = (icon) => icon?.startsWith('fa-') ? `fa-solid ${icon}` : 'fa-solid fa-chart-simple';

            const renderCards = (items) => {
                const target = root.querySelector('[data-stat-summary]');
                target.innerHTML = items.map((item) => `
                    <article class="stats-kpi p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-[11px] font-semibold uppercase text-slate-400">${escapeHtml(item.label)}</span>
                            <i class="${iconClass(item.icon)} text-slate-500"></i>
                        </div>
                        <p class="mt-3 font-['Teko'] text-[2.55rem] leading-none text-slate-50">${formatNumber(item.value)}</p>
                    </article>
                `).join('');
            };

            const renderActivity = (activity) => {
                const chart = root.querySelector('[data-stat-pulse-chart]');
                const legend = root.querySelector('[data-stat-pulse-legend]');
                const series = [
                    ['transactions', '#38bdf8', 'Transactions'],
                    ['messages', '#a78bfa', 'Messages'],
                    ['users', '#facc15', 'Users'],
                    ['characters', '#4ade80', 'Characters'],
                ];
                const max = maxOf(activity, series.map(([key]) => key));

                if (chart) {
                    chart.innerHTML = activity.map((item) => `
                        <div class="min-w-0">
                            <div class="stats-day-bars">
                                ${series.map(([key, color]) => {
                                    const value = Number(item[key] || 0);
                                    const height = value > 0 ? Math.max(8, (value / max) * 100) : 0;

                                    return `<div class="stats-activity-bar" title="${escapeHtml(key)}: ${formatNumber(value)}" style="height:${height}%; background:${color};"></div>`;
                                }).join('')}
                            </div>
                            <p class="mt-2 text-center text-[11px] font-semibold uppercase text-slate-500">${escapeHtml(item.label)}</p>
                        </div>
                    `).join('');
                }

                if (legend) {
                    legend.innerHTML = series.map(([key, color, label]) => {
                        const total = activity.reduce((sum, item) => sum + Number(item[key] || 0), 0);

                        return `
                            <div class="stats-type-chip px-3 py-2">
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase text-slate-400">
                                    <span class="h-2 w-2 rounded-full" style="background:${color}"></span>${label}
                                </div>
                                <p class="mt-1 font-['Teko'] text-2xl leading-none text-slate-50">${formatNumber(total)}</p>
                            </div>
                        `;
                    }).join('');
                }
            };

            const renderEconomy = (economy) => {
                const target = root.querySelector('[data-stat-economy]');
                const rows = [
                    ['Work earned', economy.work_earned, '#4ade80'],
                    ['Marketplace spend', economy.marketplace_spend, '#facc15'],
                    ['Bank transfers', economy.bank_transfers, '#38bdf8'],
                    ['Nation donations', economy.nation_donations, '#a3e635'],
                    ['Stock volume', economy.stock_volume, '#93c5fd'],
                    ['Refunds issued', economy.refunds, '#86efac'],
                ].map(([label, value, color]) => ({ label, value, color }));
                const max = Math.max(1, ...rows.map((row) => Number(row.value || 0)));

                target.innerHTML = rows.map((row) => `
                    <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-sm font-semibold text-slate-300">${escapeHtml(row.label)}</p>
                            <p class="font-['Teko'] text-3xl leading-none text-slate-50">${formatNumber(row.value)}</p>
                        </div>
                        <div class="stats-bar mt-3">
                            <div class="h-full rounded-full" style="width:${widthFor(row.value, max)}%; background:${row.color};"></div>
                        </div>
                    </div>
                `).join('');
            };

            const renderFactionMatrix = (factions) => {
                const target = root.querySelector('[data-stat-factions]');
                const maxCharacters = Math.max(1, ...factions.map((item) => Number(item.characters || 0)));
                const maxCredits = Math.max(1, ...factions.map((item) => Number(item.credits || 0)));

                target.innerHTML = factions.map((item) => `
                    <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full" style="background:${escapeHtml(item.color)}"></span>
                                    <p class="truncate font-semibold text-slate-100">${escapeHtml(item.label)}</p>
                                </div>
                                <p class="mt-1 text-xs uppercase text-slate-500">${formatNumber(item.territory)} territory hexes</p>
                            </div>
                            <p class="font-['Teko'] text-3xl leading-none text-slate-50">${formatNumber(item.characters)}</p>
                        </div>
                        <div class="mt-4 grid gap-3">
                            <div>
                                <div class="flex justify-between text-xs text-slate-500"><span>Characters</span><span>${formatNumber(item.characters)}</span></div>
                                <div class="stats-bar mt-1"><div class="h-full rounded-full" style="width:${widthFor(item.characters, maxCharacters)}%; background:${escapeHtml(item.color)};"></div></div>
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-slate-500"><span>Player credits</span><span>${formatNumber(item.credits)}</span></div>
                                <div class="stats-bar mt-1"><div class="h-full rounded-full bg-sky-300" style="width:${widthFor(item.credits, maxCredits)}%;"></div></div>
                            </div>
                        </div>
                    </div>
                `).join('');
            };

            const renderValueTiles = (items, target) => {
                target.innerHTML = items.map((item) => `
                    <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <span class="inline-flex items-center gap-3 text-sm font-medium text-slate-300">
                                <i class="${iconClass(item.icon)} w-5 text-center text-slate-500"></i>
                                ${escapeHtml(item.label)}
                            </span>
                            <span class="font-['Teko'] text-3xl leading-none text-slate-100">${formatNumber(item.value)}</span>
                        </div>
                    </div>
                `).join('');
            };

            const renderTerritory = (territory) => {
                const meter = root.querySelector('[data-stat-territory-meter]');
                const types = root.querySelector('[data-stat-territory-types]');
                const percent = Number(territory.claimed_percent || 0);
                const max = Math.max(1, ...(territory.types || []).map((item) => Number(item.value || 0)));

                root.querySelector('[data-territory-percent]').textContent = `${percent}%`;
                if (meter) meter.style.width = `${Math.min(100, Math.max(0, percent))}%`;
                root.querySelector('[data-territory-claimed]').textContent = formatNumber(territory.claimed);
                root.querySelector('[data-territory-total]').textContent = formatNumber(territory.total);
                root.querySelector('[data-territory-open]').textContent = formatNumber(Math.max(0, Number(territory.total || 0) - Number(territory.claimed || 0)));

                types.innerHTML = (territory.types || []).length
                    ? (territory.types || []).map((item) => `
                        <div>
                            <div class="flex justify-between gap-3 text-xs text-slate-400"><span>${escapeHtml(item.label)}</span><span>${formatNumber(item.value)}</span></div>
                            <div class="stats-bar mt-1.5"><div class="h-full rounded-full bg-slate-300" style="width:${widthFor(item.value, max)}%;"></div></div>
                        </div>
                    `).join('')
                    : '<p class="rounded-lg border border-slate-800 bg-slate-950/40 p-4 text-sm text-slate-500">No territory tiles generated yet.</p>';
            };

            const render = (payload) => {
                root.querySelector('[data-stat-generated-at]').textContent = payload.generated_at || '-';
                renderCards(payload.summary || []);
                renderActivity(payload.activity || []);
                renderEconomy(payload.economy || {});
                renderFactionMatrix(payload.factions || []);
                renderTerritory(payload.territory || {});
                renderValueTiles(payload.world || [], root.querySelector('[data-stat-world]'));
                renderValueTiles(payload.content || [], root.querySelector('[data-stat-content]'));
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

                    if (response.ok) render(await response.json());
                } finally {
                    isFetching = false;
                }
            };

            if (initialState) {
                render(JSON.parse(initialState.textContent));
            }

            window.setInterval(fetchState, 5000);
        });
    </script>
@endpush

@php
    $summary = collect($statistics['summary'] ?? []);
    $activity = collect($statistics['activity'] ?? []);
    $economy = $statistics['economy'] ?? [];
    $factions = collect($statistics['factions'] ?? []);
    $territory = $statistics['territory'] ?? [];
    $world = collect($statistics['world'] ?? []);
    $content = collect($statistics['content'] ?? []);

    $activitySeries = [
        ['key' => 'transactions', 'label' => 'Transactions', 'color' => '#38bdf8'],
        ['key' => 'messages', 'label' => 'Messages', 'color' => '#a78bfa'],
        ['key' => 'users', 'label' => 'Users', 'color' => '#facc15'],
        ['key' => 'characters', 'label' => 'Characters', 'color' => '#4ade80'],
    ];
    $activityValueMax = max(1, $activity->flatMap(fn ($item) => collect($activitySeries)->map(fn ($series) => (int) ($item[$series['key']] ?? 0)))->max() ?? 1);
    $economyRows = collect([
        ['label' => 'Work earned', 'value' => $economy['work_earned'] ?? 0, 'color' => '#4ade80'],
        ['label' => 'Marketplace spend', 'value' => $economy['marketplace_spend'] ?? 0, 'color' => '#facc15'],
        ['label' => 'Bank transfers', 'value' => $economy['bank_transfers'] ?? 0, 'color' => '#38bdf8'],
        ['label' => 'Nation donations', 'value' => $economy['nation_donations'] ?? 0, 'color' => '#a3e635'],
        ['label' => 'Stock volume', 'value' => $economy['stock_volume'] ?? 0, 'color' => '#93c5fd'],
        ['label' => 'Refunds issued', 'value' => $economy['refunds'] ?? 0, 'color' => '#86efac'],
    ]);
    $economyMax = max(1, (int) $economyRows->max('value'));
    $factionCharacterMax = max(1, (int) $factions->max('characters'));
    $factionCreditMax = max(1, (int) $factions->max('credits'));
    $territoryTypes = collect($territory['types'] ?? []);
    $territoryTypeMax = max(1, (int) $territoryTypes->max('value'));
    $territoryClaimedPercent = (float) ($territory['claimed_percent'] ?? 0);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Statistics</p>
                <p class="text-sm uppercase tracking-[0.22em] text-white/55">Live command view across players, economy, territory, content, chat, and combat.</p>
            </div>
            <div class="inline-flex w-fit items-center gap-2 rounded-lg border border-slate-700 bg-slate-950/45 px-4 py-2 text-xs font-semibold uppercase text-slate-400">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-60"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-sky-300"></span>
                </span>
                Live refresh
                <span class="text-slate-100" data-stat-generated-at>{{ $statistics['generated_at'] }}</span>
            </div>
        </div>
    </x-slot>

    <div
        data-admin-statistics
        data-statistics-state-url="{{ route('admin.statistics.state') }}"
        class="space-y-6"
    >
        <script type="application/json" data-initial-statistics-json>@json($statistics)</script>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-stat-summary>
            @foreach ($summary as $item)
                <article class="stats-kpi p-4">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[11px] font-semibold uppercase text-slate-400">{{ $item['label'] }}</span>
                        <i class="fa-solid {{ $item['icon'] ?? 'fa-chart-simple' }} text-slate-500"></i>
                    </div>
                    <p class="mt-3 font-['Teko'] text-[2.55rem] leading-none text-slate-50">{{ number_format((int) $item['value']) }}</p>
                </article>
            @endforeach
        </section>

        <div class="grid gap-4 xl:grid-cols-2">
            <section class="stats-panel p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-2xl uppercase text-slate-100">Seven Day Pulse</p>
                        <p class="mt-0.5 text-xs text-slate-400">Last 7 days across activity streams.</p>
                    </div>
                    <i class="fa-solid fa-chart-line text-xl text-sky-300"></i>
                </div>
                <div class="mt-3">
                    <div class="stats-activity-chart">
                        <div class="grid grid-cols-7 gap-3" data-stat-pulse-chart>
                            @foreach ($activity as $day)
                                <div class="min-w-0">
                                    <div class="stats-day-bars">
                                        @foreach ($activitySeries as $series)
                                            @php
                                                $value = (int) ($day[$series['key']] ?? 0);
                                                $height = $value > 0 ? max(8, ($value / $activityValueMax) * 100) : 0;
                                            @endphp
                                            <div
                                                class="stats-activity-bar"
                                                title="{{ $series['label'] }}: {{ number_format($value) }}"
                                                style="height: {{ $height }}%; background: {{ $series['color'] }};"
                                            ></div>
                                        @endforeach
                                    </div>
                                    <p class="mt-2 text-center text-[11px] font-semibold uppercase text-slate-500">{{ $day['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4" data-stat-pulse-legend>
                        @foreach ($activitySeries as $series)
                            @php
                                $seriesTotal = $activity->sum(fn ($item) => (int) ($item[$series['key']] ?? 0));
                            @endphp
                            <div class="stats-type-chip px-3 py-2">
                                <div class="flex items-center gap-2 text-[11px] font-semibold uppercase text-slate-400">
                                    <span class="h-2 w-2 rounded-full" style="background: {{ $series['color'] }}"></span>{{ $series['label'] }}
                                </div>
                                <p class="mt-1 font-['Teko'] text-2xl leading-none text-slate-50">{{ number_format($seriesTotal) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="stats-panel p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-['Teko'] text-2xl uppercase text-slate-100">Territory Control</p>
                        <p class="mt-0.5 text-xs text-slate-400">Claimed land against generated tiles.</p>
                    </div>
                    <i class="fa-solid fa-map-location-dot text-xl text-emerald-200"></i>
                </div>
                <div class="mt-3 rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase text-slate-500">Claimed Progress</p>
                            <p class="mt-1 font-['Teko'] text-4xl leading-none text-slate-50" data-territory-percent>{{ $territoryClaimedPercent }}%</p>
                        </div>
                        <p class="pb-1 text-xs font-semibold uppercase text-slate-400">
                            <span data-territory-claimed>{{ number_format((int) ($territory['claimed'] ?? 0)) }}</span>
                            /
                            <span data-territory-total>{{ number_format((int) ($territory['total'] ?? 0)) }}</span>
                            tiles
                        </p>
                    </div>
                    <div class="stats-territory-meter mt-3">
                        <div data-stat-territory-meter class="h-full rounded-full bg-sky-400" style="width: {{ min(100, max(0, $territoryClaimedPercent)) }}%;"></div>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div class="stats-type-chip p-2.5">
                            <p class="text-[10px] font-semibold uppercase text-slate-500">Claimed</p>
                            <p class="mt-1 font-['Teko'] text-2xl leading-none text-slate-50">{{ number_format((int) ($territory['claimed'] ?? 0)) }}</p>
                        </div>
                        <div class="stats-type-chip p-2.5">
                            <p class="text-[10px] font-semibold uppercase text-slate-500">Open</p>
                            <p class="mt-1 font-['Teko'] text-2xl leading-none text-slate-50" data-territory-open>{{ number_format(max(0, (int) ($territory['total'] ?? 0) - (int) ($territory['claimed'] ?? 0))) }}</p>
                        </div>
                        <div class="stats-type-chip p-2.5">
                            <p class="text-[10px] font-semibold uppercase text-slate-500">Types</p>
                            <p class="mt-1 font-['Teko'] text-2xl leading-none text-slate-50">{{ number_format($territoryTypes->count()) }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 grid gap-2" data-stat-territory-types>
                        @forelse ($territoryTypes as $item)
                            <div>
                                <div class="flex justify-between gap-3 text-xs text-slate-400"><span>{{ $item['label'] }}</span><span>{{ number_format((int) $item['value']) }}</span></div>
                                <div class="stats-bar mt-1.5"><div class="h-full rounded-full bg-slate-300" style="width: {{ max((int) $item['value'] > 0 ? 5 : 0, ((int) $item['value'] / $territoryTypeMax) * 100) }}%;"></div></div>
                            </div>
                        @empty
                            <p class="rounded-lg border border-slate-800 bg-slate-950/40 p-4 text-sm text-slate-500">No territory tiles generated yet.</p>
                        @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
            <section class="stats-panel p-5">
                <p class="font-['Teko'] text-3xl uppercase text-slate-100">Economy Movement</p>
                <p class="mt-1 text-sm text-slate-400">Where credits are being generated, spent, traded, or corrected.</p>
                <div class="mt-5 grid gap-3" data-stat-economy>
                    @foreach ($economyRows as $item)
                        <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-semibold text-slate-300">{{ $item['label'] }}</p>
                                <p class="font-['Teko'] text-3xl leading-none text-slate-50">{{ number_format((int) $item['value']) }}</p>
                            </div>
                            <div class="stats-bar mt-3">
                                <div class="h-full rounded-full" style="width: {{ max((int) $item['value'] > 0 ? 5 : 0, ((int) $item['value'] / $economyMax) * 100) }}%; background: {{ $item['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="stats-panel p-5">
                <p class="font-['Teko'] text-3xl uppercase text-slate-100">Faction Matrix</p>
                <p class="mt-1 text-sm text-slate-400">Nation population, wealth, and claimed territory at a glance.</p>
                <div class="mt-5 grid gap-3 lg:grid-cols-2" data-stat-factions>
                    @foreach ($factions as $item)
                        <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full" style="background: {{ $item['color'] }}"></span>
                                        <p class="truncate font-semibold text-slate-100">{{ $item['label'] }}</p>
                                    </div>
                                    <p class="mt-1 text-xs uppercase text-slate-500">{{ number_format((int) $item['territory']) }} territory hexes</p>
                                </div>
                                <p class="font-['Teko'] text-3xl leading-none text-slate-50">{{ number_format((int) $item['characters']) }}</p>
                            </div>
                            <div class="mt-4 grid gap-3">
                                <div>
                                    <div class="flex justify-between text-xs text-slate-500"><span>Characters</span><span>{{ number_format((int) $item['characters']) }}</span></div>
                                    <div class="stats-bar mt-1"><div class="h-full rounded-full" style="width: {{ max((int) $item['characters'] > 0 ? 5 : 0, ((int) $item['characters'] / $factionCharacterMax) * 100) }}%; background: {{ $item['color'] }};"></div></div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-xs text-slate-500"><span>Player credits</span><span>{{ number_format((int) $item['credits']) }}</span></div>
                                    <div class="stats-bar mt-1"><div class="h-full rounded-full bg-sky-300" style="width: {{ max((int) $item['credits'] > 0 ? 5 : 0, ((int) $item['credits'] / $factionCreditMax) * 100) }}%;"></div></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="stats-panel p-5">
                <p class="font-['Teko'] text-3xl uppercase text-slate-100">World Systems</p>
                <div class="mt-5 grid gap-3 md:grid-cols-2" data-stat-world>
                    @foreach ($world as $item)
                        <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="inline-flex items-center gap-3 text-sm font-medium text-slate-300">
                                    <i class="fa-solid {{ $item['icon'] ?? 'fa-circle' }} w-5 text-center text-slate-500"></i>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-['Teko'] text-3xl leading-none text-slate-100">{{ number_format((int) $item['value']) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="stats-panel p-5">
                <p class="font-['Teko'] text-3xl uppercase text-slate-100">Content & Combat</p>
                <div class="mt-5 grid gap-3 md:grid-cols-2" data-stat-content>
                    @foreach ($content as $item)
                        <div class="rounded-lg border border-slate-800 bg-slate-950/40 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <span class="inline-flex items-center gap-3 text-sm font-medium text-slate-300">
                                    <i class="fa-solid {{ $item['icon'] ?? 'fa-circle' }} w-5 text-center text-slate-500"></i>
                                    {{ $item['label'] }}
                                </span>
                                <span class="font-['Teko'] text-3xl leading-none text-slate-100">{{ number_format((int) $item['value']) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
