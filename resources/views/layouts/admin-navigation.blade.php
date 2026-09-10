@php($adminNavUser = auth()->user()->loadMissing('permissions'))
@php($adminSections = collect(config('admin_sections', [])))
@php($adminIcons = [
    'dashboard' => 'fa-chart-line',
    'users' => 'fa-users',
    'characters' => 'fa-id-card',
    'refunds' => 'fa-rotate-left',
    'nation_logs' => 'fa-building-columns',
    'factions' => 'fa-flag',
    'cities' => 'fa-city',
    'locations' => 'fa-location-dot',
    'items' => 'fa-boxes-stacked',
    'jobs' => 'fa-briefcase',
    'skirmishes' => 'fa-crosshairs',
    'changelogs' => 'fa-scroll',
    'units' => 'fa-shield-halved',
    'permissions' => 'fa-key',
    'statistics' => 'fa-chart-pie',
    'map_markers' => 'fa-map-location-dot',
    'discord' => 'fa-brands fa-discord',
    'discord_management' => 'fa-brands fa-discord',
    'nation_requisitions' => 'fa-file-signature',
    'stock_market' => 'fa-chart-simple',
    'character_logs' => 'fa-clock-rotate-left',
    'server_tools' => 'fa-screwdriver-wrench',
    'game_master' => 'fa-dice-d20',
    'moderator' => 'fa-gavel',
])

<nav class="amow-admin-sidebar amow-navigation">
    <div id="amow-navigation" class="amow-navigation-panel flex flex-col" tabindex="-1" aria-label="Admin navigation">
        @include('layouts.partials.navigation-brand', ['navigationTitle' => 'AMOW Admin', 'navigationRoute' => 'admin.dashboard'])

        <div class="amow-navigation-scroll min-h-0 flex-1 overflow-y-auto">
            @include('layouts.partials.admin-nav-links', ['adminNavUser' => $adminNavUser, 'adminSections' => $adminSections, 'adminIcons' => $adminIcons])
        </div>

        <div class="amow-navigation-footer border-t border-slate-800 p-4">
            <a href="{{ route('lobby') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-slate-100">
                <i class="fa-solid fa-arrow-left w-5 text-center"></i>
                <span>Back to Game</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-slate-100">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

</nav>
