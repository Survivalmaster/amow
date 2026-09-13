<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Player Businesses</p>
            <p class="text-sm uppercase tracking-[0.22em] text-white/55">Nation shops, workshops, contracts, and owned services.</p>
        </div>
    </x-slot>

    @include('store._marketplace-tabs', ['marketplaceSection' => 'businesses'])

    <div class="space-y-6">
        <section class="grid gap-6">
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

            <div class="overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-[#17231b] to-[#0d1510] shadow-xl shadow-black/20">
                <div class="flex items-center gap-4 border-b border-white/10 px-5 py-5 sm:px-7">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#7ead59]/25 bg-[#7ead59]/10 text-[#b8d99d]" aria-hidden="true"><i class="fa-solid fa-plus"></i></span>
                    <div>
                        <h2 class="font-['Teko'] text-3xl uppercase leading-none tracking-[0.08em]">Create Business</h2>
                        <p class="mt-1 text-sm text-white/60">Give your next venture a name and a place in Plastica.</p>
                    </div>
                </div>
                @if ($creationLicences->isEmpty())
                    <div class="m-5 rounded-2xl border border-white/10 bg-black/20 p-5 text-sm leading-7 text-white/65">
                        Your character needs a licence marked for player business creation before opening a business.
                    </div>
                @else
                    <form method="POST" action="{{ route('businesses.store') }}">
                        @csrf
                        <div class="grid gap-7 p-5 sm:p-7 lg:grid-cols-2 lg:gap-10">
                            <div class="min-w-0 space-y-5">
                                <div>
                                    <label for="business-name" class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-white/70">Business name</label>
                                    <input id="business-name" name="name" value="{{ old('name') }}" maxlength="255" class="w-full rounded-xl border border-white/15 bg-black/20 px-4 py-3 placeholder:text-white/30 focus:border-[#7ead59] focus:ring-[#7ead59]" placeholder="e.g. Plastica Trading Co." required>
                                    @error('name') <p class="mt-2 text-sm text-red-300">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="business-type" class="mb-2 block text-xs font-semibold uppercase tracking-[0.14em] text-white/70">Business type</label>
                                    <select id="business-type" name="business_type" class="w-full rounded-xl border border-white/15 bg-black/20 px-4 py-3 focus:border-[#7ead59] focus:ring-[#7ead59]" required>
                                        @foreach ($businessTypes as $value => $label)
                                            <option value="{{ $value }}" @selected(old('business_type', array_key_first($businessTypes)) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('business_type') <p class="mt-2 text-sm text-red-300">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="business-description" class="mb-2 flex items-center justify-between gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-white/70">Description <span class="text-[10px] font-normal tracking-normal text-white/40">Optional</span></label>
                                    <textarea id="business-description" name="description" rows="3" maxlength="2000" class="block w-full resize-y rounded-xl border border-white/15 bg-black/20 px-4 py-3 placeholder:text-white/30 focus:border-[#7ead59] focus:ring-[#7ead59]" placeholder="Tell customers what you offer…">{{ old('description') }}</textarea>
                                    @error('description') <p class="mt-2 text-sm text-red-300">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <fieldset class="min-w-0">
                                <legend class="text-xs font-semibold uppercase tracking-[0.14em] text-white/70">Choose your business icon</legend>
                                <p class="mb-4 mt-2 text-sm text-white/45">The face of your business on the commerce board.</p>
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
                                    @foreach ($businessIcons as $class => $label)
                                        <label class="relative cursor-pointer">
                                            <input class="peer sr-only" type="radio" name="icon_class" value="{{ $class }}" @checked(old('icon_class', array_key_first($businessIcons)) === $class) required>
                                            <span class="flex h-24 flex-col items-center justify-center gap-3 rounded-xl border border-white/10 bg-black/15 text-white/55 transition hover:border-[#7ead59]/50 hover:bg-white/5 peer-checked:border-[#7ead59] peer-checked:bg-[#7ead59]/15 peer-checked:text-[#d7edc7] peer-focus-visible:ring-2 peer-focus-visible:ring-[#b8d99d] peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-[#101a13]">
                                                <i class="{{ $class }} text-xl" aria-hidden="true"></i>
                                                <span class="text-center text-[10px] font-semibold uppercase tracking-[0.1em]">{{ $label }}</span>
                                            </span>
                                            <i class="fa-solid fa-circle-check absolute right-2 top-2 hidden text-xs text-[#b8d99d] peer-checked:block" aria-hidden="true"></i>
                                        </label>
                                    @endforeach
                                </div>
                                @error('icon_class') <p class="mt-2 text-sm text-red-300">{{ $message }}</p> @enderror
                            </fieldset>
                        </div>
                        <div class="flex flex-col gap-4 border-t border-white/10 bg-black/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                            <p class="flex items-center gap-2 text-sm text-white/55"><i class="fa-solid fa-certificate text-[#7ead59]" aria-hidden="true"></i> Permit: {{ $creationLicences->first()->name }}</p>
                            <button type="submit" class="amow-action-button inline-flex items-center justify-center gap-3 rounded-xl bg-[#7ead59] px-6 py-3 text-xs font-bold uppercase tracking-[0.14em] text-[#07100c] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#b8d99d]">
                                Open Business <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </section>

        <section aria-labelledby="business-directory-heading">
            <div class="mb-4 flex items-center gap-3">
                <h2 id="business-directory-heading" class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">Business Directory</h2>
                <span class="rounded-full border border-white/10 px-2.5 py-0.5 text-xs text-white/55">{{ number_format($businesses->count()) }}</span>
            </div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($businesses as $business)
                    <a href="{{ route('businesses.show', $business) }}" class="group relative flex min-w-0 flex-col overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-[#18231b] to-[#0d1510] shadow-lg shadow-black/20 transition duration-200 hover:border-[#7ead59]/50 hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#7ead59] motion-safe:hover:-translate-y-1">
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#7ead59]/60 to-transparent" aria-hidden="true"></div>
                        <div class="flex items-center justify-between gap-3 px-6 pt-6">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#7ead59]/25 bg-[#7ead59]/10 text-xl text-[#c9e3b4]">
                                <i class="{{ $business->icon_class }}" aria-hidden="true"></i>
                            </span>
                            @if ($business->owner_character_id === $character->id)
                                <span class="rounded-full border border-[#7ead59]/25 bg-[#7ead59]/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-[#c9e3b4]">Your business</span>
                            @endif
                        </div>
                        <div class="flex-1 px-6 pb-5 pt-5">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#b8d99d]/80">{{ $business->type_label }}</p>
                            <h3 class="mt-2 break-words font-['Teko'] text-3xl uppercase leading-tight tracking-[0.05em] transition group-hover:text-[#d7edc7]">{{ $business->name }}</h3>
                            <p class="mt-1 text-xs text-white/45">{{ $business->faction?->name }}</p>
                            <p class="mt-4 line-clamp-3 text-sm leading-6 text-white/65">{{ $business->description ?: 'No business description yet.' }}</p>
                        </div>
                        <div class="mx-6 flex items-center justify-between gap-3 border-t border-white/10 py-4">
                            <span class="min-w-0 truncate text-xs text-white/55"><i class="fa-solid fa-user mr-1.5 text-white/30" aria-hidden="true"></i>{{ $business->owner?->name }}</span>
                            <span class="shrink-0 text-xs text-white/55"><span class="font-semibold text-[#d4bf7c]">{{ number_format($business->bank_credits) }}</span> banked</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-white/5 bg-black/15 px-6 py-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#b8d99d]">
                            <span>{{ $business->owner_character_id === $character->id ? 'Manage business' : 'View business' }}</span>
                            <i class="fa-solid fa-arrow-right transition-transform motion-safe:group-hover:translate-x-1" aria-hidden="true"></i>
                        </div>
                    </a>
                @empty
                    <div class="rounded-[2rem] border border-white/10 bg-white/5 p-8 text-sm text-white/60 md:col-span-2 xl:col-span-3">No player businesses have opened yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
