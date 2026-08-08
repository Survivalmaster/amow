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
        <tbody class="divide-y divide-white/10">
            @forelse ($tableJobs as $job)
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
                    <td colspan="10" class="px-5 py-10 text-center text-sm text-white/55">{{ $emptyMessage }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
