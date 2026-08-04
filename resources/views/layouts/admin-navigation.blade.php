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

<nav x-data="{ open: false }" class="amow-admin-sidebar">
    <div class="amow-admin-mobile-bar flex items-center justify-between px-4 py-4">
        <a href="{{ route('admin.dashboard') }}" class="font-semibold text-slate-100">
            <span>AMOW Admin</span>
        </a>
        <button @click="open = ! open" class="rounded-lg border border-slate-700 px-3 py-2 text-sm text-slate-200">Menu</button>
    </div>

    <div class="amow-admin-desktop-nav">
        <div class="border-b border-slate-800 px-5 py-5">
            <a href="{{ route('admin.dashboard') }}" class="block text-sm font-semibold text-slate-100">
                AMOW Admin
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

    <div x-show="open" x-cloak class="amow-admin-mobile-menu border-t border-slate-800 px-3 py-4">
        @include('layouts.partials.admin-nav-links', ['adminNavUser' => $adminNavUser, 'adminSections' => $adminSections, 'adminIcons' => $adminIcons])
        <div class="mt-4 border-t border-slate-800 pt-4">
            <a href="{{ route('lobby') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400">
                <i class="fa-solid fa-arrow-left w-5 text-center"></i>
                Back to Game
            </a>
        </div>
    </div>
</nav>
