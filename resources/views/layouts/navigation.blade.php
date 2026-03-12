@php($navCharacter = auth()->user()->character)
@php($creditAmount = $navCharacter?->plastic_credits ?? 0)
@php($characterInitials = $navCharacter ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($navCharacter->name, 0, 2)) : 'AM')
@php(
    $creditTier = match (true) {
        $creditAmount <= 1000 => 'tier1',
        $creditAmount <= 2500 => 'tier2',
        $creditAmount <= 3500 => 'tier3',
        default => 'tier4',
    }
)

<nav x-data="{ open: false }" class="border-b border-white/10 bg-black/25 backdrop-blur lg:min-h-screen lg:border-b-0 lg:border-r">
    <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/amowog.png') }}" alt="Army Men of War" class="h-14 w-auto object-contain" />
            <p class="text-xs uppercase tracking-[0.24em] text-white/45">{{ auth()->user()->name }}</p>
        </a>

        <button @click="open = ! open" class="rounded-2xl border border-white/10 px-3 py-2 text-sm">Menu</button>
    </div>

    <div class="hidden lg:flex lg:sticky lg:top-0 lg:h-screen lg:flex-col lg:justify-between lg:px-6 lg:py-8">
        <div class="flex h-full flex-col">
            <a href="{{ route('dashboard') }}" class="mx-auto block text-center">
                <img src="{{ asset('images/amowog.png') }}" alt="Army Men of War" class="mx-auto h-28 w-auto object-contain" />
                <p class="mt-5 text-xs uppercase tracking-[0.28em] text-white/45">{{ auth()->user()->name }}</p>
            </a>

            @if ($navCharacter)
                <div class="mt-7 rounded-[1.6rem] border border-[#7ead59]/18 bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.015))] p-3 shadow-xl shadow-black/20">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-[#7ead59]/25 bg-[#7ead59]/10 font-['Teko'] text-2xl uppercase tracking-[0.08em] text-[#dbe9c5]">
                            {{ $characterInitials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-['Teko'] text-[1.9rem] uppercase leading-none tracking-[0.06em]">{{ $navCharacter->name }}</p>
                            <p class="mt-2 text-[10px] uppercase tracking-[0.26em] text-white/45">{{ $navCharacter->rank?->name ?? 'Unranked' }}</p>
                            <p class="mt-1 text-[10px] uppercase tracking-[0.26em] text-white/45">{{ ucfirst($navCharacter->role_type) }}</p>
                        </div>
                        <div class="shrink-0 rounded-2xl border border-[#7ead59]/15 bg-[#7ead59]/8 p-2">
                            <img
                                src="{{ asset('images/plastica_money/' . $creditTier . '.png') }}"
                                alt="Plastic Credits tier"
                                class="h-10 w-10 object-contain"
                            />
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between rounded-2xl border border-white/8 bg-black/15 px-3 py-2">
                        <span class="text-[10px] uppercase tracking-[0.26em] text-white/40">Plastic Credits</span>
                        <div class="flex items-center gap-2">
                            <img
                                src="{{ asset('images/plastica_money/' . $creditTier . '.png') }}"
                                alt="Plastic Credits tier"
                                class="h-5 w-5 object-contain"
                            />
                            <span class="text-sm font-semibold text-[#f4ecd0]">{{ number_format($creditAmount) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-8 grid gap-2">
                <a href="{{ route('lobby') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] transition {{ request()->routeIs('lobby') ? 'border-[#7ead59]/35 bg-[#7ead59]/12 text-[#d4e5bc]' : 'border-white/8 bg-white/[0.03] text-white/78 hover:border-white/15 hover:bg-white/[0.05]' }}"><span>Lobby</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
                <a href="{{ route('store.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] transition {{ request()->routeIs('store.*') ? 'border-[#7ead59]/35 bg-[#7ead59]/12 text-[#d4e5bc]' : 'border-white/8 bg-white/[0.03] text-white/78 hover:border-white/15 hover:bg-white/[0.05]' }}"><span>Store</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
                <a href="{{ route('market.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] transition {{ request()->routeIs('market.*') ? 'border-[#7ead59]/35 bg-[#7ead59]/12 text-[#d4e5bc]' : 'border-white/8 bg-white/[0.03] text-white/78 hover:border-white/15 hover:bg-white/[0.05]' }}"><span>Stocks</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
                <a href="{{ route('leaderboards.index') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] transition {{ request()->routeIs('leaderboards.*') ? 'border-[#7ead59]/35 bg-[#7ead59]/12 text-[#d4e5bc]' : 'border-white/8 bg-white/[0.03] text-white/78 hover:border-white/15 hover:bg-white/[0.05]' }}"><span>Leaderboards</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
                <a href="{{ route('characters.show') }}" class="flex items-center justify-between rounded-2xl border px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] transition {{ request()->routeIs('characters.show') ? 'border-[#7ead59]/35 bg-[#7ead59]/12 text-[#d4e5bc]' : 'border-white/8 bg-white/[0.03] text-white/78 hover:border-white/15 hover:bg-white/[0.05]' }}"><span>Character</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78 transition hover:border-white/15 hover:bg-white/[0.05]"><span>Admin</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
                @endif
                <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78 transition hover:border-white/15 hover:bg-white/[0.05]"><span>Account</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            </div>

            <div class="mt-6 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full rounded-2xl bg-[#7ead59] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-[#07100c]">Logout</button>
        </form>
    </div>

    <div x-show="open" x-cloak class="border-t border-white/10 px-4 py-4 lg:hidden">
        <div class="grid gap-2">
            @if ($navCharacter)
                <div class="mb-2 rounded-[1.5rem] border border-[#7ead59]/20 bg-white/5 p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-[#7ead59]/25 bg-[#7ead59]/10 font-['Teko'] text-xl uppercase tracking-[0.08em] text-[#dbe9c5]">
                            {{ $characterInitials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-['Teko'] text-2xl uppercase leading-none tracking-[0.08em]">{{ $navCharacter->name }}</p>
                            <p class="mt-2 text-[10px] uppercase tracking-[0.24em] text-white/45">{{ $navCharacter->rank?->name ?? 'Unranked' }}</p>
                            <p class="mt-1 text-[10px] uppercase tracking-[0.24em] text-white/45">{{ ucfirst($navCharacter->role_type) }}</p>
                        </div>
                        <img
                            src="{{ asset('images/plastica_money/' . $creditTier . '.png') }}"
                            alt="Plastic Credits tier"
                            class="h-10 w-10 shrink-0 object-contain"
                        />
                    </div>
                    <div class="mt-3 flex items-center justify-between rounded-2xl border border-white/8 bg-black/15 px-3 py-2">
                        <span class="text-[10px] uppercase tracking-[0.24em] text-white/40">Plastic Credits</span>
                        <div class="flex items-center gap-2">
                            <img
                                src="{{ asset('images/plastica_money/' . $creditTier . '.png') }}"
                                alt="Plastic Credits tier"
                                class="h-5 w-5 object-contain"
                            />
                            <span class="text-sm font-semibold text-[#f4ecd0]">{{ number_format($creditAmount) }}</span>
                        </div>
                    </div>
                </div>
            @endif
            <a href="{{ route('lobby') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78"><span>Lobby</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            <a href="{{ route('store.index') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78"><span>Store</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            <a href="{{ route('market.index') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78"><span>Stocks</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            <a href="{{ route('leaderboards.index') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78"><span>Leaderboards</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            <a href="{{ route('characters.show') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78"><span>Character</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78"><span>Account</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-white/78"><span>Admin</span><i class="fa-solid fa-chevron-right text-[10px] opacity-60"></i></a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full rounded-2xl bg-[#7ead59] px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-[#07100c]">Logout</button>
            </form>
        </div>
    </div>
</nav>
