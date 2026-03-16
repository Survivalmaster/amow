<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Jobs Board</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Choose work that fits your level and cooldown window.</p>
        </div>
    </x-slot>

    @php($jobChangeTime = $character->job_changed_at?->copy()->addDay())

    <div class="space-y-6">
        <section class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Current Job</p>
                <p class="mt-3 font-['Teko'] text-4xl uppercase" data-character-field="displayed_job_name">{{ $character->displayed_job_name }}</p>
                <p class="mt-3 text-sm leading-7 text-white/70">{{ $character->currentJob?->description ?? 'This character is waiting for a job assignment.' }}</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Level</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase">Lv. <span data-character-field="level">{{ $character->level }}</span></p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">XP</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase" data-character-field="experience_label">{{ $character->experience_points }}/{{ $character->experienceRequiredForNextLevel() }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-white/45">Swap Cooldown</p>
                        <p class="mt-2 text-sm text-white/70">{{ $character->canChangeJob() ? 'Ready now' : 'Available '.optional($jobChangeTime)->format('d M H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Work Rules</p>
                <div class="mt-4 space-y-3 text-sm leading-7 text-white/70">
                    <p>Each character can only hold one job at a time.</p>
                    <p>Changing jobs has a 24-hour cooldown unless an admin overrides it.</p>
                    <p>Finishing a work cycle currently awards 5 XP.</p>
                    <p>Pay and work cooldown are controlled per job in the admin panel.</p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($jobs as $job)
                @php($isCurrent = $character->current_job_id === $job->id)
                @php($isLocked = $character->level < $job->required_level)
                <article class="rounded-[2rem] border {{ $isCurrent ? 'border-[#7ead59]/40 bg-[#7ead59]/10' : 'border-white/10 bg-white/5' }} p-6 shadow-2xl shadow-black/30">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ $job->name }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.22em] {{ $job->is_active ? 'text-[#7ead59]' : 'text-[#c65b3f]' }}">{{ $job->is_active ? 'Active' : 'Unavailable' }}</p>
                        </div>
                        <div class="rounded-full border border-white/10 px-3 py-2 text-xs uppercase tracking-[0.2em] text-white/60">Lvl {{ $job->required_level }}</div>
                    </div>
                    <p class="mt-4 text-sm leading-7 text-white/70">{{ $job->description ?: 'No description added yet.' }}</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/45">Pay Range</p>
                            <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ number_format($job->min_pay) }}-{{ number_format($job->max_pay) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/45">Work Cooldown</p>
                            <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ $job->work_cooldown_minutes }}m</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        @if ($isCurrent)
                            <div class="rounded-full border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-[#d7edc7]">Current Job</div>
                        @elseif (! $job->is_active)
                            <div class="rounded-full border border-white/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Unavailable</div>
                        @elseif ($isLocked)
                            <div class="rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-[#f0b29f]">Requires Level {{ $job->required_level }}</div>
                        @else
                            <form method="POST" action="{{ route('jobs.store', $job) }}">
                                @csrf
                                <button class="w-full rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]" @disabled(! $character->canChangeJob())>
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
