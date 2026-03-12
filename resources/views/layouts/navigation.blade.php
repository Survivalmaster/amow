<nav x-data="{ open: false }" class="border-b border-white/10 bg-black/25 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#7ead59]/40 bg-[#7ead59]/10 font-['Teko'] text-3xl tracking-[0.18em] text-[#7ead59]">AM</div>
            <div>
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.16em]">Army Men of War</p>
                <p class="text-xs uppercase tracking-[0.24em] text-white/45">{{ auth()->user()->name }}</p>
            </div>
        </a>

        <button @click="open = ! open" class="rounded-2xl border border-white/10 px-3 py-2 text-sm md:hidden">Menu</button>

        <div class="hidden items-center gap-3 md:flex">
            <a href="{{ route('lobby') }}" class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('lobby') ? 'border-[#7ead59]/35 text-[#7ead59]' : 'bg-white/5' }}">Lobby</a>
            <a href="{{ route('store.index') }}" class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('store.*') ? 'border-[#7ead59]/35 text-[#7ead59]' : 'bg-white/5' }}">Store</a>
            <a href="{{ route('market.index') }}" class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('market.*') ? 'border-[#7ead59]/35 text-[#7ead59]' : 'bg-white/5' }}">Stocks</a>
            <a href="{{ route('leaderboards.index') }}" class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('leaderboards.*') ? 'border-[#7ead59]/35 text-[#7ead59]' : 'bg-white/5' }}">Leaderboards</a>
            <a href="{{ route('characters.show') }}" class="rounded-full border border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] {{ request()->routeIs('characters.show') ? 'border-[#7ead59]/35 text-[#7ead59]' : 'bg-white/5' }}">Character</a>
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.factions.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em]">Admin</a>
            @endif
            <a href="{{ route('profile.edit') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em]">Account</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-full bg-[#7ead59] px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Logout</button>
            </form>
        </div>
    </div>

    <div x-show="open" x-cloak class="border-t border-white/10 px-4 py-4 md:hidden">
        <div class="grid gap-2">
            <a href="{{ route('lobby') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Lobby</a>
            <a href="{{ route('store.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Store</a>
            <a href="{{ route('market.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Stocks</a>
            <a href="{{ route('leaderboards.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Leaderboards</a>
            <a href="{{ route('characters.show') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Character</a>
            <a href="{{ route('profile.edit') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Account</a>
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.factions.index') }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-3 text-center text-xs font-semibold uppercase tracking-[0.2em]">Admin</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full rounded-full bg-[#7ead59] px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Logout</button>
            </form>
        </div>
    </div>
</nav>
