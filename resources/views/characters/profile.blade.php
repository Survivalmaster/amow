<x-app-layout>
    @php($nextLevelXp = $character->experienceRequiredForNextLevel())
    @php($experiencePercent = min(100, (int) round(($character->experience_points / max(1, $nextLevelXp)) * 100)))
    @php($healthPercent = max(0, min(100, (int) ($character->health_points ?? 100))))
    @php($staminaPercent = max(0, min(100, (int) ($character->stamina_points ?? 100))))
    @php($armorPercent = max(0, min(100, (int) ($character->armor_points ?? 0))))
    @php($slotPercent = min(100, (int) round(($inventorySlotsUsed / max(1, $inventorySlotCapacity)) * 100)))
    @php($factionColor = $character->faction?->color ?: '#7ead59')

    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/45">UCP Character</p>
                <p class="font-['Teko'] text-6xl uppercase leading-none tracking-[0.1em] text-[#f4ecd0]">{{ $character->name }}</p>
                <p class="mt-2 text-sm uppercase tracking-[0.22em] text-white/55">{{ $character->faction->name }} | {{ $character->rank->name }} | {{ ucfirst($character->role_type) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-2 rounded-full border border-[#7ead59]/35 bg-[#7ead59]/12 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7] transition hover:bg-[#7ead59]/18">
                    <i class="fa-solid fa-box-archive"></i>
                    Inventory
                </a>
                <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white/70 transition hover:border-white/20 hover:text-white">
                    <i class="fa-solid fa-briefcase"></i>
                    Jobs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="grid lg:grid-cols-[minmax(0,1.2fr)_24rem]">
                <div class="relative p-6 sm:p-8">
                    <div class="absolute inset-x-0 top-0 h-1" style="background: {{ $factionColor }}"></div>
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                        <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-black/30 shadow-xl shadow-black/30" style="color: {{ $factionColor }}">
                            <i class="fa-solid fa-id-card-clip text-4xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border border-white/10 bg-black/25 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Age {{ $character->age }}</span>
                                <span class="rounded-full border border-white/10 bg-black/25 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-white/60">{{ $character->is_nation_leader ? 'Nation Leader' : 'Citizen' }}</span>
                                @if ($character->is_business_owner)
                                    <span class="rounded-full border border-[#c2a84f]/30 bg-[#c2a84f]/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#f4d77a]">Business Owner</span>
                                @endif
                            </div>
                            <p class="mt-4 font-['Teko'] text-5xl uppercase leading-none tracking-[0.08em]">{{ $character->displayed_job_name }}</p>
                            <p class="mt-2 text-sm uppercase tracking-[0.22em] text-white/45">Started as {{ $character->starting_occupation }}</p>
                            <p class="mt-5 max-w-3xl text-sm leading-7 text-white/72">{{ $character->biography }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/10 bg-black/20 p-6 lg:border-l lg:border-t-0">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Standing</p>
                    <div class="mt-5 space-y-4">
                        <div>
                            <div class="flex items-end justify-between gap-3">
                                <p class="text-xs uppercase tracking-[0.2em] text-white/45">Level</p>
                                <p class="font-['Teko'] text-4xl leading-none text-[#f4d77a]">Lv <span data-character-field="level">{{ $character->level }}</span></p>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-xs uppercase tracking-[0.18em] text-white/55">
                                <span>Experience</span>
                                <span data-character-field="experience_label">{{ $character->experience_points }}/{{ $nextLevelXp }}</span>
                            </div>
                            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/10">
                                <div class="h-full rounded-full bg-[linear-gradient(90deg,#c2a84f_0%,#f4d77a_100%)]" data-character-width="experience_progress_percent" style="width: {{ $experiencePercent }}%;"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-white/10 bg-black/25 px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-white/45">Credits</p>
                                <p class="mt-1 font-['Teko'] text-3xl text-[#d7edc7]" data-character-field="formatted_credits">{{ number_format($character->plastic_credits) }}</p>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-black/25 px-4 py-3">
                                <p class="text-xs uppercase tracking-[0.18em] text-white/45">Rank</p>
                                <p class="mt-1 truncate font-['Teko'] text-3xl text-white">{{ $character->rank->name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Health', 'value' => $healthPercent, 'icon' => 'fa-heart-pulse', 'color' => '#f0b29f'],
                ['label' => 'Stamina', 'value' => $staminaPercent, 'icon' => 'fa-bolt', 'color' => '#f4d77a'],
                ['label' => 'Armor', 'value' => $armorPercent, 'icon' => 'fa-shield-halved', 'color' => '#b8ccff'],
                ['label' => 'Inventory', 'value' => $slotPercent, 'icon' => 'fa-boxes-stacked', 'color' => '#d7edc7'],
            ] as $stat)
                <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 shadow-xl shadow-black/20">
                    <div class="flex items-center justify-between gap-4">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-black/25" style="color: {{ $stat['color'] }}">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </span>
                        <p class="font-['Teko'] text-4xl leading-none text-white">{{ $stat['value'] }}%</p>
                    </div>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-[0.2em] text-white/45">{{ $stat['label'] }}</p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-black/30">
                        <div class="h-full rounded-full" style="width: {{ $stat['value'] }}%; background: {{ $stat['color'] }}"></div>
                    </div>
                    @if ($stat['label'] === 'Inventory')
                        <p class="mt-2 text-xs text-white/45">{{ $inventorySlotsUsed }}/{{ $inventorySlotCapacity }} slots</p>
                    @endif
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
            <div class="space-y-6">
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Inventory Snapshot</p>
                            <p class="mt-1 text-sm text-white/55">{{ $buildingItemCount }} building items carried.</p>
                        </div>
                        <a href="{{ route('inventory.index') }}" class="rounded-full border border-white/10 bg-black/20 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/65 transition hover:text-white">Manage</a>
                    </div>

                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @forelse ($character->inventory->take(6) as $item)
                            <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-black/30 text-lg text-[#d7edc7]">
                                        <i class="{{ $item->display_icon_class }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-white">{{ $item->name }}</p>
                                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-white/60">{{ $item->description }}</p>
                                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.2em] text-[#c2a84f]">Qty {{ $item->pivot->quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-8 text-center text-sm text-white/45 md:col-span-2">No items in inventory.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Recent Activity</p>
                    <div class="mt-5 space-y-3">
                        @forelse ($character->transactions as $transaction)
                            @php($meta = collect($transaction->metadata ?? []))
                            @php($transactionLabel = match ($transaction->type) {
                                'player_transfer_sent' => 'Money Sent',
                                'player_transfer_received' => 'Money Received',
                                'refund' => 'refund',
                                default => str($transaction->type)->replace('_', ' ')->title(),
                            })
                            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white">{{ $transactionLabel }}</p>
                                        <p class="mt-1 text-sm leading-6 text-white/65">{{ $transaction->description }}</p>
                                    </div>
                                    <p class="shrink-0 font-['Teko'] text-3xl leading-none {{ $transaction->amount >= 0 ? 'text-[#7ead59]' : 'text-[#c65b3f]' }}">{{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount) }}</p>
                                </div>
                                @if (in_array($transaction->type, ['player_transfer_sent', 'player_transfer_received', 'refund', 'work'], true))
                                    <p class="mt-2 text-xs uppercase tracking-[0.16em] text-white/40">
                                        {{ collect([
                                            $meta->has('credits_before') && $meta->has('credits_after') ? 'Credits '.number_format((int) $meta->get('credits_before')).' -> '.number_format((int) $meta->get('credits_after')) : null,
                                            $meta->get('xp_earned') || $meta->get('refund_xp') ? 'XP +'.number_format((int) ($meta->get('xp_earned') ?: $meta->get('refund_xp'))) : null,
                                            $meta->get('note') ? 'Note: '.$meta->get('note') : null,
                                        ])->filter()->implode(' | ') }}
                                    </p>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-8 text-center text-sm text-white/45">No activity recorded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Licences</p>
                    <div class="mt-5 space-y-3">
                        @forelse ($character->licences as $licence)
                            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                                <p class="font-semibold text-white">{{ $licence->name }}</p>
                                <p class="mt-1 text-sm leading-6 text-white/60">{{ $licence->description }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-6 text-sm text-white/45">No licences acquired.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[2rem] border border-[#c65b3f]/25 bg-[#c65b3f]/[0.07] p-6 shadow-2xl shadow-black/30">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#c65b3f]/30 bg-black/25 text-[#f0b29f]">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </span>
                        <div>
                            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Criminal History</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.2em] text-[#f0b29f]/80">No known record</p>
                        </div>
                    </div>
                    <div class="mt-5 rounded-2xl border border-white/10 bg-black/25 px-4 py-8 text-center">
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.1em] text-white">Clean File</p>
                        <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-white/55">Criminal records will appear here once enforcement systems are active.</p>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Civil Profile</p>
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4 border-b border-white/10 pb-3">
                            <dt class="text-white/45">Faction</dt>
                            <dd class="text-right text-white">{{ $character->faction->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-white/10 pb-3">
                            <dt class="text-white/45">Current job</dt>
                            <dd class="text-right text-white" data-character-field="displayed_job_name">{{ $character->displayed_job_name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-white/10 pb-3">
                            <dt class="text-white/45">Role</dt>
                            <dd class="text-right text-white">{{ ucfirst($character->role_type) }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-white/45">Created</dt>
                            <dd class="text-right text-white">{{ $character->created_at?->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </section>
    </div>
</x-app-layout>
