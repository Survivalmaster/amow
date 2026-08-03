<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Character Logs</p>
                <p class="mt-1 text-sm text-white/55">Review a character's money, job, progression, and admin-change history.</p>
            </div>
            @if ($selectedCharacter)
                <div class="grid grid-cols-3 gap-2 text-right text-xs uppercase tracking-[0.18em] text-white/45">
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#d7edc7]">{{ number_format($selectedCharacter->plastic_credits) }}</p>
                        <p>Credits</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f4d77a]">{{ number_format($selectedCharacter->level) }}</p>
                        <p>Level</p>
                    </div>
                    <div class="border-l border-white/10 pl-4">
                        <p class="font-['Teko'] text-3xl leading-none tracking-normal text-[#f0b29f]">{{ number_format($transactions?->total() ?? 0) }}</p>
                        <p>Events</p>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    @include('admin.partials.nav')

    @php($fieldClass = 'rounded-xl border border-white/10 bg-black/25 px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#7ead59]/50 focus:bg-black/35')

    <div class="grid gap-6 xl:grid-cols-[24rem_minmax(0,1fr)]">
        <aside class="space-y-4">
            <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                <form method="GET" action="{{ route('admin.character-logs.index') }}">
                    <label class="space-y-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/45">
                        <span>Character</span>
                        <select class="{{ $fieldClass }} w-full" name="character_id" onchange="this.form.submit()">
                            @foreach ($characters as $character)
                                <option value="{{ $character->id }}" @selected($selectedCharacter?->id === $character->id)>
                                    {{ $character->name }} | {{ $character->user?->email }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </form>
            </section>

            @if ($selectedCharacter)
                <section class="rounded-[2rem] border border-white/10 bg-white/5 p-5 shadow-2xl shadow-black/30">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">{{ $selectedCharacter->name }}</p>
                    <div class="mt-4 space-y-2 text-sm text-white/65">
                        <p><span class="text-white/40">User:</span> {{ $selectedCharacter->user?->email ?? 'Unknown' }}</p>
                        <p><span class="text-white/40">Discord:</span> {{ $selectedCharacter->user?->discord_username ?: 'Not linked' }}</p>
                        <p><span class="text-white/40">Faction:</span> {{ $selectedCharacter->faction?->name ?? 'Unknown' }}</p>
                        <p><span class="text-white/40">Rank:</span> {{ $selectedCharacter->rank?->name ?? 'Unknown' }}</p>
                        <p><span class="text-white/40">Job:</span> {{ $selectedCharacter->currentJob?->name ?? $selectedCharacter->starting_occupation }}</p>
                        <p><span class="text-white/40">Created:</span> {{ $selectedCharacter->created_at?->format('d M Y H:i') }}</p>
                    </div>
                </section>
            @endif
        </aside>

        <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
            <div class="border-b border-white/10 px-5 py-4">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em] text-white">Timeline</p>
            </div>

            <div class="divide-y divide-white/10">
                @forelse ($transactions ?? [] as $transaction)
                    @php($metadata = collect($transaction->metadata ?? [])->filter(fn ($value) => ! is_array($value) && $value !== null && $value !== ''))
                    <article class="grid gap-4 px-5 py-4 md:grid-cols-[11rem_minmax(0,1fr)_8rem]">
                        <div class="text-xs uppercase tracking-[0.16em] text-white/42">
                            <p>{{ $transaction->created_at->format('d M Y') }}</p>
                            <p class="mt-1 text-white/60">{{ $transaction->created_at->format('H:i:s') }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="font-['Teko'] text-2xl uppercase leading-none tracking-[0.08em] text-white">{{ str($transaction->type)->replace('_', ' ') }}</p>
                            <p class="mt-2 text-sm leading-6 text-white/68">{{ $transaction->description }}</p>
                            @if ($metadata->isNotEmpty())
                                <dl class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($metadata as $key => $value)
                                        <div class="rounded-xl border border-white/10 bg-black/20 px-3 py-2">
                                            <dt class="text-[10px] font-semibold uppercase tracking-[0.16em] text-white/35">{{ str($key)->replace('_', ' ') }}</dt>
                                            <dd class="mt-1 break-words text-sm text-white/75">{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @endif
                        </div>
                        <div class="text-right font-['Teko'] text-3xl uppercase {{ $transaction->amount > 0 ? 'text-[#7ead59]' : ($transaction->amount < 0 ? 'text-[#c65b3f]' : 'text-white/45') }}">
                            {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount) }}
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-white/55">No log entries for this character yet.</div>
                @endforelse
            </div>

            @if ($transactions && $transactions->hasPages())
                <div class="border-t border-white/10 px-5 py-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
