<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Character Logs</p>
                <p class="mt-1 text-sm text-white/55">Review a character's money, job, progression, and admin-change history.</p>
            </div>
            @if ($selectedCharacter)
                <div class="grid grid-cols-3 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($selectedCharacter->plastic_credits) }}</p>
                        <p>Credits</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">{{ number_format($selectedCharacter->level) }}</p>
                        <p>Level</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f0b29f]">{{ number_format($transactions?->total() ?? 0) }}</p>
                        <p>Events</p>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')
    @php($canViewPlayerEmails = auth()->user()?->loadMissing('permissions')->hasPermission('developer'))

    @push('styles')
        <style>
            .character-log-summary-top {
                display: grid;
                grid-template-columns: minmax(20rem, 1fr) minmax(34rem, 0.95fr);
                gap: 1.5rem;
                align-items: start;
            }

            .character-log-stat-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.75rem;
                max-width: 52rem;
                margin-left: auto;
            }

            @media (max-width: 1100px) {
                .character-log-summary-top {
                    grid-template-columns: 1fr;
                }

                .character-log-stat-grid {
                    max-width: none;
                    margin-left: 0;
                }
            }
        </style>
    @endpush

    <div class="space-y-5">
        <section
            x-data="{ characterSearch: '' }"
            class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30"
        >
            <div class="grid gap-4 lg:grid-cols-[minmax(18rem,28rem)_minmax(0,1fr)]">
                <label class="space-y-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                    <span>Search Character</span>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                        <input x-model.debounce.150ms="characterSearch" class="{{ $fieldClass }} w-full pl-9" placeholder="Type a character name">
                    </div>
                </label>

                <div class="flex items-end text-sm text-white/50">
                    @if ($selectedCharacter)
                        <span>Showing logs for <span class="font-semibold text-white">{{ $selectedCharacter->name }}</span>. Search again to switch character.</span>
                    @else
                        <span>Search for a character to view their audit history and performance summary.</span>
                    @endif
                </div>

                <div x-show="characterSearch.trim().length > 0" x-cloak class="lg:col-span-2">
                    <div class="max-h-72 space-y-2 overflow-y-auto rounded-xl border border-white/10 bg-black/20 p-2">
                        @foreach ($characters as $character)
                            @php($characterUserLabel = $canViewPlayerEmails ? $character->user?->email : ($character->user?->name ?? 'User #'.$character->user_id))
                            <a
                                href="{{ route('admin.character-logs.index', ['character_id' => $character->id]) }}"
                                x-show="@js(str($character->name.' '.$characterUserLabel)->lower()->toString()).includes(characterSearch.toLowerCase())"
                                class="block rounded-lg border px-3 py-2 text-sm transition {{ $selectedCharacter?->id === $character->id ? 'border-blue-400/50 bg-blue-500/15 text-white' : 'border-white/10 bg-black/20 text-white/68 hover:border-white/20 hover:text-white' }}"
                            >
                                <span class="block font-semibold text-white">{{ $character->name }}</span>
                                <span class="mt-0.5 block truncate text-xs text-white/42">{{ $characterUserLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @if ($selectedCharacter)
            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                <div class="character-log-summary-top">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">{{ $selectedCharacter->name }}</p>
                        <div class="mt-4 space-y-2 text-sm text-white/65">
                            <p><span class="text-white/40">User:</span> {{ $canViewPlayerEmails ? ($selectedCharacter->user?->email ?? 'Unknown') : ($selectedCharacter->user?->name ?? 'User #'.$selectedCharacter->user_id) }}</p>
                            <p><span class="text-white/40">Discord:</span> {{ $selectedCharacter->user?->discord_username ?: 'Not linked' }}</p>
                            <p><span class="text-white/40">Faction:</span> {{ $selectedCharacter->faction?->name ?? 'Unknown' }}</p>
                            <p><span class="text-white/40">Rank:</span> {{ $selectedCharacter->rank?->name ?? 'Unknown' }}</p>
                            <p><span class="text-white/40">Job:</span> {{ $selectedCharacter->currentJob?->name ?? $selectedCharacter->starting_occupation }}</p>
                            <p><span class="text-white/40">Created:</span> {{ $selectedCharacter->created_at?->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <div>
                        <div class="character-log-stat-grid">
                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Earned</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['earned_credits'] ?? 0) }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Spent</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['spent_credits'] ?? 0) }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">XP Logged</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['xp_earned'] ?? 0) }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Log Entries</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['total_logs'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-4">
                        <div class="grid gap-4 xl:grid-cols-2">
                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <div class="flex items-center justify-between text-xs uppercase tracking-[0.14em] text-white/45">
                                    <span>Level Progress</span>
                                    <span>{{ $selectedCharacter->experience_points }}/{{ $selectedCharacter->experienceRequiredForNextLevel() }} XP</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full" style="width: {{ $logStats['level_progress_percent'] ?? 0 }}%; background: linear-gradient(90deg, #ef4444 0%, #f59e0b 50%, #22c55e 100%);"></div>
                                </div>

                                <div class="mt-5 flex items-center justify-between text-xs uppercase tracking-[0.14em] text-white/45">
                                    <span>Stamina</span>
                                    <span>{{ $selectedCharacter->stamina_points ?? 100 }}/100</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full" style="width: {{ $logStats['stamina_percent'] ?? 0 }}%; background: linear-gradient(90deg, #ef4444 0%, #f59e0b 50%, #22c55e 100%);"></div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <p class="text-xs uppercase tracking-[0.14em] text-white/45">Activity Mix</p>
                                @php($activityRows = [
                                    ['Work', $logStats['work_count'] ?? 0],
                                    ['Purchases', $logStats['purchase_count'] ?? 0],
                                    ['Market', $logStats['market_count'] ?? 0],
                                    ['Transfers', $logStats['transfer_count'] ?? 0],
                                    ['Changes', $logStats['change_count'] ?? 0],
                                ])
                                @php($maxActivity = max(1, max(array_column($activityRows, 1))))
                                <div class="mt-3 space-y-3">
                                    @foreach ($activityRows as [$label, $count])
                                        <div>
                                            <div class="flex justify-between text-xs text-white/55">
                                                <span>{{ $label }}</span>
                                                <span>{{ number_format($count) }}</span>
                                            </div>
                                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-white/10">
                                                @php($activityPercent = (int) round(($count / $maxActivity) * 100))
                                                <div class="h-full rounded-full" style="width: {{ $activityPercent }}%; background: linear-gradient(90deg, #ef4444 0%, #f59e0b 50%, #22c55e 100%);"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                            <p class="text-xs uppercase tracking-[0.14em] text-white/45">Last 7 Days</p>
                            <div class="mt-4 flex h-28 items-end gap-2">
                                @foreach (($logStats['activity_days'] ?? collect()) as $day)
                                    <div class="flex flex-1 flex-col items-center gap-2">
                                        <div class="relative flex h-20 w-full items-end rounded bg-white/5 px-1 pb-1">
                                            <span class="absolute right-2 top-2 rounded bg-slate-950/75 px-1.5 py-0.5 text-[11px] font-semibold leading-none text-white">{{ number_format($day['count']) }}</span>
                                            @php($barColor = sprintf('hsl(%d 70%% 45%%)', (int) round(($day['percent'] / 100) * 130)))
                                            <div class="w-full rounded" style="height: {{ max(5, $day['percent']) }}%; background: {{ $barColor }};"></div>
                                        </div>
                                        <span class="text-[10px] uppercase text-white/40">{{ $day['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-4 xl:grid-cols-3">
                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.14em] text-white/45">Level</p>
                                        <p class="mt-2 text-2xl font-semibold text-white">Lv {{ number_format($selectedCharacter->level) }}</p>
                                    </div>
                                    <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-white/55">{{ number_format($selectedCharacter->experience_points) }} XP</span>
                                </div>
                                <p class="mt-3 text-xs text-white/45">Next level at {{ number_format($selectedCharacter->experienceRequiredForNextLevel()) }} XP.</p>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.14em] text-white/45">Inventory</p>
                                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['inventory_quantity'] ?? 0) }} items</p>
                                    </div>
                                    <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-white/55">{{ number_format($logStats['inventory_count'] ?? 0) }} slots</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse ($selectedCharacter->inventory->take(5) as $item)
                                        <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-white/65">{{ $item->name }} x{{ max(1, (int) ($item->pivot->quantity ?? 1)) }}</span>
                                    @empty
                                        <span class="text-xs text-white/40">No inventory items.</span>
                                    @endforelse
                                    @if ($selectedCharacter->inventory->count() > 5)
                                        <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-white/45">+{{ number_format($selectedCharacter->inventory->count() - 5) }} more</span>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.14em] text-white/45">Land</p>
                                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['open_land_tiles'] ?? 0) }}/{{ number_format($logStats['land_tiles'] ?? 0) }} open</p>
                                    </div>
                                    <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-white/55">{{ number_format($logStats['complete_land_buildings'] ?? 0) }} buildings</span>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2 text-xs text-white/55">
                                    <span>Blocked {{ number_format($logStats['blocked_land_tiles'] ?? 0) }}</span>
                                    <span>Clearing {{ number_format($logStats['clearing_land_tiles'] ?? 0) }}</span>
                                    <span>Building {{ number_format($logStats['building_land_buildings'] ?? 0) }}</span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse ($selectedCharacter->landBuildings->take(3) as $building)
                                        <span class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs text-white/65">{{ $building->item?->name ?? 'Building' }} {{ $building->isComplete() ? 'ready' : 'building' }}</span>
                                    @empty
                                        <span class="text-xs text-white/40">No land buildings.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                </div>
            </section>
        @endif

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-2 border-b border-white/10 px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Audit Table</p>
                    <p class="text-xs uppercase tracking-[0.18em] text-white/38">Work, purchases, sales, rank changes, and job changes</p>
                </div>
                @if ($selectedCharacter)
                    <form method="GET" action="{{ route('admin.character-logs.index') }}" class="flex items-center gap-2 text-xs uppercase tracking-[0.14em] text-white/45">
                        <input type="hidden" name="character_id" value="{{ $selectedCharacter->id }}">
                        <label for="character-log-per-page">Rows</label>
                        <select id="character-log-per-page" name="per_page" class="{{ $fieldClass }} py-2" onchange="this.form.submit()">
                            @foreach ($perPageOptions as $option)
                                <option value="{{ $option }}" @selected((string) $perPage === (string) $option)>{{ $option }}</option>
                            @endforeach
                            <option value="max" @selected($perPage === 'max')>Max</option>
                        </select>
                    </form>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-[11px] uppercase tracking-[0.18em] text-white/40">
                        <tr>
                            <th class="px-5 py-3 text-left">Time</th>
                            <th class="px-4 py-3 text-left">Event</th>
                            <th class="px-4 py-3 text-left">Details</th>
                            <th class="px-4 py-3 text-left">Change</th>
                            <th class="px-5 py-3 text-right">Credits</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @if (! $selectedCharacter)
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-white/55">Search for a character to load their audit table.</td>
                            </tr>
                        @else
                        @forelse ($transactions ?? [] as $transaction)
                            @php($meta = collect($transaction->metadata ?? []))
                            @php($eventLabel = match ($transaction->type) {
                                'work' => 'Work',
                                'item_purchase' => 'Item Bought',
                                'licence_purchase' => 'Licence Bought',
                                'stock_buy' => 'Stock Bought',
                                'stock_sell' => 'Stock Sold',
                                'player_transfer_sent' => 'Money Sent',
                                'player_transfer_received' => 'Money Received',
                                'job_change' => 'Job Change',
                                'rank_change' => 'Rank Change',
                                'refund' => 'Refund',
                                default => str($transaction->type)->replace('_', ' ')->title(),
                            })
                            @php($eventClass = match ($transaction->type) {
                                'work' => 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]',
                                'item_purchase', 'licence_purchase', 'stock_buy' => 'border-[#c2a84f]/30 bg-[#c2a84f]/10 text-[#f4d77a]',
                                'stock_sell' => 'border-[#7ec6ff]/30 bg-[#7ec6ff]/10 text-[#b9ddff]',
                                'player_transfer_sent' => 'border-[#f0b29f]/30 bg-[#f0b29f]/10 text-[#f0b29f]',
                                'player_transfer_received' => 'border-[#7ec6ff]/30 bg-[#7ec6ff]/10 text-[#b9ddff]',
                                'refund' => 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]',
                                'job_change', 'rank_change' => 'border-white/15 bg-white/8 text-white',
                                default => 'border-white/10 bg-black/20 text-white/65',
                            })
                            @php($detail = match ($transaction->type) {
                                'work' => ($meta->get('job') ? $meta->get('job').' shift' : $transaction->description),
                                'job_change' => ($meta->get('from_job') || $meta->get('to_job')) ? (($meta->get('from_job') ?: 'None').' -> '.($meta->get('to_job') ?: 'None')) : $transaction->description,
                                'rank_change' => ($meta->get('from_rank') || $meta->get('to_rank')) ? (($meta->get('from_rank') ?: 'None').' -> '.($meta->get('to_rank') ?: 'None')) : $transaction->description,
                                'player_transfer_sent' => 'To '.($meta->get('recipient_name') ?: 'Unknown recipient'),
                                'player_transfer_received' => 'From '.($meta->get('sender_name') ?: 'Unknown sender'),
                                'refund' => $meta->get('reason') ?: $transaction->description,
                                default => $transaction->description,
                            })
                            @php($workChanges = collect([
                                $meta->has('xp_earned') ? 'XP +'.number_format((int) $meta->get('xp_earned')) : null,
                                $meta->has('level_before') && $meta->has('level_after') ? 'Lv '.$meta->get('level_before').' -> '.$meta->get('level_after') : null,
                                $meta->has('stamina_before') && $meta->has('stamina_after') ? 'Stamina '.$meta->get('stamina_before').' -> '.$meta->get('stamina_after') : null,
                                $meta->has('credits_before') && $meta->has('credits_after') ? 'Balance '.number_format((int) $meta->get('credits_before')).' -> '.number_format((int) $meta->get('credits_after')) : null,
                            ])->filter()->implode(' | '))
                            @php($formatMultiplier = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.'))
                            @php($xpBonusEvents = collect($meta->get('xp_multiplier_events', []))->pluck('name')->filter()->implode(', '))
                            @php($creditBonusEvents = collect($meta->get('credit_multiplier_events', []))->pluck('name')->filter()->implode(', '))
                            @php($bonusTags = collect([
                                ((float) $meta->get('xp_multiplier', 1) > 1 && $xpBonusEvents !== '') ? 'XP '.$formatMultiplier($meta->get('xp_multiplier')).'x: '.$xpBonusEvents : null,
                                ((float) $meta->get('credit_multiplier', 1) > 1 && $creditBonusEvents !== '') ? 'Credits '.$formatMultiplier($meta->get('credit_multiplier')).'x: '.$creditBonusEvents : null,
                            ])->filter()->implode(' | '))
                            @php($stateChange = match ($transaction->type) {
                                'work' => $workChanges !== '' ? trim($workChanges.($bonusTags !== '' ? ' | Bonus '.$bonusTags : '')) : 'Legacy work log - XP and level details were not recorded',
                                'job_change' => $meta->get('changed_by') ? 'Changed by '.$meta->get('changed_by') : 'Cooldown updated',
                                'rank_change' => $meta->get('changed_by') ? 'Changed by '.$meta->get('changed_by') : 'Rank updated',
                                'player_transfer_sent', 'player_transfer_received' => collect([
                                    $meta->has('credits_before') && $meta->has('credits_after') ? 'Balance '.number_format((int) $meta->get('credits_before')).' -> '.number_format((int) $meta->get('credits_after')) : null,
                                    $meta->get('faction_name') ? 'Faction '.$meta->get('faction_name') : null,
                                    $meta->get('note') ? 'Note: '.$meta->get('note') : null,
                                ])->filter()->implode(' | '),
                                'refund' => collect([
                                    $meta->get('refund_xp') ? 'XP +'.number_format((int) $meta->get('refund_xp')) : null,
                                    $meta->has('level_before') && $meta->has('level_after') ? 'Lv '.$meta->get('level_before').' -> '.$meta->get('level_after') : null,
                                    $meta->has('credits_before') && $meta->has('credits_after') ? 'Balance '.number_format((int) $meta->get('credits_before')).' -> '.number_format((int) $meta->get('credits_after')) : null,
                                    $meta->get('admin') ? 'Issued by '.$meta->get('admin') : null,
                                ])->filter()->implode(' | '),
                                default => $meta->get('credits_after') ? 'Balance '.number_format((int) $meta->get('credits_after')) : 'Balance impact',
                            })
                            <tr class="transition hover:bg-white/[0.035]">
                                <td class="whitespace-nowrap px-5 py-4 text-xs uppercase tracking-[0.14em] text-white/50">
                                    <p>{{ $transaction->created_at->format('d M Y') }}</p>
                                    <p class="mt-1 text-white/65">{{ $transaction->created_at->format('H:i:s') }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $eventClass }}">{{ $eventLabel }}</span>
                                </td>
                                <td class="min-w-[18rem] max-w-xl px-4 py-4">
                                    <p class="truncate font-semibold text-white">{{ $detail }}</p>
                                    <p class="mt-1 truncate text-xs text-white/45">{{ $transaction->description }}</p>
                                </td>
                                <td class="min-w-[16rem] px-4 py-4 text-xs text-white/58">{{ $stateChange }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right font-['Teko'] text-2xl uppercase {{ $transaction->amount > 0 ? 'text-[#7ead59]' : ($transaction->amount < 0 ? 'text-[#c65b3f]' : 'text-white/45') }}">
                                    {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-white/55">No important log entries for this character yet.</td>
                            </tr>
                        @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @if ($transactions && $transactions->hasPages())
                <div class="border-t border-white/10 px-5 py-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
