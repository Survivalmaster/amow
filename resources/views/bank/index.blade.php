<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Bank</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">{{ number_format($character->plastic_credits) }} Plastic Credits available</p>
        </div>
    </x-slot>

    @include('store._marketplace-tabs')

    <div class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Banking Overview</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] border border-[#7ead59]/25 bg-[#7ead59]/10 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Liquid Credits</p>
                        <p class="mt-2 font-['Teko'] text-5xl uppercase leading-none text-[#d7edc7]">{{ number_format($character->plastic_credits) }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Faction</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase leading-none text-[#f4ecd0]">{{ $character->faction?->name ?? 'No Faction' }}</p>
                        <p class="mt-2 text-sm text-white/55">{{ number_format($sameFactionCharacters->count()) }} transfer {{ $sameFactionCharacters->count() === 1 ? 'recipient' : 'recipients' }} available</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('bank.transfers.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Send Credits</p>
                <p class="mt-2 text-sm text-white/60">Transfer Plastic Credits directly to another player in your faction.</p>

                <div class="mt-5 grid gap-4">
                    <label class="grid gap-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Recipient</span>
                        <select name="recipient_character_id" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-[#f4ecd0]" required>
                            <option value="">Choose a faction member</option>
                            @foreach ($sameFactionCharacters as $recipient)
                                <option value="{{ $recipient->id }}" @selected((int) old('recipient_character_id') === $recipient->id)>
                                    {{ $recipient->name }} - {{ $recipient->rank?->name ?? 'Unranked' }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Amount</span>
                        <input name="amount" type="number" min="1" max="{{ max(1, $character->plastic_credits) }}" value="{{ old('amount') }}" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-[#f4ecd0]" placeholder="250" required>
                    </label>

                    <label class="grid gap-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-white/45">Note</span>
                        <input name="note" maxlength="160" value="{{ old('note') }}" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3 text-[#f4ecd0]" placeholder="Ammo restock, mission split, trade payment">
                    </label>
                </div>

                <button class="mt-5 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]" @disabled($sameFactionCharacters->isEmpty())>
                    Send Transfer
                </button>
            </form>
        </section>

        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Recent Transfers</p>
            <div class="mt-5 space-y-3">
                @forelse ($recentTransfers as $transfer)
                    @php($meta = collect($transfer->metadata ?? []))
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-[#f4ecd0]">{{ $transfer->description }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.18em] text-white/38">{{ $transfer->created_at->format('j M Y H:i') }}</p>
                                @if ($meta->get('note'))
                                    <p class="mt-2 text-sm text-white/60">{{ $meta->get('note') }}</p>
                                @endif
                            </div>
                            <p class="shrink-0 font-['Teko'] text-3xl uppercase {{ $transfer->amount >= 0 ? 'text-[#7ead59]' : 'text-[#f0b29f]' }}">
                                {{ $transfer->amount >= 0 ? '+' : '' }}{{ number_format($transfer->amount) }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-5 text-sm text-white/58">
                        No player transfers yet.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
