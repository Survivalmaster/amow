<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Player Businesses</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Nation shops, workshops, contracts, and owned services.</p>
        </div>
    </x-slot>

    @include('store._marketplace-tabs', ['marketplaceSection' => 'businesses'])

    <div class="space-y-6">
        <section class="grid gap-6 xl:grid-cols-[1fr_0.85fr]">
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[#7ead59]/30 bg-[#7ead59]/10 text-2xl text-[#d7edc7]">
                        <i class="fa-solid fa-shop"></i>
                    </div>
                    <div>
                        <p class="font-['Teko'] text-4xl uppercase tracking-[0.1em]">Commerce Board</p>
                        <p class="text-sm text-white/60">{{ number_format($businesses->count()) }} registered businesses across Plastica.</p>
                    </div>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Owned By You</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#f4ecd0]">{{ number_format($businesses->where('owner_character_id', $character->id)->count()) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Business Banked</p>
                        <p class="mt-2 font-['Teko'] text-3xl uppercase text-[#c2a84f]">{{ number_format($businesses->sum('bank_credits')) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/45">Your Permit</p>
                        <p class="mt-2 truncate font-['Teko'] text-3xl uppercase text-[#7ead59]">{{ $creationLicences->first()?->name ?? 'Missing' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Business</p>
                @if ($creationLicences->isEmpty())
                    <div class="mt-5 rounded-2xl border border-white/10 bg-black/20 p-5 text-sm leading-7 text-white/65">
                        Your character needs a licence marked for player business creation before opening a business.
                    </div>
                @else
                    <form method="POST" action="{{ route('businesses.store') }}" class="mt-5 grid gap-3">
                        @csrf
                        <input name="name" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="Business name" required>
                        <select name="business_type" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" required>
                            @foreach ($businessTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="grid gap-2 sm:grid-cols-4">
                            @foreach ($businessIcons as $class => $label)
                                <label class="cursor-pointer rounded-2xl border border-white/10 bg-black/20 p-3 text-center transition hover:border-[#7ead59]/40">
                                    <input class="peer sr-only" type="radio" name="icon_class" value="{{ $class }}" @checked($loop->first) required>
                                    <span class="block rounded-xl py-3 peer-checked:bg-[#7ead59]/15 peer-checked:text-[#d7edc7]">
                                        <i class="{{ $class }} text-xl"></i>
                                        <span class="mt-2 block text-[10px] uppercase tracking-[0.16em] text-white/55">{{ $label }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <textarea name="description" class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" placeholder="What does this business do?"></textarea>
                        <button class="amow-action-button inline-flex items-center justify-center gap-2 rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#07100c]">
                            <i class="fa-solid fa-plus"></i>
                            Open Business
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($businesses as $business)
                <a href="{{ route('businesses.show', $business) }}" class="group rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 transition hover:border-[#7ead59]/35 hover:bg-[#7ead59]/10">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-['Teko'] text-4xl uppercase tracking-[0.08em]">{{ $business->name }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.2em] text-white/45">{{ $business->faction?->name }} | {{ $business->type_label }}</p>
                        </div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-black/20 text-xl text-[#d7edc7]">
                            <i class="{{ $business->icon_class }}"></i>
                        </span>
                    </div>
                    <p class="mt-4 line-clamp-3 text-sm leading-7 text-white/65">{{ $business->description ?: 'No business description yet.' }}</p>
                    <div class="mt-5 flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/55">
                        <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1.5">{{ number_format($business->bank_credits) }} banked</span>
                        <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1.5">{{ $business->owner?->name }}</span>
                        @if ($business->owner_character_id === $character->id)
                            <span class="rounded-full border border-[#7ead59]/30 bg-[#7ead59]/10 px-3 py-1.5 text-[#d7edc7]">Yours</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 text-sm text-white/60 md:col-span-2 xl:col-span-3">No player businesses have opened yet.</div>
            @endforelse
        </section>
    </div>
</x-app-layout>
