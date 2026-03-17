@php($navUser = auth()->user()->loadMissing('permissions.accountIcon'))
@php($navCharacter = $navUser->character?->loadMissing(['rank', 'currentJob']))
@php($creditAmount = $navCharacter?->plastic_credits ?? 0)
@php($healthPoints = $navCharacter?->health_points ?? 100)
@php($staminaPoints = $navCharacter?->stamina_points ?? 100)
@php($armorPoints = $navCharacter?->armor_points ?? 0)
@php($staminaPercent = max(0, min(100, (int) $staminaPoints)))
@php($experienceRequired = $navCharacter?->experienceRequiredForNextLevel() ?? 100)
@php($experiencePercent = $navCharacter ? min(100, (int) round(($navCharacter->experience_points / max(1, $experienceRequired)) * 100)) : 0)
@php($accountIcons = $navUser->permissionIcons())
@php(
    $formattedCredits = match (true) {
        $creditAmount >= 1000000 => rtrim(rtrim(number_format($creditAmount / 1000000, 1), '0'), '.') . 'M',
        $creditAmount >= 100000 => rtrim(rtrim(number_format($creditAmount / 1000, 1), '0'), '.') . 'K',
        default => number_format($creditAmount),
    }
)
@php(
    $primaryNav = array_values(array_filter([
        ['label' => 'My Dashboard', 'route' => 'lobby', 'match' => ['lobby', 'cities.*', 'locations.*', 'messages.*', 'work.*'], 'icon' => 'fa-solid fa-gauge-high'],
        ['label' => 'Jobs', 'route' => 'jobs.index', 'match' => ['jobs.*'], 'icon' => 'fa-solid fa-briefcase'],
        ['label' => 'Store', 'route' => 'store.index', 'match' => ['store.*'], 'icon' => 'fa-solid fa-store'],
        ['label' => 'Stock Market', 'route' => 'market.index', 'match' => ['market.*'], 'icon' => 'fa-solid fa-chart-line'],
        ['label' => 'Leaderboards', 'route' => 'leaderboards.index', 'match' => ['leaderboards.*'], 'icon' => 'fa-solid fa-trophy'],
    ]))
)
@php(
    $operationsNav = array_values(array_filter([
        ['label' => 'Character', 'route' => 'characters.show', 'match' => ['characters.show'], 'icon' => 'fa-solid fa-id-badge'],
        ['label' => 'Account', 'route' => 'profile.edit', 'match' => ['profile.*'], 'icon' => 'fa-solid fa-gear'],
        $navUser->canAccessAdmin() ? ['label' => 'Admin', 'route' => 'admin.dashboard', 'match' => ['admin.*'], 'icon' => 'fa-solid fa-shield-halved'] : null,
    ]))
)

<nav x-data="{ open: false }" class="border-b border-white/10 bg-black/25 backdrop-blur lg:min-h-screen lg:border-b-0 lg:border-r">
    <div class="flex items-center justify-between px-4 py-4 sm:px-6 lg:hidden">
        <a href="{{ route('dashboard') }}" class="font-['Teko'] text-3xl uppercase tracking-[0.16em] text-[#f4ecd0]">AMOW</a>
        <button @click="open = ! open" class="rounded-2xl border border-white/10 px-3 py-2 text-sm">Menu</button>
    </div>

    <div class="hidden lg:flex lg:sticky lg:top-0 lg:h-screen lg:flex-col lg:justify-between lg:px-6 lg:py-8">
        <div class="flex h-full flex-col">
            @if ($navCharacter)
                <div class="rounded-[1.25rem] bg-[linear-gradient(180deg,rgba(15,27,20,0.95),rgba(8,15,11,0.92))] p-3 shadow-xl shadow-black/25">
                    <div class="flex items-center gap-3">
                        <div class="flex shrink-0 flex-col items-center gap-2">
                            <div class="inline-flex items-center justify-center rounded-full border border-[#c2a84f]/30 bg-[#0c140f] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-[#c2a84f]">
                                Lvl <span class="ml-1" data-character-field="level">{{ $navCharacter->level }}</span>
                            </div>
                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#17271e] text-2xl font-bold text-[#f4ecd0] ring-1 ring-[#2b4a36]">
                                <span class="leading-none">?</span>
                            </div>
                            @if ($accountIcons->isNotEmpty())
                                <div class="grid w-14 grid-cols-3 justify-items-center gap-1">
                                    @foreach ($accountIcons as $accountIcon)
                                        <span class="inline-flex h-4.5 w-4.5 items-center justify-center rounded-full border border-white/10 bg-black/25 text-[9px] shadow-inner shadow-black/30" title="{{ $accountIcon->tooltip ?: $accountIcon->name }}" style="color: {{ $accountIcon->color ?: '#f4ecd0' }};">
                                            <i class="{{ $accountIcon->icon_value }}"></i>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-['Teko'] text-[1.35rem] uppercase leading-none tracking-[0.04em]" data-character-field="name">{{ $navCharacter->name }}</p>
                            <p class="mt-0.5 text-[11px] uppercase tracking-[0.2em] text-white/55">
                                <span data-character-field="rank_name">{{ $navCharacter->rank?->name ?? 'Unranked' }}</span>
                                |
                                <span data-character-field="displayed_job_name">{{ $navCharacter->displayed_job_name }}</span>
                            </p>
                            <div class="mt-1.5 flex items-center gap-4 text-[12px] font-semibold text-[#d9e5d0]">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <i class="fa-solid fa-heart text-[#d75b5b]"></i>
                                    <span data-character-field="health_label">{{ $healthPoints }}/100</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-white/75">
                                    <i class="fa-solid fa-shield-halved text-[#8f949d]"></i>
                                    <span data-character-field="armor_points">{{ $armorPoints }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-[#f4ecd0]">
                                    <i class="fa-solid fa-coins text-[#c2a84f]"></i>
                                    <span class="min-w-[3.5rem]" data-character-field="formatted_credits">{{ $formattedCredits }}</span>
                                </span>
                            </div>
                            <div class="mt-2">
                                <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/72">
                                    <span class="text-[#c2a84f]">XP</span>
                                    <span data-character-field="experience_label">{{ $navCharacter->experience_points }}/{{ $experienceRequired }}</span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-[linear-gradient(90deg,#c2a84f_0%,#f4d77a_100%)]" data-character-width="experience_progress_percent" style="width: {{ $experiencePercent }}%;"></div>
                                </div>
                            </div>
                            <div class="mt-1.5">
                                <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/72">
                                    <span class="inline-flex items-center gap-1.5 text-[#d7edc7]">
                                        <i class="fa-solid fa-bolt text-[#7ead59]"></i>
                                        Stamina
                                    </span>
                                    <span data-character-field="stamina_label">{{ $staminaPoints }}/100</span>
                                </div>
                                <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-[linear-gradient(90deg,#7ead59_0%,#b7d680_100%)]" data-character-width="stamina_percent" style="width: {{ $staminaPercent }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-8">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Dashboard</p>
                <div class="mt-3 grid gap-1">
                    @foreach ($primaryNav as $item)
                        @php($isActive = request()->routeIs(...$item['match']))
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}">
                            <span class="h-6 w-1 rounded-full {{ $isActive ? 'bg-[#7ead59]' : 'bg-transparent' }}"></span>
                            <i class="{{ $item['icon'] }} w-5 text-center text-[#7ead59]"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-7">
                <p class="px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">User Area</p>
                <div class="mt-3 grid gap-1">
                    @foreach ($operationsNav as $item)
                        @php($isActive = request()->routeIs(...$item['match']))
                        <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}">
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
                <div class="mb-2 rounded-[1.25rem] bg-[linear-gradient(180deg,rgba(15,27,20,0.95),rgba(8,15,11,0.92))] p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex shrink-0 flex-col items-center gap-2">
                            <div class="inline-flex items-center justify-center rounded-full border border-[#c2a84f]/30 bg-[#0c140f] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-[#c2a84f]">
                                Lvl <span class="ml-1" data-character-field="level">{{ $navCharacter->level }}</span>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#17271e] text-xl font-bold text-[#f4ecd0] ring-1 ring-[#2b4a36]">
                                <span class="leading-none">?</span>
                            </div>
                            @if ($accountIcons->isNotEmpty())
                                <div class="grid w-12 grid-cols-3 justify-items-center gap-1">
                                    @foreach ($accountIcons as $accountIcon)
                                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-white/10 bg-black/25 text-[8px] shadow-inner shadow-black/30" title="{{ $accountIcon->tooltip ?: $accountIcon->name }}" style="color: {{ $accountIcon->color ?: '#f4ecd0' }};">
                                            <i class="{{ $accountIcon->icon_value }}"></i>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-['Teko'] text-[1.3rem] uppercase leading-none tracking-[0.04em]" data-character-field="name">{{ $navCharacter->name }}</p>
                            <p class="mt-0.5 text-[11px] uppercase tracking-[0.2em] text-white/55">
                                <span data-character-field="rank_name">{{ $navCharacter->rank?->name ?? 'Unranked' }}</span>
                                |
                                <span data-character-field="displayed_job_name">{{ $navCharacter->displayed_job_name }}</span>
                            </p>
                            <div class="mt-1.5 flex items-center gap-4 text-[12px] font-semibold text-[#d9e5d0]">
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                    <i class="fa-solid fa-heart text-[#d75b5b]"></i>
                                    <span data-character-field="health_label">{{ $healthPoints }}/100</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-white/75">
                                    <i class="fa-solid fa-shield-halved text-[#8f949d]"></i>
                                    <span data-character-field="armor_points">{{ $armorPoints }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-[#f4ecd0]">
                                    <i class="fa-solid fa-coins text-[#c2a84f]"></i>
                                    <span class="min-w-[3.5rem]" data-character-field="formatted_credits">{{ $formattedCredits }}</span>
                                </span>
                            </div>
                            <div class="mt-2">
                                <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/72">
                                    <span class="text-[#c2a84f]">XP</span>
                                    <span data-character-field="experience_label">{{ $navCharacter->experience_points }}/{{ $experienceRequired }}</span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-[linear-gradient(90deg,#c2a84f_0%,#f4d77a_100%)]" data-character-width="experience_progress_percent" style="width: {{ $experiencePercent }}%;"></div>
                                </div>
                            </div>
                            <div class="mt-1.5">
                                <div class="flex items-center justify-between gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-white/72">
                                    <span class="inline-flex items-center gap-1.5 text-[#d7edc7]">
                                        <i class="fa-solid fa-bolt text-[#7ead59]"></i>
                                        Stamina
                                    </span>
                                    <span data-character-field="stamina_label">{{ $staminaPoints }}/100</span>
                                </div>
                                <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full rounded-full bg-[linear-gradient(90deg,#7ead59_0%,#b7d680_100%)]" data-character-width="stamina_percent" style="width: {{ $staminaPercent }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <p class="mt-2 px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">Dashboard</p>
            @foreach ($primaryNav as $item)
                @php($isActive = request()->routeIs(...$item['match']))
                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}">
                    <span class="h-6 w-1 rounded-full {{ $isActive ? 'bg-[#7ead59]' : 'bg-transparent' }}"></span>
                    <i class="{{ $item['icon'] }} w-5 text-center text-[#7ead59]"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

            <p class="mt-4 px-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white/38">User Area</p>
            @foreach ($operationsNav as $item)
                @php($isActive = request()->routeIs(...$item['match']))
                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-[15px] font-semibold transition {{ $isActive ? 'bg-white/[0.06] text-[#f4ecd0]' : 'text-white/82 hover:bg-white/[0.05]' }}">
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
