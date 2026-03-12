@php($navCharacter = auth()->user()->character)
@php($creditAmount = $navCharacter?->plastic_credits ?? 0)
@php($healthPoints = $navCharacter?->health_points ?? 100)
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
        ['label' => 'My Dashboard', 'route' => 'lobby', 'match' => ['lobby'], 'icon' => 'fa-solid fa-gauge-high'],
        ['label' => 'Store', 'route' => 'store.index', 'match' => ['store.*'], 'icon' => 'fa-solid fa-store'],
        ['label' => 'Stock Market', 'route' => 'market.index', 'match' => ['market.*'], 'icon' => 'fa-solid fa-chart-line'],
        ['label' => 'Leaderboards', 'route' => 'leaderboards.index', 'match' => ['leaderboards.*'], 'icon' => 'fa-solid fa-trophy'],
    ]
)
@php(
    $operationsNav = array_values(array_filter([
        ['label' => 'Character', 'route' => 'characters.show', 'match' => ['characters.show'], 'icon' => 'fa-solid fa-id-badge'],
        ['label' => 'Account', 'route' => 'profile.edit', 'match' => ['profile.*'], 'icon' => 'fa-solid fa-gear'],
        auth()->user()->is_admin ? ['label' => 'Admin', 'route' => 'admin.dashboard', 'match' => ['admin.*'], 'icon' => 'fa-solid fa-shield-halved'] : null,
    ]))
)

<nav x-data="{ open: false }" class="border-b border-white/10 bg-black/25 backdrop-blur lg:min-h-screen lg:border-b-0 lg:border-r">
    <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('images/amowog.png') }}" alt="Army Men of War" class="h-14 w-auto object-contain" />
        </a>

        <button @click="open = ! open" class="rounded-2xl border border-white/10 px-3 py-2 text-sm">Menu</button>
    </div>

    <div class="hidden lg:flex lg:sticky lg:top-0 lg:h-screen lg:flex-col lg:justify-between lg:px-6 lg:py-8">
        <div class="flex h-full flex-col">
            <a href="{{ route('dashboard') }}" class="mx-auto block text-center">
                <img src="{{ asset('images/amowog.png') }}" alt="Army Men of War" class="mx-auto h-28 w-auto object-contain" />
            </a>

            @if ($navCharacter)
                <div class="mt-7 rounded-[1.25rem] bg-[linear-gradient(180deg,rgba(15,27,20,0.95),rgba(8,15,11,0.92))] p-3 shadow-xl shadow-black/25 ring-1 ring-[#1e3929]">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-[#17271e] text-2xl font-bold text-[#f4ecd0] ring-1 ring-[#2b4a36]">
                            ?
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-['Teko'] text-[1.35rem] uppercase leading-none tracking-[0.04em]">{{ $navCharacter->name }}</p>
                            <p class="mt-1 text-[11px] uppercase tracking-[0.2em] text-white/55">{{ ucfirst($navCharacter->role_type) }} | {{ $navCharacter->rank?->name ?? 'Unranked' }}</p>
                            <div class="mt-2 flex items-center gap-3 text-[12px] font-semibold text-[#d9e5d0]">
                                <span class="inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-heart text-[#d75b5b]"></i>
                                    {{ $healthPoints }}/100
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <img
                            src="{{ asset('images/plastica_money/' . $creditTier . '.png') }}"
                            alt="Plastic Credits tier"
                            class="h-5 w-5 object-contain"
                        />
                        <span class="text-sm font-semibold text-[#f4ecd0]">{{ number_format($creditAmount) }}</span>
                    </div>
                </div>
            @endif

            <div class="mt-8">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Dashboard</p>
                <div class="mt-3 grid gap-1">
                    @foreach ($primaryNav as $item)
                        @php($isActive = request()->routeIs(...$item['match']))
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                        >
                            <span class="h-6 w-1 rounded-full {{ $isActive ? 'bg-[#7ead59]' : 'bg-transparent' }}"></span>
                            <i class="{{ $item['icon'] }} w-5 text-center text-[#7ead59]"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-7">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Operations</p>
                <div class="mt-3 grid gap-1">
                    @foreach ($operationsNav as $item)
                        @php($isActive = request()->routeIs(...$item['match']))
                        <a
                            href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                        >
                            <span class="h-6 w-1 rounded-full {{ $isActive ? 'bg-[#7ead59]' : 'bg-transparent' }}"></span>
                            <i class="{{ $item['icon'] }} w-5 text-center text-[#7ead59]"></i>
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
                <div class="mb-2 rounded-[1.25rem] bg-[linear-gradient(180deg,rgba(15,27,20,0.95),rgba(8,15,11,0.92))] p-3 ring-1 ring-[#1e3929]">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#17271e] text-xl font-bold text-[#f4ecd0] ring-1 ring-[#2b4a36]">?</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-['Teko'] text-[1.3rem] uppercase leading-none tracking-[0.04em]">{{ $navCharacter->name }}</p>
                            <p class="mt-1 text-[11px] uppercase tracking-[0.2em] text-white/55">{{ ucfirst($navCharacter->role_type) }} | {{ $navCharacter->rank?->name ?? 'Unranked' }}</p>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#d9e5d0]">
                                <i class="fa-solid fa-heart text-[#d75b5b]"></i>
                                <span>{{ $healthPoints }}/100</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <img
                            src="{{ asset('images/plastica_money/' . $creditTier . '.png') }}"
                            alt="Plastic Credits tier"
                            class="h-5 w-5 object-contain"
                        />
                        <span class="text-sm font-semibold text-[#f4ecd0]">{{ number_format($creditAmount) }}</span>
                    </div>
                </div>
            @endif
            <p class="mt-2 px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Dashboard</p>
            @foreach ($primaryNav as $item)
                @php($isActive = request()->routeIs(...$item['match']))
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                >
                    <span class="h-6 w-1 rounded-full {{ $isActive ? 'bg-[#7ead59]' : 'bg-transparent' }}"></span>
                    <i class="{{ $item['icon'] }} w-5 text-center text-[#7ead59]"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
            <p class="mt-4 px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Operations</p>
            @foreach ($operationsNav as $item)
                @php($isActive = request()->routeIs(...$item['match']))
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}"
                >
                    <span class="h-6 w-1 rounded-full {{ $isActive ? 'bg-[#7ead59]' : 'bg-transparent' }}"></span>
                    <i class="{{ $item['icon'] }} w-5 text-center text-[#7ead59]"></i>
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
