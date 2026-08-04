@php
    $groups = [
        'core' => [
            'label' => 'Core',
            'items' => [
                ['label' => 'Overview', 'route' => 'admin.dashboard', 'match' => ['admin.dashboard'], 'icon' => 'fa-solid fa-chart-line', 'access' => true],
                ['section' => 'users'],
                ['section' => 'characters'],
                ['label' => 'Character Logs', 'route' => 'admin.character-logs.index', 'match' => ['admin.character-log.*', 'admin.character-logs.*'], 'icon' => 'fa-solid fa-clock-rotate-left', 'access' => $adminNavUser->canAccessAdminSection('characters')],
                ['section' => 'permissions'],
            ],
        ],
        'world' => [
            'label' => 'World',
            'items' => [
                ['section' => 'factions'],
                ['section' => 'cities'],
                ['section' => 'locations'],
                ['section' => 'map_markers'],
                ['section' => 'nation_requisitions'],
            ],
        ],
        'economy' => [
            'label' => 'Economy',
            'items' => [
                ['section' => 'items'],
                ['section' => 'jobs'],
                ['section' => 'stock_market'],
            ],
        ],
        'combat' => [
            'label' => 'Combat',
            'items' => [
                ['section' => 'skirmishes'],
                ['section' => 'units'],
            ],
        ],
        'discord' => [
            'label' => 'Discord',
            'items' => [
                ['section' => 'discord'],
                ['section' => 'discord_management'],
            ],
        ],
        'tools' => [
            'label' => 'Tools',
            'items' => [
                ['section' => 'game_master'],
                ['section' => 'moderator'],
            ],
        ],
    ];

    $resolvedGroups = collect($groups)->map(function ($group) use ($adminSections, $adminIcons, $adminNavUser) {
        $items = collect($group['items'])->map(function ($item) use ($adminSections, $adminIcons, $adminNavUser) {
            if (isset($item['section'])) {
                $definition = $adminSections->get($item['section']);

                if (! $definition || ! $adminNavUser->canAccessAdminSection($item['section'])) {
                    return null;
                }

                $routeGroup = Str::beforeLast($definition['route'], '.');
                $match = [$definition['route']];

                if ($routeGroup !== 'admin') {
                    $match[] = $routeGroup.'.*';
                }

                $icon = $adminIcons[$item['section']] ?? 'fa-circle';

                return [
                    'label' => $definition['label'],
                    'route' => $definition['route'],
                    'match' => $match,
                    'icon' => str_starts_with($icon, 'fa-brands') ? $icon : 'fa-solid '.$icon,
                ];
            }

            if (! ($item['access'] ?? true)) {
                return null;
            }

            return $item;
        })->filter()->values();

        return [
            ...$group,
            'items' => $items,
            'active' => $items->contains(fn ($item) => request()->routeIs(...$item['match'])),
        ];
    })->filter(fn ($group) => $group['items']->isNotEmpty());
@endphp

<div
    x-data="{
        groups: {
            @foreach ($resolvedGroups as $key => $group)
                {{ $key }}: {{ $group['active'] || $key === 'core' ? 'true' : 'false' }},
            @endforeach
        }
    }"
    class="space-y-2"
>
    @foreach ($resolvedGroups as $key => $group)
        <div class="amow-admin-nav-group">
            <button type="button" class="amow-admin-group-toggle" @click="groups.{{ $key }} = !groups.{{ $key }}">
                <span>{{ $group['label'] }}</span>
                <i class="fa-solid fa-chevron-down" :class="groups.{{ $key }} ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="groups.{{ $key }}" class="mt-1 space-y-1">
                @foreach ($group['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="amow-admin-side-link {{ request()->routeIs(...$item['match']) ? 'is-active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
