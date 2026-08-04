<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Nation Logs</p>
                <p class="mt-1 text-sm text-white/55">Review treasury movement, requisitions, and nation-linked audit events.</p>
            </div>
            @if ($selectedFaction)
                <div class="grid grid-cols-3 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($logStats['current_bank'] ?? 0) }}</p>
                        <p>Bank</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">{{ number_format($selectedFaction->characters_count) }}</p>
                        <p>Members</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f0b29f]">{{ number_format($logs?->total() ?? 0) }}</p>
                        <p>Events</p>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')
    @php($canViewPlayerEmails = auth()->user()?->loadMissing('permissions')->hasPermission('developer'))

    <div class="space-y-5">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <form method="GET" action="{{ route('admin.nation-logs.index') }}" class="grid gap-4 lg:grid-cols-[minmax(18rem,28rem)_auto_minmax(0,1fr)] lg:items-end">
                <label class="space-y-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                    <span>Nation</span>
                    <select name="faction_id" class="{{ $fieldClass }} w-full" required>
                        <option value="">Select a nation</option>
                        @foreach ($factions as $faction)
                            <option value="{{ $faction->id }}" @selected($selectedFaction?->id === $faction->id)>{{ $faction->name }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Load Logs
                </button>
                <div class="text-sm text-white/50">
                    @if ($selectedFaction)
                        Showing nation logs for <span class="font-semibold text-white">{{ $selectedFaction->name }}</span>.
                    @else
                        Select a nation to view its audit history.
                    @endif
                </div>
            </form>
        </section>

        @if ($selectedFaction)
            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Money In</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['money_in'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Money Out</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['money_out'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Net Movement</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['net_bank_movement'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Requisitions</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['requisition_count'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/38">Log Entries</p>
                        <p class="mt-2 text-2xl font-semibold text-white">{{ number_format($logStats['total_logs'] ?? 0) }}</p>
                    </div>
                </div>
            </section>
        @endif

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-2 border-b border-white/10 px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Audit Table</p>
                    <p class="text-xs uppercase tracking-[0.18em] text-white/38">Donations, withdrawals, and requisition history</p>
                </div>
                @if ($selectedFaction)
                    <form method="GET" action="{{ route('admin.nation-logs.index') }}" class="flex items-center gap-2 text-xs uppercase tracking-[0.14em] text-white/45">
                        <input type="hidden" name="faction_id" value="{{ $selectedFaction->id }}">
                        <label for="nation-log-per-page">Rows</label>
                        <select id="nation-log-per-page" name="per_page" class="{{ $fieldClass }} py-2" onchange="this.form.submit()">
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
                            <th class="px-4 py-3 text-left">Actor</th>
                            <th class="px-4 py-3 text-left">Details</th>
                            <th class="px-5 py-3 text-right">Bank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @if (! $selectedFaction)
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-white/55">Select a nation to load its audit table.</td>
                            </tr>
                        @else
                            @forelse ($logs ?? [] as $entry)
                                @php($bankAmount = (int) $entry['bank_amount'])
                                @php($eventClass = match (true) {
                                    $bankAmount > 0 => 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]',
                                    $bankAmount < 0 => 'border-[#c65b3f]/30 bg-[#c65b3f]/10 text-[#f0b29f]',
                                    $entry['kind'] === 'requisition' => 'border-[#c2a84f]/30 bg-[#c2a84f]/10 text-[#f4d77a]',
                                    default => 'border-white/10 bg-black/20 text-white/65',
                                })
                                <tr class="transition hover:bg-white/[0.035]">
                                    <td class="whitespace-nowrap px-5 py-4 text-xs uppercase tracking-[0.14em] text-white/50">
                                        <p>{{ $entry['occurred_at']?->format('d M Y') }}</p>
                                        <p class="mt-1 text-white/65">{{ $entry['occurred_at']?->format('H:i:s') }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $eventClass }}">{{ $entry['event'] }}</span>
                                    </td>
                                    <td class="min-w-[12rem] px-4 py-4">
                                        <p class="font-semibold text-white">{{ $entry['actor'] }}</p>
                                        @if ($entry['actor_user'])
                                            <p class="mt-1 truncate text-xs text-white/42">{{ $canViewPlayerEmails ? $entry['actor_user']->email : ($entry['actor_user']->name ?? 'User #'.$entry['actor_user']->id) }}</p>
                                        @endif
                                    </td>
                                    <td class="min-w-[20rem] max-w-2xl px-4 py-4">
                                        <p class="truncate font-semibold text-white">{{ $entry['description'] }}</p>
                                        <p class="mt-1 truncate text-xs text-white/45">{{ $entry['detail'] }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-['Teko'] text-2xl uppercase {{ $bankAmount > 0 ? 'text-[#7ead59]' : ($bankAmount < 0 ? 'text-[#c65b3f]' : 'text-white/45') }}">
                                        {{ $bankAmount > 0 ? '+' : '' }}{{ number_format($bankAmount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-white/55">No nation logs for this nation yet.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @if ($logs && $logs->hasPages())
                <div class="border-t border-white/10 px-5 py-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
