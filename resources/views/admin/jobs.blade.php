<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Jobs</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="{ openId: null }" class="space-y-6">
        <form method="POST" action="{{ route('admin.jobs.store') }}" class="grid gap-4 rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 lg:grid-cols-2 xl:grid-cols-4">
            @csrf
            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Job name" required>
            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" placeholder="job-slug" required>
            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="min_pay" type="number" min="0" placeholder="Min pay" required>
            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="max_pay" type="number" min="0" placeholder="Max pay" required>
            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_level" type="number" min="0" placeholder="Required level" required>
            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="work_cooldown_minutes" type="number" min="1" placeholder="Cooldown minutes" required>
            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                <input type="checkbox" name="is_starter" value="1">
                Starter job
            </label>
            <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>
            <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 xl:col-span-4" name="description" placeholder="Job description"></textarea>
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
                            <th class="px-5 py-4 text-left">Cooldown</th>
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
                                <td class="px-5 py-4">{{ $job->work_cooldown_minutes }} min</td>
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
                                <td colspan="6" class="px-5 pb-5">
                                    <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5 lg:grid-cols-2 xl:grid-cols-4">
                                        @csrf
                                        @method('PATCH')
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $job->name }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="slug" value="{{ $job->slug }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="min_pay" type="number" min="0" value="{{ $job->min_pay }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="max_pay" type="number" min="0" value="{{ $job->max_pay }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="required_level" type="number" min="0" value="{{ $job->required_level }}" required>
                                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="work_cooldown_minutes" type="number" min="1" value="{{ $job->work_cooldown_minutes }}" required>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_starter" value="1" @checked($job->is_starter)>
                                            Starter job
                                        </label>
                                        <label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-sm text-white/70">
                                            <input type="checkbox" name="is_active" value="1" @checked($job->is_active)>
                                            Active
                                        </label>
                                        <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 xl:col-span-4" name="description">{{ $job->description }}</textarea>
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
