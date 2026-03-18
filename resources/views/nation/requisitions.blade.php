<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Nation Requisitions</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ $character->faction->name }} leadership request log.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">Submit Request</p>
            <p class="mt-2 text-sm text-white/65">Only one outstanding request can exist at a time for the nation.</p>

            @if ($hasOutstandingRequest)
                <div class="mt-5 rounded-[1.5rem] border border-[#c65b3f]/35 bg-[#c65b3f]/10 px-4 py-4 text-sm text-[#f0b29f]">
                    An existing requisition is still being processed. Submit a new one after the current request is accepted or denied.
                </div>
            @else
                <form method="POST" action="{{ route('nation.requisitions.store') }}" class="mt-5 grid gap-4">
                    @csrf
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Request Title</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="title" required>
                    </label>
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Details</span>
                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="details" required></textarea>
                    </label>
                    <div>
                        <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">Submit Requisition</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">Request History</p>
            <div class="mt-5 space-y-4">
                @forelse ($requisitions as $requisition)
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $requisition->title }}</p>
                                <p class="text-xs uppercase tracking-[0.18em] text-white/45">{{ str($requisition->status)->replace('_', ' ')->title() }}</p>
                            </div>
                            <p class="text-xs uppercase tracking-[0.18em] text-white/40">{{ $requisition->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-white/70">{{ $requisition->details }}</p>
                        @if ($requisition->admin_reason)
                            <div class="mt-4 rounded-2xl border border-[#7ead59]/25 bg-[#7ead59]/10 px-4 py-3 text-sm text-white/80">
                                <span class="block text-xs uppercase tracking-[0.18em] text-white/45">Admin Reason</span>
                                {{ $requisition->admin_reason }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-white/45">No requisitions submitted yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
