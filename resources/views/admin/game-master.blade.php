<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Game Master</p>
                <p class="mt-1 text-sm text-white/55">Create timed event banners and temporary work multipliers.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($events->filter->isActive()->count()) }}</p>
                    <p>Active</p>
                </div>
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">{{ number_format($events->where('xp_multiplier_enabled', true)->count()) }}</p>
                    <p>XP Boosts</p>
                </div>
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f0b29f]">{{ number_format($events->where('credit_multiplier_enabled', true)->count()) }}</p>
                    <p>Credit Boosts</p>
                </div>
            </div>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')
    @php($labelClass = 'space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45')
    @php($toggleClass = 'flex items-center gap-3 rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white/70')
    @php($formatMultiplier = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.'))

    <div
        x-data="{
            openId: null,
            showCreate: false,
            query: '',
            status: 'all',
            filterRows() {
                if (!this.$refs.rows) return;
                [...this.$refs.rows.querySelectorAll('[data-admin-row]')].forEach((row) => {
                    const textMatch = !this.query || row.dataset.search.includes(this.query.toLowerCase());
                    const statusMatch = this.status === 'all' || row.dataset.status === this.status;
                    row.toggleAttribute('hidden', !(textMatch && statusMatch));
                });
            }
        }"
        x-effect="filterRows()"
        class="space-y-5"
    >
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid flex-1 gap-3 md:grid-cols-3">
                    <label class="{{ $labelClass }} md:col-span-2">
                        <span>Search</span>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                            <input x-model.debounce.150ms="query" class="{{ $fieldClass }} w-full pl-9" placeholder="Title, details, faction">
                        </div>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span>Status</span>
                        <select x-model="status" class="{{ $fieldClass }} w-full">
                            <option value="all">All events</option>
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </label>
                </div>
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]">
                    <i class="fa-solid fa-plus"></i>
                    Create Event
                </button>
            </div>
        </section>

        <x-admin.modal open="showCreate" title="Create Event" subtitle="Events can display banners and boost work rewards." max-width="56rem">
            <form method="POST" action="{{ route('admin.game-master.events.store') }}" class="grid gap-4 p-5 lg:grid-cols-2" x-data="{ xpBoost: false, creditBoost: false }">
                @csrf
                @include('admin.partials.game-event-fields', ['event' => null])
                <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                    <button type="button" @click="showCreate = false" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                    <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Create</button>
                </div>
            </form>
        </x-admin.modal>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-[11px] uppercase tracking-[0.18em] text-white/40">
                        <tr>
                            <th class="px-5 py-3 text-left">Event</th>
                            <th class="px-4 py-3 text-left">Scope</th>
                            <th class="px-4 py-3 text-left">Boosts</th>
                            <th class="px-4 py-3 text-left">Deadline</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Participation</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @forelse ($events as $event)
                            @php($status = ! $event->is_enabled ? 'disabled' : ($event->ends_at?->isPast() ? 'expired' : 'active'))
                            @php($boosts = collect([
                                $event->xp_multiplier_enabled ? 'XP '.$formatMultiplier($event->xp_multiplier).'x' : null,
                                $event->credit_multiplier_enabled ? 'Credits '.$formatMultiplier($event->credit_multiplier).'x' : null,
                            ])->filter()->implode(' | '))
                            @php($participation = $eventParticipation[$event->id] ?? ['participant_count' => 0, 'shift_count' => 0, 'credits' => 0, 'xp' => 0, 'participants' => collect()])
                            <tr
                                data-admin-row
                                data-status="{{ $status }}"
                                data-search="{{ str($event->title.' '.$event->body.' '.($event->faction?->name ?? 'Global').' '.$boosts)->lower() }}"
                                class="align-top transition hover:bg-white/[0.035]"
                            >
                                <td class="min-w-[20rem] px-5 py-4">
                                    <p class="font-semibold text-white">{{ $event->title }}</p>
                                    <p class="mt-1 max-w-xl truncate text-xs text-white/45">{{ $event->body }}</p>
                                    <p class="mt-2 text-[10px] uppercase tracking-[0.16em] text-white/35">Created by {{ $event->creator?->name ?? 'Unknown' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $event->faction?->name ?? 'Global' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $boosts ?: 'None' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $event->ends_at?->format('d M Y H:i') ?? 'No deadline' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $status === 'active' ? 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]' : ($status === 'expired' ? 'border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f]' : 'border-white/10 bg-black/20 text-white/45') }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="min-w-[22rem] px-4 py-4">
                                    <div class="grid grid-cols-2 gap-2 text-xs text-white/65">
                                        <span><span class="text-white/38">Players:</span> {{ number_format($participation['participant_count']) }}</span>
                                        <span><span class="text-white/38">Shifts:</span> {{ number_format($participation['shift_count']) }}</span>
                                        <span><span class="text-white/38">Credits:</span> {{ number_format($participation['credits']) }}</span>
                                        <span><span class="text-white/38">XP:</span> {{ number_format($participation['xp']) }}</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @forelse ($participation['participants']->take(4) as $participant)
                                            <span class="rounded-lg border border-white/10 bg-black/20 px-2 py-1 text-[11px] text-white/60">
                                                {{ $participant['character']?->name ?? 'Deleted character' }} x{{ number_format($participant['shifts']) }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-white/35">No work participation yet.</span>
                                        @endforelse
                                        @if ($participation['participants']->count() > 4)
                                            <span class="rounded-lg border border-white/10 bg-black/20 px-2 py-1 text-[11px] text-white/38">+{{ number_format($participation['participants']->count() - 4) }} more</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $event->id }}" />
                                </td>
                            </tr>

                            <x-admin.modal open="openId === {{ $event->id }}" close="openId = null" title="Edit {{ $event->title }}" subtitle="{{ $event->faction?->name ?? 'Global event' }}" max-width="56rem">
                                <form method="POST" action="{{ route('admin.game-master.events.update', $event) }}" class="grid gap-4 p-5 lg:grid-cols-2" x-data="{ xpBoost: @js($event->xp_multiplier_enabled), creditBoost: @js($event->credit_multiplier_enabled) }">
                                    @csrf
                                    @method('PATCH')
                                    @include('admin.partials.game-event-fields', ['event' => $event])
                                    <div class="flex justify-end gap-2 border-t border-white/10 pt-3 lg:col-span-2">
                                        <button type="button" @click="openId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">Cancel</button>
                                        <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]"><i class="fa-solid fa-check"></i>Save</button>
                                    </div>
                                </form>
                            </x-admin.modal>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm text-white/55">No events created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
