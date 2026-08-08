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

    <script>
        window.dropRuleBuilder = function (initialRows = []) {
            return {
                rows: initialRows.map((row, index) => ({ key: `${Date.now()}-${index}`, ...row })),
                addRule() {
                    this.rows.push({
                        key: `${Date.now()}-${this.rows.length}-${Math.random()}`,
                        item_id: '',
                        min_tier: 1,
                        max_tier: 20,
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
                <label class="{{ $labelClass }}">
                    <span>Max Tier</span>
                    <input class="{{ $fieldClass }} w-full" name="max_tier" type="number" min="1" max="20" value="20" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Tier XP</span>
                    <input class="{{ $fieldClass }} w-full" name="tier_xp_required" type="number" min="1" value="100" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Tier Pay %</span>
                    <input class="{{ $fieldClass }} w-full" name="tier_pay_bonus_percent" type="number" min="0" max="500" value="5" required>
                </label>
                <label class="{{ $labelClass }}">
                    <span>Tier XP %</span>
                    <input class="{{ $fieldClass }} w-full" name="tier_xp_bonus_percent" type="number" min="0" max="500" value="5" required>
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
                <label class="{{ $toggleClass }}">
                    <input type="checkbox" name="is_new" value="1" class="accent-[#7ead59]">
                    New
                </label>
                <label class="{{ $labelClass }} md:col-span-2 xl:col-span-6">
                    <span>Description</span>
                    <textarea class="{{ $fieldClass }} min-h-20 w-full" name="description" placeholder="A short description players will see."></textarea>
                </label>
                <div x-data="dropRuleBuilder()" class="space-y-3 md:col-span-2 xl:col-span-6">
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
                    <template x-for="(row, index) in rows" :key="row.key">
                        <div class="grid gap-3 rounded-xl border border-white/10 bg-black/20 p-3 lg:grid-cols-[minmax(12rem,1.4fr)_repeat(5,minmax(5rem,0.7fr))_auto]">
                            <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                <span>Item</span>
                                <select class="{{ $fieldClass }} w-full" x-model="row.item_id" :name="`drop_rules[${index}][item_id]`">
                                    <option value="">Select item</option>
                                    @foreach ($dropItems as $dropItem)
                                        <option value="{{ $dropItem->id }}">{{ $dropItem->name }} ({{ $dropItem->slug }})</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                <span>Tier From</span>
                                <input class="{{ $fieldClass }} w-full" type="number" min="1" max="20" x-model="row.min_tier" :name="`drop_rules[${index}][min_tier]`">
                            </label>
                            <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                <span>Tier To</span>
                                <input class="{{ $fieldClass }} w-full" type="number" min="1" max="20" x-model="row.max_tier" :name="`drop_rules[${index}][max_tier]`">
                            </label>
                            <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                <span>Min Qty</span>
                                <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.min_quantity" :name="`drop_rules[${index}][min_quantity]`">
                            </label>
                            <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                <span>Max Qty</span>
                                <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.max_quantity" :name="`drop_rules[${index}][max_quantity]`">
                            </label>
                            <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                <span>Chance %</span>
                                <input class="{{ $fieldClass }} w-full" type="number" min="0" max="100" step="0.01" x-model="row.drop_chance_percent" :name="`drop_rules[${index}][drop_chance_percent]`">
                            </label>
                            <button type="button" @click="removeRule(index)" class="self-end inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f]" title="Remove drop">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
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
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-[11px] uppercase tracking-[0.18em] text-white/40">
                        <tr>
                            <th class="px-5 py-3 text-left">Job</th>
                            <th class="px-4 py-3 text-left">Level</th>
                            <th class="px-4 py-3 text-left">Pay</th>
                            <th class="px-4 py-3 text-left">XP</th>
                            <th class="px-4 py-3 text-left">Tiering</th>
                            <th class="px-4 py-3 text-left">Drops</th>
                            <th class="px-4 py-3 text-left">Cooldown</th>
                            <th class="px-4 py-3 text-left">Stamina</th>
                            <th class="px-4 py-3 text-left">Assigned</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="jobsList" class="divide-y divide-white/10">
                        @forelse ($jobs as $job)
                            @php($averagePay = (int) round(($job->min_pay + $job->max_pay) / 2))
                            @php($levelBucket = $job->required_level <= 1 ? 'starter' : ($job->required_level <= 5 ? 'mid' : 'late'))
                            @php($status = $job->is_new ? 'new' : ($job->is_starter ? 'starter' : ($job->is_active ? 'active' : 'hidden')))
                            <tr
                                data-job-row
                                data-status="{{ $status }}"
                                data-level-bucket="{{ $levelBucket }}"
                                data-search="{{ str($job->name.' '.$job->slug.' '.$job->description.' '.$job->working_display_message)->lower() }}"
                                class="transition hover:bg-white/[0.035]"
                            >
                                <td class="min-w-[18rem] px-5 py-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-['Teko'] text-2xl uppercase leading-none tracking-[0.06em] text-white">{{ $job->name }}</p>
                                        @if ($job->is_starter)
                                            <span class="rounded-full border border-[#f4d77a]/30 bg-[#f4d77a]/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#f4d77a]">Starter</span>
                                        @endif
                                        @if ($job->is_new)
                                            <span class="rounded-full border border-[#7aa7ff]/30 bg-[#7aa7ff]/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-[#b8ccff]">New</span>
                                        @endif
                                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em] {{ $job->is_active ? 'border-[#7ead59]/30 bg-[#7ead59]/10 text-[#d7edc7]' : 'border-white/10 bg-black/20 text-white/45' }}">
                                            {{ $job->is_active ? 'Active' : 'Hidden' }}
                                        </span>
                                    </div>
                                    <p class="mt-1 font-mono text-xs text-white/38">{{ $job->slug }}</p>
                                    <p class="mt-1 max-w-xl truncate text-xs text-white/50">{{ $job->working_display_message ?: $job->description ?: 'No activity message set.' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 font-semibold text-white">Lv {{ number_format($job->required_level) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <p class="font-semibold text-white">{{ number_format($job->min_pay) }}-{{ number_format($job->max_pay) }}</p>
                                    <p class="text-xs text-white/38">Avg {{ number_format($averagePay) }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#d7edc7]">{{ number_format($job->experience_reward) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <p class="font-semibold text-white">{{ $job->max_tier }} tiers</p>
                                    <p class="text-xs text-white/38">{{ number_format($job->tier_xp_required) }} XP each</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">{{ number_format($job->drops->count()) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ number_format($job->work_cooldown_minutes) }} min</td>
                                <td class="whitespace-nowrap px-4 py-4 font-semibold {{ $job->stamina_decrease > 50 ? 'text-[#f0b29f]' : 'text-white' }}">-{{ number_format($job->stamina_decrease) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ number_format($job->characters_count) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            @click="openId = {{ $job->id }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/75 transition hover:border-[#7ead59]/35 hover:text-[#d7edc7]"
                                            title="Edit"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f] transition hover:bg-[#c65b3f]/20 disabled:cursor-not-allowed disabled:opacity-40"
                                                title="{{ $job->characters_count > 0 ? 'Assigned jobs cannot be deleted' : 'Delete' }}"
                                                @disabled($job->characters_count > 0)
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-10 text-center text-sm text-white/55">No jobs created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                                <input class="{{ $fieldClass }} w-full" name="max_tier" type="number" min="1" max="20" value="{{ $job->max_tier }}" required>
                            </label>
                            <label class="{{ $labelClass }}">
                                <span>Tier XP</span>
                                <input class="{{ $fieldClass }} w-full" name="tier_xp_required" type="number" min="1" value="{{ $job->tier_xp_required }}" required>
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
                            <div x-data='dropRuleBuilder(@json($dropRuleRows))' class="space-y-3 md:col-span-2">
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
                                <template x-for="(row, index) in rows" :key="row.key">
                                    <div class="grid gap-3 rounded-xl border border-white/10 bg-black/20 p-3 lg:grid-cols-[minmax(12rem,1.4fr)_repeat(5,minmax(5rem,0.7fr))_auto]">
                                        <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                            <span>Item</span>
                                            <select class="{{ $fieldClass }} w-full" x-model="row.item_id" :name="`drop_rules[${index}][item_id]`">
                                                <option value="">Select item</option>
                                                @foreach ($dropItems as $dropItem)
                                                    <option value="{{ $dropItem->id }}">{{ $dropItem->name }} ({{ $dropItem->slug }})</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                            <span>Tier From</span>
                                            <input class="{{ $fieldClass }} w-full" type="number" min="1" max="20" x-model="row.min_tier" :name="`drop_rules[${index}][min_tier]`">
                                        </label>
                                        <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                            <span>Tier To</span>
                                            <input class="{{ $fieldClass }} w-full" type="number" min="1" max="20" x-model="row.max_tier" :name="`drop_rules[${index}][max_tier]`">
                                        </label>
                                        <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                            <span>Min Qty</span>
                                            <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.min_quantity" :name="`drop_rules[${index}][min_quantity]`">
                                        </label>
                                        <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                            <span>Max Qty</span>
                                            <input class="{{ $fieldClass }} w-full" type="number" min="1" x-model="row.max_quantity" :name="`drop_rules[${index}][max_quantity]`">
                                        </label>
                                        <label class="space-y-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/45">
                                            <span>Chance %</span>
                                            <input class="{{ $fieldClass }} w-full" type="number" min="0" max="100" step="0.01" x-model="row.drop_chance_percent" :name="`drop_rules[${index}][drop_chance_percent]`">
                                        </label>
                                        <button type="button" @click="removeRule(index)" class="self-end inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#c65b3f]/35 bg-[#c65b3f]/10 text-[#f0b29f]" title="Remove drop">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
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
