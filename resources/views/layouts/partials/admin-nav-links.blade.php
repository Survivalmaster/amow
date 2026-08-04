@php($mainAdminSections = $adminSections->reject(fn ($definition, $section) => in_array($section, ['overview', 'game_master', 'moderator'], true)))

<div class="space-y-5">
    <div>
        <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Manage</p>
        <div class="mt-2 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="amow-admin-side-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>Overview</span>
            </a>

            @foreach ($mainAdminSections as $section => $definition)
                @continue(! $adminNavUser->canAccessAdminSection($section))
                @php($routeGroup = Str::beforeLast($definition['route'], '.'))
                @php($isActive = request()->routeIs($definition['route']) || ($routeGroup !== 'admin' && request()->routeIs($routeGroup.'.*')))
                <a href="{{ route($definition['route']) }}" class="amow-admin-side-link {{ $isActive ? 'is-active' : '' }}">
                    <i class="{{ str_starts_with($adminIcons[$section] ?? '', 'fa-brands') ? $adminIcons[$section] : 'fa-solid '.($adminIcons[$section] ?? 'fa-circle') }}"></i>
                    <span>{{ $definition['label'] }}</span>
                </a>
            @endforeach

            @if ($adminNavUser->canAccessAdminSection('characters'))
                <a href="{{ route('admin.character-logs.index') }}" class="amow-admin-side-link {{ request()->routeIs('admin.character-log.*', 'admin.character-logs.*') ? 'is-active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Character Logs</span>
                </a>
            @endif
        </div>
    </div>

    @if ($adminNavUser->canAccessAdminSection('game_master') || $adminNavUser->canAccessAdminSection('moderator'))
        <div>
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Tools</p>
            <div class="mt-2 space-y-1">
                @if ($adminNavUser->canAccessAdminSection('game_master'))
                    <a href="{{ route('admin.game-master.index') }}" class="amow-admin-side-link {{ request()->routeIs('admin.game-master.*') ? 'is-active' : '' }}">
                        <i class="fa-solid fa-dice-d20"></i>
                        <span>Game Master</span>
                    </a>
                @endif
                @if ($adminNavUser->canAccessAdminSection('moderator'))
                    <a href="{{ route('admin.moderator.index') }}" class="amow-admin-side-link {{ request()->routeIs('admin.moderator.*') ? 'is-active' : '' }}">
                        <i class="fa-solid fa-gavel"></i>
                        <span>Moderator</span>
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
