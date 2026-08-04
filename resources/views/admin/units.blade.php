<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Units</p>
                <p class="mt-1 text-sm text-white/55">Configure purchasable military units and their firepower values.</p>
            </div>
            <div class="grid grid-cols-3 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                <div class="border-l border-white/10 pl-4"><p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($units->where('is_active', true)->count()) }}</p><p>Active</p></div>
                <div class="border-l border-white/10 pl-4"><p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">{{ number_format($units->max('firepower') ?? 0) }}</p><p>Max FP</p></div>
                <div class="border-l border-white/10 pl-4"><p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f0b29f]">{{ number_format($units->count()) }}</p><p>Total</p></div>
            </div>
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')
    @php($labelClass = 'space-y-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45')
    @php($toggleClass = 'flex items-center gap-3 rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white/70')

    <div x-data="{ openId: null, showCreate: false, query: '', status: 'all', filterRows() { if (!this.$refs.rows) return; [...this.$refs.rows.querySelectorAll('[data-admin-row]')].forEach((row) => row.toggleAttribute('hidden', !((!this.query || row.dataset.search.includes(this.query.toLowerCase())) && (this.status === 'all' || row.dataset.status === this.status)))); } }" x-effect="filterRows()" class="space-y-5">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid flex-1 gap-3 md:grid-cols-3">
                    <label class="{{ $labelClass }} md:col-span-2"><span>Search</span><input x-model.debounce.150ms="query" class="{{ $fieldClass }} w-full" placeholder="Name, slug, category"></label>
                    <label class="{{ $labelClass }}"><span>Status</span><select x-model="status" class="{{ $fieldClass }} w-full"><option value="all">All units</option><option value="active">Active</option><option value="hidden">Hidden</option></select></label>
                </div>
                <button type="button" @click="showCreate = true" class="inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c] transition hover:bg-[#d7edc7]"><i class="fa-solid fa-plus"></i>Create Unit</button>
            </div>
        </section>

        <x-admin.modal open="showCreate" title="Create Unit" subtitle="Add a purchasable unit for the future firepower system." max-width="48rem">
            @include('admin.partials.unit-form', ['unit' => null, 'action' => route('admin.units.store'), 'method' => null, 'close' => 'showCreate = false'])
        </x-admin.modal>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-white/75">
                    <thead class="bg-black/30 text-[11px] uppercase tracking-[0.18em] text-white/40">
                        <tr><th class="px-5 py-3 text-left">Unit</th><th class="px-4 py-3 text-left">Category</th><th class="px-4 py-3 text-left">Firepower</th><th class="px-4 py-3 text-left">Cost</th><th class="px-4 py-3 text-left">Status</th><th class="px-5 py-3 text-right">Actions</th></tr>
                    </thead>
                    <tbody x-ref="rows" class="divide-y divide-white/10">
                        @forelse ($units as $unit)
                            @php($status = $unit->is_active ? 'active' : 'hidden')
                            <tr data-admin-row data-status="{{ $status }}" data-search="{{ str($unit->name.' '.$unit->slug.' '.$unit->category.' '.$unit->description)->lower() }}" class="transition hover:bg-white/[0.035]">
                                <td class="min-w-[18rem] px-5 py-4"><p class="font-semibold text-white">{{ $unit->name }}</p><p class="mt-1 font-mono text-xs text-white/38">{{ $unit->slug }}</p><p class="mt-1 max-w-xl truncate text-xs text-white/50">{{ $unit->description ?: 'No description set.' }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4">{{ str($unit->category)->title() }}</td>
                                <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#d7edc7]">{{ number_format($unit->firepower) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ number_format($unit->cost) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">{{ $unit->is_active ? 'Active' : 'Hidden' }}</td>
                                <td class="px-5 py-4 text-right"><div class="flex justify-end gap-2"><x-admin.icon-button icon="fa-pen" title="Edit" x-on:click="openId = {{ $unit->id }}" /><form method="POST" action="{{ route('admin.units.destroy', $unit) }}">@csrf @method('DELETE')<x-admin.icon-button icon="fa-trash" title="Delete" tone="danger" type="submit" /></form></div></td>
                            </tr>
                            <x-admin.modal open="openId === {{ $unit->id }}" close="openId = null" title="Edit {{ $unit->name }}" subtitle="{{ $unit->slug }}" max-width="48rem">
                                @include('admin.partials.unit-form', ['unit' => $unit, 'action' => route('admin.units.update', $unit), 'method' => 'PATCH', 'close' => 'openId = null'])
                            </x-admin.modal>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-white/55">No units created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
