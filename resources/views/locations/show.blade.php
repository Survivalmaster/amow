<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $location->name }}</p>
                <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ $location->city->name }} • {{ $location->city->faction->name }}</p>
            </div>
            <a href="{{ route('cities.show', $location->city->slug) }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em]">Back to City</a>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Location Brief</p>
                <p class="mt-4 text-sm leading-7 text-white/70">{{ $location->description }}</p>
                @if ($location->slug === 'go-to-work')
                    <form method="POST" action="{{ route('work.store', $location) }}" class="mt-6">
                        @csrf
                        <button class="w-full rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Work for 10-30 Credits</button>
                    </form>
                @endif
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Transmit Message</p>
                <form method="POST" action="{{ route('messages.store', $location) }}" class="mt-4">
                    @csrf
                    <textarea name="message" class="min-h-40 w-full rounded-3xl border border-white/10 bg-black/25 px-4 py-4" maxlength="500" placeholder="Write in-character chat here..." required>{{ old('message') }}</textarea>
                    <button class="mt-4 rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Send</button>
                </form>
            </div>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Location Chat</p>
            <div class="mt-5 space-y-4">
                @forelse ($messages as $message)
                    <article class="rounded-3xl border border-white/10 bg-black/20 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $message->character->name }}</p>
                                <p class="text-xs uppercase tracking-[0.22em] text-white/45">{{ $message->character->rank->name }}</p>
                            </div>
                            <p class="text-xs uppercase tracking-[0.22em] text-white/40">{{ $message->created_at->format('d M H:i') }}</p>
                        </div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-white/75">{{ $message->message }}</p>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/10 px-4 py-8 text-center text-sm uppercase tracking-[0.2em] text-white/45">
                        No chatter yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
