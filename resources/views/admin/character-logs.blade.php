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

    <div class="grid gap-6 xl:grid-cols-[24rem_minmax(0,1fr)]">
        <aside class="space-y-4">
            <section
                x-data="{ characterSearch: '' }"
                class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30"
            >
                <label class="space-y-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                    <span>Search Character</span>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                        <input x-model.debounce.150ms="characterSearch" class="{{ $fieldClass }} w-full pl-9" placeholder="Type a character name">
                    </div>
                </label>

                <div class="mt-4 max-h-72 space-y-2 overflow-y-auto pr-1">
                    @foreach ($characters as $character)
                        @php($characterUserLabel = $canViewPlayerEmails ? $character->user?->email : ($character->user?->name ?? 'User #'.$character->user_id))
                        <a
                            href="{{ route('admin.character-logs.index', ['character_id' => $character->id]) }}"
                            x-show="!characterSearch || @js(str($character->name.' '.$characterUserLabel)->lower()->toString()).includes(characterSearch.toLowerCase())"
                            class="block rounded-xl border px-3 py-2 text-sm transition {{ $selectedCharacter?->id === $character->id ? 'border-[#7ead59]/35 bg-[#7ead59]/10 text-[#d7edc7]' : 'border-white/10 bg-black/20 text-white/68 hover:border-white/20 hover:text-white' }}"
                        >
                            <span class="block font-semibold text-white">{{ $character->name }}</span>
                            <span class="mt-0.5 block truncate text-xs text-white/42">{{ $characterUserLabel }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            @if ($selectedCharacter)
                <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">{{ $selectedCharacter->name }}</p>
                    <div class="mt-4 space-y-2 text-sm text-white/65">
                        <p><span class="text-white/40">User:</span> {{ $canViewPlayerEmails ? ($selectedCharacter->user?->email ?? 'Unknown') : ($selectedCharacter->user?->name ?? 'User #'.$selectedCharacter->user_id) }}</p>
                        <p><span class="text-white/40">Discord:</span> {{ $selectedCharacter->user?->discord_username ?: 'Not linked' }}</p>
                        <p><span class="text-white/40">Faction:</span> {{ $selectedCharacter->faction?->name ?? 'Unknown' }}</p>
                        <p><span class="text-white/40">Rank:</span> {{ $selectedCharacter->rank?->name ?? 'Unknown' }}</p>
                        <p><span class="text-white/40">Job:</span> {{ $selectedCharacter->currentJob?->name ?? $selectedCharacter->starting_occupation }}</p>
                        <p><span class="text-white/40">Created:</span> {{ $selectedCharacter->created_at?->format('d M Y H:i') }}</p>
                    </div>
                </section>
            @endif
        </aside>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-2 border-b border-white/10 px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Audit Table</p>
                    <p class="text-xs uppercase tracking-[0.18em] text-white/38">Work, purchases, sales, rank changes, and job changes</p>
                </div>
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
                        @forelse ($transactions ?? [] as $transaction)
                            @php($meta = collect($transaction->metadata ?? []))
                            @php($eventLabel = match ($transaction->type) {
                                'work' => 'Work',
                                'item_purchase' => 'Item Bought',
                                'licence_purchase' => 'Licence Bought',
                                'stock_buy' => 'Stock Bought',
                                'stock_sell' => 'Stock Sold',
                                'job_change' => 'Job Change',
                                'rank_change' => 'Rank Change',
                                default => str($transaction->type)->replace('_', ' ')->title(),
                            })
                            @php($eventClass = match ($transaction->type) {
                                'work' => 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]',
                                'item_purchase', 'licence_purchase', 'stock_buy' => 'border-[#c2a84f]/30 bg-[#c2a84f]/10 text-[#f4d77a]',
                                'stock_sell' => 'border-[#7ec6ff]/30 bg-[#7ec6ff]/10 text-[#b9ddff]',
                                'job_change', 'rank_change' => 'border-white/15 bg-white/8 text-white',
                                default => 'border-white/10 bg-black/20 text-white/65',
                            })
                            @php($detail = match ($transaction->type) {
                                'work' => ($meta->get('job') ? $meta->get('job').' shift' : $transaction->description),
                                'job_change' => ($meta->get('from_job') || $meta->get('to_job')) ? (($meta->get('from_job') ?: 'None').' -> '.($meta->get('to_job') ?: 'None')) : $transaction->description,
                                'rank_change' => ($meta->get('from_rank') || $meta->get('to_rank')) ? (($meta->get('from_rank') ?: 'None').' -> '.($meta->get('to_rank') ?: 'None')) : $transaction->description,
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
