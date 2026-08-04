<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Skirmishes</p>
                <p class="mt-1 text-sm text-white/55">Prepare fight scenarios players can join later.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($skirmishes->whereIn('status', ['open', 'active'])->count()) }}</p>
                    <p>Live</p>
                </div>
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">{{ number_format($skirmishes->where('status', 'draft')->count()) }}</p>
                    <p>Draft</p>
                </div>
                <div class="border-l border-white/10 pl-4">
                    <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f0b29f]">{{ number_format($skirmishes->count()) }}</p>
                    <p>Total</p>
                </div>
            </div>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')
    @php($labelClass = 'space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45')

    <div x-data="{ openId: null, showCreate: false, query: '', status: 'all', filterRows() { if (!this.$refs.rows) return; [...this.$refs.rows.querySelectorAll('[data-admin-row]')].forEach((row) => row.toggleAttribute('hidden', !((!this.query || row.dataset.search.includes(this.query.toLowerCase())) && (this.status === 'all' || row.dataset.status === this.status)))); } }" x-effect="filterRows()" class="space-y-5">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid flex-1 gap-3 md:grid-cols-3">
                    <label class="{{ $labelClass }} md:col-span-2">
                        <span>Search</span>
                        <input x-model.debounce.150ms="query" class="{{ $fieldClass }} w-full" placeholder="Title, slug, details">
                    </label>
                    <label class="{{ $labelClass }}">
                        <span>Status</span>
                        <select x-model="status" class="{{ $fieldClass }} w-full">
                            <option value="all">All skirmishes</option>
                            <option value="draft">Draft</option>
                            <option value="open">Open</option>
                            <option value="active">Active</option>
                            <option value="resolved">Resolved</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </label>
                </div>
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]"><i class="fa-solid fa-plus"></i>Create Skirmish</button>
            </div>
        </section>

        <x-admin.modal open="showCreate" title="Create Skirmish" subtitle="Set the first fight setup details." max-width="48rem">
            @include('admin.partials.skirmish-form', ['skirmish' => null, 'action' => route('admin.skirmishes.store'), 'method' => null, 'close' => 'showCreate = false'])
        </x-admin.modal>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-[11px] uppercase tracking-[0.18em] text-white/40">
                        <tr>
                            <th class="px-5 py-3 text-left">Skirmish</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Starts</th>
                            <th class="px-4 py-3 text-left">Ends</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @forelse ($skirmishes as $skirmish)
                            <tr data-admin-row data-status="{{ $skirmish->status }}" data-search="{{ str($skirmish->title.' '.$skirmish->slug.' '.$skirmish->description)->lower() }}" class="transition hover:bg-white/[0.035]">
                                <td class="min-w-[20rem] px-5 py-4">
                                    <p class="font-semibold text-white">{{ $skirmish->title }}</p>
                                    <p class="mt-1 font-mono text-xs text-white/38">{{ $skirmish->slug }}</p>
                                    <p class="mt-1 max-w-xl truncate text-xs text-white/50">{{ $skirmish->description ?: 'No description set.' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 uppercase tracking-[0.14em]">{{ $skirmish->status }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $skirmish->starts_at?->format('d M Y H:i') ?? 'Unset' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $skirmish->ends_at?->format('d M Y H:i') ?? 'Unset' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $skirmish->id }}" />
                                        <form method="POST" action="{{ route('admin.skirmishes.destroy', $skirmish) }}">@csrf @method('DELETE')<x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" /></form>
                                    </div>
                                </td>
                            </tr>
                            <x-admin.modal open="openId === {{ $skirmish->id }}" close="openId = null" title="Edit {{ $skirmish->title }}" subtitle="{{ $skirmish->slug }}" max-width="48rem">
                                @include('admin.partials.skirmish-form', ['skirmish' => $skirmish, 'action' => route('admin.skirmishes.update', $skirmish), 'method' => 'PATCH', 'close' => 'openId = null'])
                            </x-admin.modal>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-white/55">No skirmishes created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
