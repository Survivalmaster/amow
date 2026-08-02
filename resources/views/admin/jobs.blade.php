<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Jobs</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        @php($fieldClass = 'rounded-2xl border border-white/10 bg-black/25 px-4 py-3')
        @php($labelClass = 'space-y-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/45')

        <form method="POST" action="{{ route('admin.jobs.store') }}" class="grid gap-4 rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 lg:grid-cols-2 xl:grid-cols-4">
            @csrf
            <label class="{{ $labelClass }}">
                <span>Job Name</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="name" placeholder="Begger" required>
            </label>
            <label class="{{ $labelClass }}">
                <span>Slug</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="slug" placeholder="begger" required>
            </label>
            <label class="{{ $labelClass }}">
                <span>Minimum Pay</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="min_pay" type="number" min="0" placeholder="5" required>
            </label>
            <label class="{{ $labelClass }}">
                <span>Maximum Pay</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="max_pay" type="number" min="0" placeholder="25" required>
            </label>
            <label class="{{ $labelClass }}">
                <span>Required Level</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="required_level" type="number" min="0" placeholder="0" required>
            </label>
            <label class="{{ $labelClass }}">
                <span>Cooldown Minutes</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="work_cooldown_minutes" type="number" min="1" placeholder="2" required>
            </label>
            <label class="{{ $labelClass }}">
                <span>Stamina Decrease</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="stamina_decrease" type="number" min="0" max="100" placeholder="15" value="0" required>
            </label>
            <label class="{{ $labelClass }}">
                <span>XP Reward</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="experience_reward" type="number" min="0" placeholder="5" value="5" required>
            </label>
            <label class="{{ $labelClass }} xl:col-span-2">
                <span>Activity Message</span>
                <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="working_display_message" placeholder="Begging in the city.">
            </label>
            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                <input type="checkbox" name="is_starter" value="1">
                Starter job
            </label>
            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>
            <label class="{{ $labelClass }} xl:col-span-4">
                <span>Description</span>
                <textarea class="{{ $fieldClass }} min-h-24 w-full normal-case tracking-normal text-white" name="description" placeholder="The most simple job in Plastica. Beg for money."></textarea>
            </label>
            <div class="xl:col-span-4 flex justify-end">
                <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Job</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                        <tr>
                            <th class="px-5 py-4 text-left">Name</th>
                            <th class="px-5 py-4 text-left">Level</th>
                            <th class="px-5 py-4 text-left">Pay</th>
                            <th class="px-5 py-4 text-left">XP</th>
                            <th class="px-5 py-4 text-left">Cooldown</th>
                            <th class="px-5 py-4 text-left">Stamina</th>
                            <th class="px-5 py-4 text-left">Activity</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($jobs as $job)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-white">{{ $job->name }}</td>
                                <td class="px-5 py-4">Level {{ $job->required_level }}</td>
                                <td class="px-5 py-4">{{ number_format($job->min_pay) }} - {{ number_format($job->max_pay) }}</td>
                                <td class="px-5 py-4">{{ number_format($job->experience_reward) }}</td>
                                <td class="px-5 py-4">{{ $job->work_cooldown_minutes }} min</td>
                                <td class="px-5 py-4">-{{ $job->stamina_decrease }}</td>
                                <td class="px-5 py-4 text-white/60">{{ $job->working_display_message ?: 'No live activity set.' }}</td>
                                <td class="px-5 py-4">
                                    {{ $job->is_active ? 'Active' : 'Hidden' }}
                                    @if ($job->is_starter)
                                        | Starter
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openId = openId === {{ $job->id }} ? null : {{ $job->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $job->id }}" x-cloak>
                                <td colspan="9" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2 xl:grid-cols-4">
                                        @csrf
                                        @method('PATCH')
                                        <label class="{{ $labelClass }}">
                                            <span>Job Name</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="name" value="{{ $job->name }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span>Slug</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="slug" value="{{ $job->slug }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span>Minimum Pay</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="min_pay" type="number" min="0" value="{{ $job->min_pay }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span>Maximum Pay</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="max_pay" type="number" min="0" value="{{ $job->max_pay }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span>Required Level</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="required_level" type="number" min="0" value="{{ $job->required_level }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span>Cooldown Minutes</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="work_cooldown_minutes" type="number" min="1" value="{{ $job->work_cooldown_minutes }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span>Stamina Decrease</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="stamina_decrease" type="number" min="0" max="100" value="{{ $job->stamina_decrease }}" required>
                                        </label>
                                        <label class="{{ $labelClass }}">
                                            <span>XP Reward</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="experience_reward" type="number" min="0" value="{{ $job->experience_reward }}" required>
                                        </label>
                                        <label class="{{ $labelClass }} xl:col-span-2">
                                            <span>Activity Message</span>
                                            <input class="{{ $fieldClass }} w-full normal-case tracking-normal text-white" name="working_display_message" value="{{ $job->working_display_message }}" placeholder="Working display message">
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_starter" value="1" @checked($job->is_starter)>
                                            Starter job
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_active" value="1" @checked($job->is_active)>
                                            Active
                                        </label>
                                        <label class="{{ $labelClass }} xl:col-span-4">
                                            <span>Description</span>
                                            <textarea class="{{ $fieldClass }} min-h-24 w-full normal-case tracking-normal text-white" name="description">{{ $job->description }}</textarea>
                                        </label>
                                        <div class="xl:col-span-4 flex justify-end">
                                            <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
