<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Jobs New</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Developer preview for tiered work and job item drops.</p>
        </div>
    </x-slot>

    @php($currentJob = $character->currentJob)
    @php($maxTier = max(1, (int) ($currentJob?->max_tier ?? 20)))
    @php($tierRequired = max(1, (int) ($currentJob?->tier_xp_required ?? 100)))
    @php($tierPercent = min(100, (int) round((($currentProgress?->tier_experience ?? 0) / $tierRequired) * 100)))
    @php($workCooldownEndsAt = $character->workCooldownEndsAt())
    @php($workRemainingSeconds = $workCooldownEndsAt && $workCooldownEndsAt->isFuture() ? now()->diffInSeconds($workCooldownEndsAt) : 0)
    @php($canWork = $workRemainingSeconds === 0 && (int) ($character->stamina_points ?? 100) > 0)

    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-[#7ead59]/10 p-6 shadow-2xl shadow-black/30">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#d7edc7]">Current Assignment</p>
                        <p class="mt-2 font-['Teko'] text-5xl uppercase tracking-[0.08em]">{{ $currentJob?->name ?? $character->starting_occupation }}</p>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-white/70">{{ $currentJob?->description ?? 'Pick a job to begin tier progression.' }}</p>
                    </div>
                    <form method="POST" action="{{ route('jobs-new.work') }}">
                        @csrf
                        <button class="inline-flex items-center gap-2 rounded-full px-6 py-3 text-xs font-semibold uppercase tracking-[0.18em] transition {{ $canWork ? 'bg-[#7ead59] text-[#07100c] hover:bg-[#d7edc7]' : 'cursor-not-allowed border border-white/10 bg-white/5 text-white/38' }}" @disabled(! $canWork)>
                            <i class="fa-solid fa-hammer"></i>
                            Work Shift
                        </button>
                    </form>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Tier</p>
                        <p class="mt-1 font-['Teko'] text-4xl text-[#f4ecd0]">{{ $currentProgress?->tier ?? 1 }}/{{ $maxTier }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Tier XP</p>
                        <p class="mt-1 font-['Teko'] text-4xl text-[#d7edc7]">{{ $currentProgress?->tier_experience ?? 0 }}/{{ $tierRequired }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Pay Bonus</p>
                        <p class="mt-1 font-['Teko'] text-4xl text-white">+{{ max(0, (($currentProgress?->tier ?? 1) - 1) * (int) ($currentJob?->tier_pay_bonus_percent ?? 0)) }}%</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Cooldown</p>
                        <p class="mt-1 font-['Teko'] text-4xl text-white">{{ $canWork ? 'Ready' : gmdate($workRemainingSeconds >= 3600 ? 'H:i:s' : 'i:s', $workRemainingSeconds) }}</p>
                    </div>
                </div>

                <div class="mt-5 h-2.5 overflow-hidden rounded-full bg-black/30">
                    <div class="h-full rounded-full bg-[linear-gradient(90deg,#7ead59_0%,#c2a84f_100%)]" style="width: {{ $tierPercent }}%;"></div>
                </div>
            </div>

            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Possible Drops</p>
                <div class="mt-4 grid gap-3">
                    @forelse ($currentJob?->drops ?? [] as $drop)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-black/20 px-4 py-3">
                            <div>
                                <p class="font-semibold text-white">{{ $drop->item?->name }}</p>
                                <p class="text-xs text-white/50">Tier {{ $drop->min_tier }}-{{ $drop->max_tier }} | {{ $drop->min_quantity }}-{{ $drop->max_quantity }} each shift</p>
                            </div>
                            <span class="rounded-full border border-[#c2a84f]/30 bg-[#c2a84f]/10 px-3 py-1 text-xs font-semibold text-[#f4d77a]">{{ rtrim(rtrim(number_format((float) $drop->drop_chance_percent, 2), '0'), '.') }}%</span>
                        </div>
                    @empty
                        <p class="rounded-xl border border-white/10 bg-black/20 px-4 py-6 text-sm text-white/55">No drops configured for this job yet.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($jobs as $job)
                @php($progress = $job->progress->first())
                @php($isCurrent = $character->current_job_id === $job->id)
                @php($isLocked = $character->level < $job->required_level)
                <article class="rounded-[1.5rem] border {{ $isCurrent ? 'border-[#7ead59]/40 bg-[#7ead59]/10' : 'border-white/10 bg-white/5' }} p-5 shadow-2xl shadow-black/30">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ $job->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.2em] text-white/45">Tier {{ $progress?->tier ?? 1 }}/{{ $job->max_tier }}</p>
                        </div>
                        <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs text-white/60">Lv {{ $job->required_level }}</span>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-white/65">{{ $job->description ?: 'No description added yet.' }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs text-white/55">
                        <div class="rounded-xl border border-white/10 bg-black/20 p-3">Pay<br><span class="font-semibold text-white">{{ $job->min_pay }}-{{ $job->max_pay }}</span></div>
                        <div class="rounded-xl border border-white/10 bg-black/20 p-3">XP<br><span class="font-semibold text-white">{{ $job->experience_reward }}</span></div>
                        <div class="rounded-xl border border-white/10 bg-black/20 p-3">Drops<br><span class="font-semibold text-white">{{ $job->drops->count() }}</span></div>
                    </div>
                    <div class="mt-5">
                        @if ($isCurrent)
                            <div class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-[#d7edc7]">Current Job</div>
                        @elseif (! $job->is_active)
                            <div class="rounded-full border border-white/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Unavailable</div>
                        @elseif ($isLocked)
                            <div class="rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Requires Level {{ $job->required_level }}</div>
                        @else
                            <form method="POST" action="{{ route('jobs-new.store', $job) }}">
                                @csrf
                                <button class="w-full rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]" @disabled(! $character->canChangeJob())>
                                    {{ $character->canChangeJob() ? 'Take Job' : 'Swap Cooldown Active' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    </div>
</x-app-layout>
