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
    @php($oldJobs = $jobs->where('is_new', false)->values())
    @php($newJobs = $jobs->where('is_new', true)->values())
    @php($dropItemOptions = $dropItems->map(fn ($item) => [
        'id' => (string) $item->id,
        'name' => $item->name,
        'slug' => $item->slug,
        'icon_class' => $item->icon_class ?: 'fa-solid fa-box',
    ])->values())

    <script>
        window.dropRuleBuilder = function (initialRows = [], items = []) {
            return {
                items,
                rows: initialRows.map((row, index) => ({ key: `${Date.now()}-${index}`, ...row })),
                itemFor(row) {
                    return this.items.find((item) => item.id === `${row.item_id}`) || null;
                },
                addRule() {
                    this.rows.push({
                        key: `${Date.now()}-${this.rows.length}-${Math.random()}`,
                        item_id: '',
                        min_tier: 1,
                        max_tier: 0,
                        min_quantity: 1,
                        max_quantity: 1,
                        drop_chance_percent: 100,
                    });
                },
                removeRule(index) {
                    this.rows.splice(index, 1);
                },
            };
        };
    </script>

    <div
        x-data="{
            openId: null,
            showCreate: {{ $errors->any() && old('_method') !== 'PATCH' ? 'true' : 'false' }},
            query: '',
            status: 'all',
            level: 'all',
            sort: 'level',
            groups: { old: true, new: true },
            init() {
                const saved = JSON.parse(localStorage.getItem('adminJobsGroups') || '{}');
                this.groups = { ...this.groups, ...saved };
            },
            toggleGroup(group) {
                this.groups[group] = !this.groups[group];
                localStorage.setItem('adminJobsGroups', JSON.stringify(this.groups));
            },
            visibleJobs() {
                const rows = [...this.$root.querySelectorAll('[data-job-row]')];
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
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid flex-1 gap-3 md:grid-cols-4">
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
                            <option value="new">New</option>
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
                    @click="showCreate = true"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7] lg:mb-0.5"
                >
                    <i class="fa-solid fa-plus"></i>
                    Create Job
                </button>
            </div>

        </section>

        <x-admin.modal open="showCreate" title="Create Job" subtitle="Set work rewards, cooldowns, and progression gates." max-width="56rem">
            <form method="POST" action="{{ route('admin.jobs.store') }}" class="grid gap-3 p-5 md:grid-cols-2 xl:grid-cols-6">
                @csrf
                @if ($errors->any() && old('_method') !== 'PATCH')
                    <div class="rounded-xl border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-4 py-3 text-sm text-[#f0b29f] md:col-span-2 xl:col-span-6">
                        <p class="font-semibold text-white">Job was not created.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <label class="{{ $labelClass }} xl:col-span-2">
                    <span>Job Name</span>
                    <input class="{{ $fieldClass }} w-full" name="name" value="{{ old('name') }}" placeholder="Royal Advisor" required>
                </label>
                <label class="{{ $labelClass }} xl:col-span-2">
                    <span>Slug</span>
                    <input class="{{ $fieldClass }} w-full" name="slug" value="{{ old('slug') }}" placeholder="royal-advisor" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Min Pay</span>
                    <input class="{{ $fieldClass }} w-full" name="min_pay" type="number" min="0" value="{{ old('min_pay') }}" placeholder="25" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Max Pay</span>
                    <input class="{{ $fieldClass }} w-full" name="max_pay" type="number" min="0" value="{{ old('max_pay') }}" placeholder="75" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Level</span>
                    <input class="{{ $fieldClass }} w-full" name="required_level" type="number" min="0" value="{{ old('required_level', 0) }}" placeholder="0" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Cooldown</span>
                    <input class="{{ $fieldClass }} w-full" name="work_cooldown_minutes" type="number" min="1" value="{{ old('work_cooldown_minutes') }}" placeholder="5" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Stamina</span>
                    <input class="{{ $fieldClass }} w-full" name="stamina_decrease" type="number" min="0" max="100" value="{{ old('stamina_decrease', 0) }}" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>XP</span>
                    <input class="{{ $fieldClass }} w-full" name="experience_reward" type="number" min="0" value="{{ old('experience_reward', 5) }}" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Max Tier</span>
                    <input class="{{ $fieldClass }} w-full" name="max_tier" type="number" min="0" max="20" value="{{ old('max_tier', 0) }}" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Tier XP</span>
                    <input class="{{ $fieldClass }} w-full" name="tier_xp_required" type="number" min="0" value="{{ old('tier_xp_required', 0) }}" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Tier Pay %</span>
                    <input class="{{ $fieldClass }} w-full" name="tier_pay_bonus_percent" type="number" min="0" max="500" value="{{ old('tier_pay_bonus_percent', 0) }}" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Tier XP %</span>
                    <input class="{{ $fieldClass }} w-full" name="tier_xp_bonus_percent" type="number" min="0" max="500" value="{{ old('tier_xp_bonus_percent', 0) }}" required>
                </label>
                <label class="{{ $labelClass }} md:col-span-2">
                    <span>Activity</span>
                    <input class="{{ $fieldClass }} w-full" name="working_display_message" value="{{ old('working_display_message') }}" placeholder="Advising the crown.">
                </label>
                <label class="{{ $toggleClass }}">
                    <input type="checkbox" name="is_starter" value="1" class="accent-[#7ead59]" @checked(old('is_starter'))>
                    Starter
                </label>
                <label class="{{ $toggleClass }}">
                    <input type="checkbox" name="is_active" value="1" class="accent-[#7ead59]" @checked(old('is_active', true))>
                    Active
                </label>
                <label class="{{ $toggleClass }}">
                    <input type="checkbox" name="is_new" value="1" class="accent-[#7ead59]" @checked(old('is_new'))>
                    New
                </label>
                <label class="{{ $labelClass }} md:col-span-2 xl:col-span-6">
                    <span>Description</span>
                    <textarea class="{{ $fieldClass }} min-h-20 w-full" name="description" placeholder="A short description players will see.">{{ old('description') }}</textarea>
                </label>
                <div x-data='dropRuleBuilder([], @json($dropItemOptions))' class="space-y-3 md:col-span-2 xl:col-span-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">Item Drops</p>
                            <p class="mt-1 text-xs text-white/45">Add one row per tier range. Job-only rewards can be marked not buyable in Admin Items.</p>
                        </div>
                        <button type="button" @click="addRule()" class="inline-flex items-center gap-2 rounded-full border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-[#d7edc7]">
                            <i class="fa-solid fa-plus"></i>
                            Add Drop
                        </button>
                    </div>
                    <template x-if="rows.length === 0">
                        <div class="rounded-xl border border-white/10 bg-black/20 px-4 py-5 text-sm text-white/50">No item drops configured.</div>
                    </template>
                    <template x-if="rows.length > 0">
                        @include('admin.partials.job-drop-rule-table')
                    </template>
                </div>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-3 md:col-span-2 xl:col-span-6">
                    <button type="button" @click="showCreate = false" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">
                        Cancel
                    </button>
                    <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                        <i class="fa-solid fa-check"></i>
                        Create Job
                    </button>
                </div>
            </form>
        </x-admin.modal>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <button type="button" @click="toggleGroup('old')" class="flex w-full items-center justify-between gap-4 bg-black/20 px-5 py-4 text-left transition hover:bg-white/[0.035]">
                <span>
                    <span class="block font-['Teko'] text-3xl uppercase tracking-[0.12em] text-white">Old Jobs</span>
                    <span class="mt-1 block text-sm text-white/50">{{ number_format($oldJobs->count()) }} current jobs used by the live jobs page.</span>
                </span>
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/70">
                    <i class="fa-solid fa-chevron-down transition" :class="{ 'rotate-180': groups.old }"></i>
                </span>
            </button>
            <div x-show="groups.old">
                @include('admin.partials.job-table', ['tableJobs' => $oldJobs, 'emptyMessage' => 'No old jobs created yet.'])
            </div>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-[#7aa7ff]/20 bg-[#7aa7ff]/[0.04] shadow-2xl shadow-black/30">
            <button type="button" @click="toggleGroup('new')" class="flex w-full items-center justify-between gap-4 bg-black/20 px-5 py-4 text-left transition hover:bg-white/[0.035]">
                <span>
                    <span class="block font-['Teko'] text-3xl uppercase tracking-[0.12em] text-white">New Jobs</span>
                    <span class="mt-1 block text-sm text-white/50">{{ number_format($newJobs->count()) }} jobs flagged for the new jobs page.</span>
                </span>
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/70">
                    <i class="fa-solid fa-chevron-down transition" :class="{ 'rotate-180': groups.new }"></i>
                </span>
            </button>
            <div x-show="groups.new">
                @include('admin.partials.job-table', ['tableJobs' => $newJobs, 'emptyMessage' => 'No new jobs created yet.'])
            </div>
        </section>

        @foreach ($jobs as $job)
            <template x-teleport="body">
                <div
                    x-show="openId === {{ $job->id }}"
                    x-cloak
                    x-transition.opacity
                    @keydown.escape.window="openId = null"
                    class="flex items-center justify-center p-4"
                    style="position: fixed; inset: 0; z-index: 9999; background: rgba(3, 7, 18, 0.78); backdrop-filter: blur(4px);"
                >
                    <div
                        @click.outside="openId = null"
                        class="w-full overflow-y-auto rounded-[1.25rem] border border-white/10 shadow-2xl shadow-black"
                        style="max-width: 42rem; max-height: 86vh; background: #111827; border-color: #263244; color: #e5e7eb; box-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);"
                    >
                        <div class="flex items-start justify-between gap-4 border-b border-white/10 px-5 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-xl font-semibold text-slate-100">Edit {{ $job->name }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $job->slug }} | {{ number_format($job->characters_count) }} assigned</p>
                            </div>
                            <button type="button" @click="openId = null" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/60 transition hover:text-white" title="Close">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="grid gap-3 p-5 md:grid-cols-2">
                            @csrf
                            @method('PATCH')
                            <label class="{{ $labelClass }}">
                                <span>Job Name</span>
                                <input class="{{ $fieldClass }} w-full" name="name" value="{{ $job->name }}" required>
                            </label>
                            <label class="{{ $labelClass }}">
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
                            <label class="{{ $labelClass }}">
                                <span>Max Tier</span>
                                <input class="{{ $fieldClass }} w-full" name="max_tier" type="number" min="0" max="20" value="{{ $job->max_tier }}" required>
                            </label>
                            <label class="{{ $labelClass }}">
                                <span>Tier XP</span>
                                <input class="{{ $fieldClass }} w-full" name="tier_xp_required" type="number" min="0" value="{{ $job->tier_xp_required }}" required>
                            </label>
                            <label class="{{ $labelClass }}">
                                <span>Tier Pay %</span>
                                <input class="{{ $fieldClass }} w-full" name="tier_pay_bonus_percent" type="number" min="0" max="500" value="{{ $job->tier_pay_bonus_percent }}" required>
                            </label>
                            <label class="{{ $labelClass }}">
                                <span>Tier XP %</span>
                                <input class="{{ $fieldClass }} w-full" name="tier_xp_bonus_percent" type="number" min="0" max="500" value="{{ $job->tier_xp_bonus_percent }}" required>
                            </label>
                            <label class="{{ $labelClass }} md:col-span-2">
                                <span>Activity</span>
                                <input class="{{ $fieldClass }} w-full" name="working_display_message" value="{{ $job->working_display_message }}" placeholder="Working display message">
                            </label>
                            <div class="grid gap-3 md:col-span-2 md:grid-cols-3">
                                <label class="{{ $toggleClass }}">
                                    <input type="checkbox" name="is_starter" value="1" class="accent-[#7ead59]" @checked($job->is_starter)>
                                    Starter
                                </label>
                                <label class="{{ $toggleClass }}">
                                    <input type="checkbox" name="is_active" value="1" class="accent-[#7ead59]" @checked($job->is_active)>
                                    Active
                                </label>
                                <label class="{{ $toggleClass }}">
                                    <input type="checkbox" name="is_new" value="1" class="accent-[#7ead59]" @checked($job->is_new)>
                                    New
                                </label>
                            </div>
                            <label class="{{ $labelClass }} md:col-span-2">
                                <span>Description</span>
                                <textarea class="{{ $fieldClass }} min-h-20 w-full" name="description">{{ $job->description }}</textarea>
                            </label>
                            @php($dropRuleRows = $job->drops->map(fn ($drop) => [
                                'item_id' => (string) $drop->item_id,
                                'min_tier' => $drop->min_tier,
                                'max_tier' => $drop->max_tier,
                                'min_quantity' => $drop->min_quantity,
                                'max_quantity' => $drop->max_quantity,
                                'drop_chance_percent' => rtrim(rtrim(number_format((float) $drop->drop_chance_percent, 2), '0'), '.'),
                            ])->values())
                            <div x-data='dropRuleBuilder(@json($dropRuleRows), @json($dropItemOptions))' class="space-y-3 md:col-span-2">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">Item Drops</p>
                                        <p class="mt-1 text-xs text-white/45">Add one row per tier range.</p>
                                    </div>
                                    <button type="button" @click="addRule()" class="inline-flex items-center gap-2 rounded-full border border-[#7ead59]/35 bg-[#7ead59]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-[#d7edc7]">
                                        <i class="fa-solid fa-plus"></i>
                                        Add Drop
                                    </button>
                                </div>
                                <template x-if="rows.length === 0">
                                    <div class="rounded-xl border border-white/10 bg-black/20 px-4 py-5 text-sm text-white/50">No item drops configured.</div>
                                </template>
                                <template x-if="rows.length > 0">
                                    @include('admin.partials.job-drop-rule-table')
                                </template>
                            </div>
                            <div class="flex justify-end gap-2 border-t border-white/10 pt-3 md:col-span-2">
                                <button type="button" @click="openId = null" class="rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/65">
                                    Cancel
                                </button>
                                <button class="inline-flex items-center gap-2 rounded-full bg-[#7ead59] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                                    <i class="fa-solid fa-check"></i>
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endforeach
    </div>
</x-app-layout>
