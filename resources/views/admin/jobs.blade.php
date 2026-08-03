<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Jobs</p>
                <p class="mt-1 text-sm text-white/55">Tune work, rewards, cooldowns, and progression gates.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($jobs->where('is_active', true)->count()) }}</p>
                    <p>Active</p>
                </div>
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">{{ number_format($jobs->sum('characters_count')) }}</p>
                    <p>Assigned</p>
                </div>
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f0b29f]">{{ number_format($jobs->max('required_level') ?? 0) }}</p>
                    <p>Max Lv</p>
                </div>
            </div>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')
    @php($labelClass = 'space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45')
    @php($toggleClass = 'flex items-center gap-3 rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white/70')

    <div
        x-data="{
            openId: null,
            showCreate: false,
            query: '',
            status: 'all',
            level: 'all',
            sort: 'level',
            visibleJobs() {
                if (!this.$refs.jobsList) return;
                const rows = [...this.$refs.jobsList.querySelectorAll('[data-job-row]')];
                rows.forEach((row) => {
                    const textMatch = !this.query || row.dataset.search.includes(this.query.toLowerCase());
                    const statusMatch = this.status === 'all' || row.dataset.status === this.status;
                    const levelMatch = this.level === 'all' || row.dataset.levelBucket === this.level;
                    row.toggleAttribute('hidden', !(textMatch && statusMatch && levelMatch));
                });
            }
        }"
        x-effect="visibleJobs()"
        class="space-y-5"
    >
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="grid gap-3 md:grid-cols-4 xl:min-w-[42rem]">
                    <label class="{{ $labelClass }} md:col-span-2">
                        <span>Search</span>
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/35"></i>
                            <input x-model.debounce.150ms="query" class="{{ $fieldClass }} w-full pl-9" placeholder="Name, slug, activity, description">
                        </div>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span>Status</span>
                        <select x-model="status" class="{{ $fieldClass }} w-full">
                            <option value="all">All jobs</option>
                            <option value="active">Active</option>
                            <option value="hidden">Hidden</option>
                            <option value="starter">Starter</option>
                        </select>
                    </label>
                    <label class="{{ $labelClass }}">
                        <span>Level</span>
                        <select x-model="level" class="{{ $fieldClass }} w-full">
                            <option value="all">All levels</option>
                            <option value="starter">0-1</option>
                            <option value="mid">2-5</option>
                            <option value="late">6+</option>
                        </select>
                    </label>
                </div>

                <button
                    type="button"
                    @click="showCreate = !showCreate"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]"
                >
                    <i class="fa-solid" :class="showCreate ? 'fa-minus' : 'fa-plus'"></i>
                    <span x-text="showCreate ? 'Close Create' : 'Create Job'"></span>
                </button>
            </div>

            <form x-show="showCreate" x-cloak method="POST" action="{{ route('admin.jobs.store') }}" class="mt-5 grid gap-3 border-t border-white/10 pt-5 md:grid-cols-2 xl:grid-cols-6">
                @csrf
                <label class="{{ $labelClass }} xl:col-span-2">
                    <span>Job Name</span>
                    <input class="{{ $fieldClass }} w-full" name="name" placeholder="Royal Advisor" required>
                </label>
                <label class="{{ $labelClass }} xl:col-span-2">
                    <span>Slug</span>
                    <input class="{{ $fieldClass }} w-full" name="slug" placeholder="royal-advisor" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Min Pay</span>
                    <input class="{{ $fieldClass }} w-full" name="min_pay" type="number" min="0" placeholder="25" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Max Pay</span>
                    <input class="{{ $fieldClass }} w-full" name="max_pay" type="number" min="0" placeholder="75" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Level</span>
                    <input class="{{ $fieldClass }} w-full" name="required_level" type="number" min="0" placeholder="0" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Cooldown</span>
                    <input class="{{ $fieldClass }} w-full" name="work_cooldown_minutes" type="number" min="1" placeholder="5" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Stamina</span>
                    <input class="{{ $fieldClass }} w-full" name="stamina_decrease" type="number" min="0" max="100" value="0" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>XP</span>
                    <input class="{{ $fieldClass }} w-full" name="experience_reward" type="number" min="0" value="5" required>
                </label>
                <label class="{{ $labelClass }} md:col-span-2">
                    <span>Activity</span>
                    <input class="{{ $fieldClass }} w-full" name="working_display_message" placeholder="Advising the crown.">
                </label>
                <label class="{{ $toggleClass }}">
                    <input type="checkbox" name="is_starter" value="1" class="accent-[#7ead59]">
                    Starter
                </label>
                <label class="{{ $toggleClass }}">
                    <input type="checkbox" name="is_active" value="1" class="accent-[#7ead59]" checked>
                    Active
                </label>
                <label class="{{ $labelClass }} md:col-span-2 xl:col-span-6">
                    <span>Description</span>
                    <textarea class="{{ $fieldClass }} min-h-20 w-full" name="description" placeholder="A short description players will see."></textarea>
                </label>
                <div class="flex justify-end md:col-span-2 xl:col-span-6">
                    <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                        <i class="fa-solid fa-check"></i>
                        Create Job
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="grid grid-cols-[minmax(16rem,1.2fr)_repeat(6,minmax(7rem,0.55fr))_minmax(10rem,0.75fr)] gap-3 border-b border-white/10 bg-black/30 px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/40 max-xl:hidden">
                <p>Job</p>
                <p>Level</p>
                <p>Pay</p>
                <p>XP</p>
                <p>Cooldown</p>
                <p>Stamina</p>
                <p>Assigned</p>
                <p class="text-right">Actions</p>
            </div>

            <div x-ref="jobsList" class="divide-y divide-white/10">
                @forelse ($jobs as $job)
                    @php($averagePay = (int) round(($job->min_pay + $job->max_pay) / 2))
                    @php($levelBucket = $job->required_level <= 1 ? 'starter' : ($job->required_level <= 5 ? 'mid' : 'late'))
                    @php($status = $job->is_starter ? 'starter' : ($job->is_active ? 'active' : 'hidden'))
                    <article
                        data-job-row
                        data-status="{{ $status }}"
                        data-level-bucket="{{ $levelBucket }}"
                        data-search="{{ str($job->name.' '.$job->slug.' '.$job->description.' '.$job->working_display_message)->lower() }}"
                    >
                        <div class="grid gap-4 px-5 py-4 transition hover:bg-white/[0.035] xl:grid-cols-[minmax(16rem,1.2fr)_repeat(6,minmax(7rem,0.55fr))_minmax(10rem,0.75fr)] xl:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate font-['Teko'] text-2xl uppercase leading-none tracking-[0.06em] text-white">{{ $job->name }}</p>
                                    @if ($job->is_starter)
                                        <span class="rounded-full border border-[#f4d77a]/30 bg-[#f4d77a]/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#f4d77a]">Starter</span>
                                    @endif
                                    <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $job->is_active ? 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]' : 'border-white/10 bg-black/20 text-white/45' }}">
                                        {{ $job->is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </div>
                                <p class="mt-1 truncate font-mono text-xs text-white/38">{{ $job->slug }}</p>
                                <p class="mt-2 line-clamp-1 text-sm text-white/58">{{ $job->working_display_message ?: $job->description ?: 'No activity message set.' }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] uppercase tracking-[0.16em] text-white/35 xl:hidden">Level</p>
                                <p class="font-semibold text-white">Lv {{ number_format($job->required_level) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.16em] text-white/35 xl:hidden">Pay</p>
                                <p class="font-semibold text-white">{{ number_format($job->min_pay) }}-{{ number_format($job->max_pay) }}</p>
                                <p class="text-xs text-white/38">Avg {{ number_format($averagePay) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.16em] text-white/35 xl:hidden">XP</p>
                                <p class="font-semibold text-[#d7edc7]">{{ number_format($job->experience_reward) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.16em] text-white/35 xl:hidden">Cooldown</p>
                                <p class="font-semibold text-white">{{ number_format($job->work_cooldown_minutes) }} min</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.16em] text-white/35 xl:hidden">Stamina</p>
                                <p class="font-semibold {{ $job->stamina_decrease > 50 ? 'text-[#f0b29f]' : 'text-white' }}">-{{ number_format($job->stamina_decrease) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.16em] text-white/35 xl:hidden">Assigned</p>
                                <p class="font-semibold text-white">{{ number_format($job->characters_count) }}</p>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="openId = openId === {{ $job->id }} ? null : {{ $job->id }}"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/75 transition hover:border-[#7ead59]/35 hover:text-[#d7edc7]"
                                    title="Edit"
                                >
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f] transition hover:bg-[#c65b3f]/20 disabled:cursor-not-allowed disabled:opacity-40"
                                        title="{{ $job->characters_count > 0 ? 'Assigned jobs cannot be deleted' : 'Delete' }}"
                                        @disabled($job->characters_count > 0)
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div x-show="openId === {{ $job->id }}" x-cloak class="border-t border-white/10 bg-black/20 px-5 py-5">
                            <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                                @csrf
                                @method('PATCH')
                                <label class="{{ $labelClass }} xl:col-span-2">
                                    <span>Job Name</span>
                                    <input class="{{ $fieldClass }} w-full" name="name" value="{{ $job->name }}" required>
                                </label>
                                <label class="{{ $labelClass }} xl:col-span-2">
                                    <span>Slug</span>
                                    <input class="{{ $fieldClass }} w-full" name="slug" value="{{ $job->slug }}" required>
                                </label>
                                <label class="{{ $labelClass }}">
                                    <span>Min Pay</span>
                                    <input class="{{ $fieldClass }} w-full" name="min_pay" type="number" min="0" value="{{ $job->min_pay }}" required>
                                </label>
                                <label class="{{ $labelClass }}">
                                    <span>Max Pay</span>
                                    <input class="{{ $fieldClass }} w-full" name="max_pay" type="number" min="0" value="{{ $job->max_pay }}" required>
                                </label>
                                <label class="{{ $labelClass }}">
                                    <span>Level</span>
                                    <input class="{{ $fieldClass }} w-full" name="required_level" type="number" min="0" value="{{ $job->required_level }}" required>
                                </label>
                                <label class="{{ $labelClass }}">
                                    <span>Cooldown</span>
                                    <input class="{{ $fieldClass }} w-full" name="work_cooldown_minutes" type="number" min="1" value="{{ $job->work_cooldown_minutes }}" required>
                                </label>
                                <label class="{{ $labelClass }}">
                                    <span>Stamina</span>
                                    <input class="{{ $fieldClass }} w-full" name="stamina_decrease" type="number" min="0" max="100" value="{{ $job->stamina_decrease }}" required>
                                </label>
                                <label class="{{ $labelClass }}">
                                    <span>XP</span>
                                    <input class="{{ $fieldClass }} w-full" name="experience_reward" type="number" min="0" value="{{ $job->experience_reward }}" required>
                                </label>
                                <label class="{{ $labelClass }} md:col-span-2">
                                    <span>Activity</span>
                                    <input class="{{ $fieldClass }} w-full" name="working_display_message" value="{{ $job->working_display_message }}" placeholder="Working display message">
                                </label>
                                <label class="{{ $toggleClass }}">
                                    <input type="checkbox" name="is_starter" value="1" class="accent-[#7ead59]" @checked($job->is_starter)>
                                    Starter
                                </label>
                                <label class="{{ $toggleClass }}">
                                    <input type="checkbox" name="is_active" value="1" class="accent-[#7ead59]" @checked($job->is_active)>
                                    Active
                                </label>
                                <label class="{{ $labelClass }} md:col-span-2 xl:col-span-6">
                                    <span>Description</span>
                                    <textarea class="{{ $fieldClass }} min-h-20 w-full" name="description">{{ $job->description }}</textarea>
                                </label>
                                <div class="flex justify-end gap-2 md:col-span-2 xl:col-span-6">
                                    <button type="button" @click="openId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">
                                        Cancel
                                    </button>
                                    <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                                        <i class="fa-solid fa-check"></i>
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-white/55">No jobs created yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
