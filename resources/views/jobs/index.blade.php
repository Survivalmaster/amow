<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Jobs Board</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Choose work that fits your level and cooldown window.</p>
        </div>
    </x-slot>

    @php($jobChangeTime = $character->job_changed_at?->copy()->addDay())
    @php($workCooldownEndsAt = $character->workCooldownEndsAt())
    @php($workRemainingSeconds = $workCooldownEndsAt && $workCooldownEndsAt->isFuture() ? now()->diffInSeconds($workCooldownEndsAt) : 0)
    @php($workCooldownMinutes = $character->currentJob?->work_cooldown_minutes ?? 5)
    @php($workProgressPercent = $workRemainingSeconds > 0 ? max(0, min(100, (int) round((1 - ($workRemainingSeconds / max(1, $workCooldownMinutes * 60))) * 100))) : 100)
    @php($canWork = $workRemainingSeconds === 0)
    @php($currentActivityText = $workRemainingSeconds > 0 ? ($character->currentJob?->working_display_message ?: 'Is working.') : 'Reviewing job assignments.')

    <div class="space-y-6">
        <div class="hidden" data-presence-activity="{{ $currentActivityText }}"></div>
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
                <div class="mt-5 rounded-[1.75rem] border border-white/10 bg-black/20 p-5">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-white/45">Work Cooldown</p>
                            <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]" data-work-countdown-label>{{ $canWork ? 'Ready now' : ($workRemainingSeconds >= 3600 ? gmdate('H:i:s', $workRemainingSeconds) : gmdate('i:s', $workRemainingSeconds)) }}</p>
                        </div>
                        @if ($workLocation)
                            <form method="POST" action="{{ route('work.store', $workLocation) }}">
                                @csrf
                                <button
                                    class="amow-action-button rounded-full px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] transition {{ $canWork ? 'bg-[#7ead59] text-[#07100c] hover:bg-[#92c46a]' : 'cursor-not-allowed border border-white/10 bg-white/5 text-white/38' }}"
                                    data-character-toggle-disabled="work_cooldown_active"
                                    data-work-button
                                >
                                    Work
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/60">
                            <span>Shift Recovery</span>
                            <span data-work-countdown-caption>{{ $canWork ? 'Ready for work' : 'Cooldown active' }}</span>
                        </div>
                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/10">
                            <div class="amow-progress-fill h-full rounded-full bg-[linear-gradient(90deg,#7ead59_0%,#c2a84f_100%)]" data-work-countdown-progress style="width: {{ $workProgressPercent }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Work Rules</p>
                <div class="mt-4 space-y-3 text-sm leading-7 text-white/70">
                    <p>Each character can only hold one job at a time.</p>
                    <p>Changing jobs has a 24-hour cooldown unless an admin overrides it.</p>
                    <p>Finishing a work cycle awards the XP set for your current job.</p>
                    <p>Pay, XP, stamina use, and work cooldown are controlled per job in the admin panel.</p>
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
                        <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/45">XP Reward</p>
                            <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ number_format($job->experience_reward) }}</p>
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
                                <button class="amow-action-button w-full rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]" @disabled(! $character->canChangeJob())>
                                    {{ $character->canChangeJob() ? 'Take Job' : 'Swap Cooldown Active' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const countdownLabel = document.querySelector('[data-work-countdown-label]');
                const countdownCaption = document.querySelector('[data-work-countdown-caption]');
                const progressBar = document.querySelector('[data-work-countdown-progress]');
                const workButton = document.querySelector('[data-work-button]');

                if (!countdownLabel || !countdownCaption || !progressBar || !workButton) {
                    return;
                }

                let remainingSeconds = {{ $workRemainingSeconds }};
                let cooldownMinutes = {{ $workCooldownMinutes }};
                const presenceActivity = document.querySelector('[data-presence-activity]');
                const workingMessage = @json($character->currentJob?->working_display_message ?: 'Is working.');
                const idleMessage = 'Reviewing job assignments.';

                const formatSeconds = (seconds) => {
                    const safeSeconds = Math.max(0, Math.floor(Number(seconds) || 0));
                    const hours = Math.floor(safeSeconds / 3600);
                    const minutes = Math.floor((safeSeconds % 3600) / 60);
                    const secs = safeSeconds % 60;

                    if (hours > 0) {
                        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    }

                    return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                };

                const render = () => {
                    const canWork = remainingSeconds <= 0;
                    const totalSeconds = Math.max(1, cooldownMinutes * 60);
                    const progress = canWork ? 100 : Math.max(0, Math.min(100, Math.round((1 - (remainingSeconds / totalSeconds)) * 100)));

                    countdownLabel.textContent = canWork ? 'Ready now' : formatSeconds(remainingSeconds);
                    countdownCaption.textContent = canWork ? 'Ready for work' : 'Cooldown active';
                    progressBar.style.width = `${progress}%`;
                    workButton.disabled = !canWork;
                    workButton.classList.toggle('bg-[#7ead59]', canWork);
                    workButton.classList.toggle('text-[#07100c]', canWork);
                    workButton.classList.toggle('hover:bg-[#92c46a]', canWork);
                    workButton.classList.toggle('cursor-not-allowed', !canWork);
                    workButton.classList.toggle('border', !canWork);
                    workButton.classList.toggle('border-white/10', !canWork);
                    workButton.classList.toggle('bg-white/5', !canWork);
                    workButton.classList.toggle('text-white/38', !canWork);

                    if (presenceActivity) {
                        presenceActivity.dataset.presenceActivity = canWork ? idleMessage : workingMessage;
                    }
                };

                render();

                window.setInterval(() => {
                    if (remainingSeconds > 0) {
                        remainingSeconds -= 1;
                        render();
                    }
                }, 1000);

                window.addEventListener('character-state:updated', (event) => {
                    const state = event.detail ?? {};

                    if (typeof state.work_remaining_seconds === 'number') {
                        remainingSeconds = state.work_remaining_seconds;
                    }

                    if (typeof state.work_cooldown_minutes === 'number') {
                        cooldownMinutes = state.work_cooldown_minutes;
                    }

                    if (typeof state.work_status_label === 'string') {
                        countdownLabel.textContent = state.work_status_label;
                    }

                    if (typeof state.work_cooldown_progress_percent === 'number') {
                        progressBar.style.width = `${state.work_cooldown_progress_percent}%`;
                    }

                    if (typeof state.can_work === 'boolean') {
                        workButton.disabled = !state.can_work;
                    }

                    render();
                });
            });
        </script>
    @endpush
</x-app-layout>
