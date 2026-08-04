@php($adminNavUser = auth()->user()->loadMissing('permissions'))
@php($adminSections = collect(config('admin_sections', [])))
@php($adminIcons = [
    'dashboard' => 'fa-chart-line',
    'users' => 'fa-users',
    'characters' => 'fa-id-card',
    'factions' => 'fa-flag',
    'cities' => 'fa-city',
    'locations' => 'fa-location-dot',
    'items' => 'fa-boxes-stacked',
    'jobs' => 'fa-briefcase',
    'skirmishes' => 'fa-crosshairs',
    'units' => 'fa-shield-halved',
    'permissions' => 'fa-key',
    'map_markers' => 'fa-map-location-dot',
    'discord' => 'fa-brands fa-discord',
    'discord_management' => 'fa-brands fa-discord',
    'nation_requisitions' => 'fa-file-signature',
    'stock_market' => 'fa-chart-simple',
    'character_logs' => 'fa-clock-rotate-left',
    'game_master' => 'fa-dice-d20',
    'moderator' => 'fa-gavel',
])

<nav x-data="{ open: false }" class="amow-admin-sidebar border-b border-slate-800 bg-[#0f1720] lg:sticky lg:top-0 lg:h-screen lg:border-b-0 lg:border-r">
    <div class="flex items-center justify-between px-4 py-4 lg:hidden">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 font-semibold text-slate-100">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-slate-950">AM</span>
            <span>Admin</span>
        </a>
        <button @click="open = ! open" class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200">Menu</button>
    </div>

    <div class="hidden min-h-0 flex-col lg:flex lg:h-screen">
        <div class="border-b border-slate-800 px-5 py-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold text-slate-950">AM</span>
                <span>
                    <span class="block text-sm font-semibold text-slate-100">AMOW Admin</span>
                    <span class="block text-xs text-slate-500">Operations Console</span>
                </span>
            </a>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-3 py-4">
            @include('layouts.partials.admin-nav-links', ['adminNavUser' => $adminNavUser, 'adminSections' => $adminSections, 'adminIcons' => $adminIcons])
        </div>

        <div class="border-t border-slate-800 p-4">
            <a href="{{ route('lobby') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-slate-100">
                <i class="fa-solid fa-arrow-left w-5 text-center"></i>
                Back to Game
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-slate-100">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

    <div x-show="open" x-cloak class="border-t border-slate-800 px-3 py-4 lg:hidden">
        @include('layouts.partials.admin-nav-links', ['adminNavUser' => $adminNavUser, 'adminSections' => $adminSections, 'adminIcons' => $adminIcons])
        <div class="mt-4 border-t border-slate-800 pt-4">
            <a href="{{ route('lobby') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400">
                <i class="fa-solid fa-arrow-left w-5 text-center"></i>
                Back to Game
            </a>
        </div>
    </div>
</nav>
