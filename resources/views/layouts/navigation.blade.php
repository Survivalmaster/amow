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
@php(
    $primaryNav = [
        ['label' => 'My Dashboard', 'route' => 'lobby', 'match' => 'lobby', 'icon' => 'fa-solid fa-gauge-high'],
        ['label' => 'Store', 'route' => 'store.index', 'match' => 'store.*', 'icon' => 'fa-solid fa-store'],
        ['label' => 'Stock Market', 'route' => 'market.index', 'match' => 'market.*', 'icon' => 'fa-solid fa-chart-line'],
        ['label' => 'Leaderboards', 'route' => 'leaderboards.index', 'match' => 'leaderboards.*', 'icon' => 'fa-solid fa-trophy'],
    ]
)
@php(
    $operationsNav = array_values(array_filter([
        ['label' => 'Character', 'route' => 'characters.show', 'match' => 'characters.show', 'icon' => 'fa-solid fa-id-badge'],
        ['label' => 'Account', 'route' => 'profile.edit', 'match' => 'profile.edit', 'icon' => 'fa-solid fa-gear'],
        auth()->user()->is_admin ? ['label' => 'Admin', 'route' => 'admin.dashboard', 'match' => 'admin.*', 'icon' => 'fa-solid fa-shield-halved'] : null,
    ]))
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
                <div class="mt-7 rounded-[1.35rem] border border-[#7ead59]/16 bg-[linear-gradient(180deg,rgba(255,255,255,0.05),rgba(255,255,255,0.015))] p-3 shadow-xl shadow-black/20">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-[#7ead59]/25 bg-[#7ead59]/10 font-['Teko'] text-xl uppercase tracking-[0.08em] text-[#dbe9c5]">
                            {{ $characterInitials }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-['Teko'] text-[1.55rem] uppercase leading-none tracking-[0.05em]">{{ $navCharacter->name }}</p>
                            <p class="mt-2 text-[10px] uppercase tracking-[0.26em] text-white/45">{{ $navCharacter->rank?->name ?? 'Unranked' }}</p>
                            <p class="mt-1 text-[10px] uppercase tracking-[0.26em] text-white/45">{{ ucfirst($navCharacter->role_type) }}</p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between rounded-xl border border-white/8 bg-black/15 px-3 py-2">
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

            <div class="mt-8">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Dashboard</p>
                <div class="mt-3 grid gap-1">
                    @foreach ($primaryNav as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ request()->routeIs($item['match']) ? 'bg-[#c9d7ea] text-[#133b73]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                        >
                            <i class="{{ $item['icon'] }} w-5 text-center {{ request()->routeIs($item['match']) ? 'text-[#2a73d8]' : 'text-[#6da2ff]' }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-7">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Operations</p>
                <div class="mt-3 grid gap-1">
                    @foreach ($operationsNav as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ request()->routeIs($item['match']) ? 'bg-[#c9d7ea] text-[#133b73]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                        >
                            <i class="{{ $item['icon'] }} w-5 text-center {{ request()->routeIs($item['match']) ? 'text-[#2a73d8]' : 'text-[#6da2ff]' }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full rounded-xl border border-[#7ead59]/35 bg-[#7ead59]/90 px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-[#07100c]">Logout</button>
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
            <p class="mt-2 px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Dashboard</p>
            @foreach ($primaryNav as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ request()->routeIs($item['match']) ? 'bg-[#c9d7ea] text-[#133b73]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                >
                    <i class="{{ $item['icon'] }} w-5 text-center {{ request()->routeIs($item['match']) ? 'text-[#2a73d8]' : 'text-[#6da2ff]' }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <p class="mt-4 px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Operations</p>
            @foreach ($operationsNav as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ request()->routeIs($item['match']) ? 'bg-[#c9d7ea] text-[#133b73]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                >
                    <i class="{{ $item['icon'] }} w-5 text-center {{ request()->routeIs($item['match']) ? 'text-[#2a73d8]' : 'text-[#6da2ff]' }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="mt-2 w-full rounded-xl border border-[#7ead59]/35 bg-[#7ead59]/90 px-4 py-3 text-sm font-semibold uppercase tracking-[0.18em] text-[#07100c]">Logout</button>
            </form>
        </div>
    </div>
</nav>
