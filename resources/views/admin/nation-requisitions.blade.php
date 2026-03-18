<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Nation Requisitions</p></x-slot>

    @include('admin.partials.nav')

    <div class="space-y-4">
        @forelse ($requisitions as $requisition)
            <form method="POST" action="{{ route('admin.nation-requisitions.update', $requisition) }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                @method('PATCH')
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $requisition->title }}</p>
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">{{ $requisition->faction->name }} | Submitted by {{ $requisition->submitter->name }}</p>
                        <p class="mt-3 text-sm leading-7 text-white/70">{{ $requisition->details }}</p>
                    </div>
                    <div class="min-w-[240px] rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Current Status</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase">{{ str($requisition->status)->replace('_', ' ')->title() }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Status</span>
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="status">
                            @foreach (['submitted' => 'Submitted', 'being_reviewed' => 'Being Reviewed', 'accepted' => 'Accepted', 'denied' => 'Denied'] as $value => $label)
                                <option value="{{ $value }}" @selected($requisition->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm text-white/70 lg:col-span-2">
                        <span class="uppercase tracking-[0.18em] text-white/45">Admin Reason</span>
                        <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="admin_reason">{{ $requisition->admin_reason }}</textarea>
                    </label>
                </div>

                <div class="mt-4 flex items-center justify-between gap-4">
                    <p class="text-xs uppercase tracking-[0.18em] text-white/40">
                        @if ($requisition->reviewer)
                            Reviewed by {{ $requisition->reviewer->name }} at {{ $requisition->reviewed_at?->format('d M Y H:i') }}
                        @else
                            Awaiting review
                        @endif
                    </p>
                    <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save Verdict</button>
                </div>
            </form>
        @empty
            <div class="rounded-[2rem] border border-dashed border-white/10 bg-white/[0.03] p-6 text-sm text-white/55">No nation requisitions yet.</div>
        @endforelse
    </div>
</x-app-layout>
