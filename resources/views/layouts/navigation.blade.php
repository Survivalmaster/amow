@php($navCharacter = auth()->user()->character)

<nav x-data="{ open: false }" class="border-b border-white/10 bg-black/25 backdrop-blur lg:min-h-screen lg:border-b-0 lg:border-r">
    <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#7ead59]/40 bg-[#7ead59]/10 font-['Teko'] text-3xl tracking-[0.18em] text-[#7ead59]">AM</div>
            <div>
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.16em]">Army Men of War</p>
                <p class="text-xs uppercase tracking-[0.24em] text-white/45">{{ auth()->user()->name }}</p>
            </div>
        </a>

        <button @click="open = ! open" class="rounded-2xl border border-white/10 px-3 py-2 text-sm">Menu</button>
    </div>

    <div class="hidden lg:flex lg:sticky lg:top-0 lg:h-screen lg:flex-col lg:justify-between lg:px-6 lg:py-8">
        <div>
            <a href="{{ route('dashboard') }}" class="flex items-start gap-4">
                <div class="mt-2 flex h-12 w-12 items-center justify-center rounded-2xl border border-[#7ead59]/40 bg-[#7ead59]/10 font-['Teko'] text-3xl tracking-[0.18em] text-[#7ead59]">AM</div>
                <div>
                    <p class="font-['Teko'] text-4xl uppercase leading-none tracking-[0.18em]">Army</p>
                    <p class="font-['Teko'] text-4xl uppercase leading-none tracking-[0.18em]">Men</p>
                    <p class="font-['Teko'] text-4xl uppercase leading-none tracking-[0.18em]">of</p>
                    <p class="font-['Teko'] text-4xl uppercase leading-none tracking-[0.18em]">War</p>
                    <p class="mt-4 text-xs uppercase tracking-[0.24em] text-white/45">{{ auth()->user()->name }}</p>
                </div>
            </a>

            @if ($navCharacter)
                <div class="mt-8 rounded-[2rem] border border-white/10 bg-white/5 p-5">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.08em]">{{ $navCharacter->name }}</p>
                    <p class="mt-2 text-xs uppercase tracking-[0.2em] text-white/45">{{ $navCharacter->rank->name }}</p>
                    <p class="mt-1 text-xs uppercase tracking-[0.2em] text-white/45">{{ ucfirst($navCharacter->role_type) }}</p>
                    <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-[#7ead59]/15 px-4 py-2 text-[#c2a84f]">
                        <i class="fa-solid fa-coins"></i>
                        <span class="font-semibold text-sm text-[#f4ecd0]">{{ number_format($navCharacter->plastic_credits) }}</span>
                    </div>
                </div>
            @endif

            <div class="mt-8 grid gap-3">
                <a href="{{ route('lobby') }}" class="rounded-full border border-white/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('lobby') ? 'border-[#7ead59]/35 bg-[#7ead59]/10 text-[#7ead59]' : 'bg-white/5' }}">Lobby</a>
                <a href="{{ route('store.index') }}" class="rounded-full border border-white/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('store.*') ? 'border-[#7ead59]/35 bg-[#7ead59]/10 text-[#7ead59]' : 'bg-white/5' }}">Store</a>
                <a href="{{ route('market.index') }}" class="rounded-full border border-white/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('market.*') ? 'border-[#7ead59]/35 bg-[#7ead59]/10 text-[#7ead59]' : 'bg-white/5' }}">Stocks</a>
                <a href="{{ route('leaderboards.index') }}" class="rounded-full border border-white/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('leaderboards.*') ? 'border-[#7ead59]/35 bg-[#7ead59]/10 text-[#7ead59]' : 'bg-white/5' }}">Leaderboards</a>
                <a href="{{ route('characters.show') }}" class="rounded-full border border-white/10 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('characters.show') ? 'border-[#7ead59]/35 bg-[#7ead59]/10 text-[#7ead59]' : 'bg-white/5' }}">Character</a>
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Admin</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Account</a>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full rounded-full bg-[#7ead59] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Logout</button>
        </form>
    </div>

    <div x-show="open" x-cloak class="border-t border-white/10 px-4 py-4 lg:hidden">
        <div class="grid gap-2">
            @if ($navCharacter)
                <div class="mb-2 rounded-[1.5rem] border border-white/10 bg-white/5 px-4 py-4">
                    <p class="font-['Teko'] text-2xl uppercase tracking-[0.08em]">{{ $navCharacter->name }}</p>
                    <p class="mt-1 text-[11px] uppercase tracking-[0.2em] text-white/45">{{ $navCharacter->rank->name }}</p>
                    <p class="mt-1 text-[11px] uppercase tracking-[0.2em] text-white/45">{{ ucfirst($navCharacter->role_type) }}</p>
                    <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-[#7ead59]/15 px-3 py-2 text-[#c2a84f]">
                        <i class="fa-solid fa-coins"></i>
                        <span class="font-semibold text-sm text-[#f4ecd0]">{{ number_format($navCharacter->plastic_credits) }}</span>
                    </div>
                </div>
            @endif
            <a href="{{ route('lobby') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Lobby</a>
            <a href="{{ route('store.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Store</a>
            <a href="{{ route('market.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Stocks</a>
            <a href="{{ route('leaderboards.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Leaderboards</a>
            <a href="{{ route('characters.show') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Character</a>
            <a href="{{ route('profile.edit') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Account</a>
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Admin</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full rounded-full bg-[#7ead59] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Logout</button>
            </form>
        </div>
    </div>
</nav>
