@php($territoryFactions = $factions->map(fn ($faction) => [
    'id' => $faction->id,
    'name' => $faction->name,
    'slug' => $faction->slug,
    'colour' => $faction->color,
    'color' => $faction->color,
])->values())

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>World of Plastica - {{ config('app.name', 'AMOW') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=rajdhani:500,600,700|teko:500,600,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="{{ asset('css/territory-map.css') }}?v={{ filemtime(public_path('css/territory-map.css')) }}">
    </head>
    <body class="territory-body">
        <div
            id="territory-map-app"
            class="territory-shell territory-fullscreen"
            style="--territory-map-ratio: {{ $mapWidth }} / {{ $mapHeight }};"
            data-map-image-url="{{ $mapImageUrl }}"
            data-map-width="{{ $mapWidth }}"
            data-map-height="{{ $mapHeight }}"
            data-hexes-url="{{ route('api.map.hexes.index') }}"
            data-can-manage="{{ $canManageTerritory ? 'true' : 'false' }}"
            data-csrf-token="{{ csrf_token() }}"
            data-factions='@json($territoryFactions)'
        >
            <nav class="territory-corner-nav" aria-label="Map navigation">
                <a href="{{ route('lobby') }}">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to Game</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button>
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>

            <section class="territory-toolbar">
                <div class="territory-mode-group" role="group" aria-label="Map display mode">
                    <button type="button" data-territory-mode="hex" class="is-active">Hex</button>
                    <button type="button" data-territory-mode="territory">Territory</button>
                    <button type="button" data-territory-mode="unclaimed">Unclaimed</button>
                    @if ($canManageTerritory)
                        <button type="button" data-territory-mode="admin">Admin</button>
                    @endif
                </div>

                @if ($canManageTerritory)
                    <div class="territory-brush" data-territory-brush>
                        <label>
                            <input type="checkbox" data-brush-enabled>
                            <span>Brush</span>
                        </label>
                        <select data-brush-action>
                            <option value="tile_type">Tile Type</option>
                            <option value="claim">Claim Faction</option>
                        </select>
                        <select data-brush-type>
                            @foreach (\App\Models\MapHex::TILE_TYPES as $tileType)
                                <option value="{{ $tileType }}">{{ str($tileType)->title() }}</option>
                            @endforeach
                        </select>
                        <span class="territory-brush-note">Claim brush uses the panel faction.</span>
                    </div>
                @endif

                <div class="territory-status" data-territory-status>Loading territory grid...</div>
            </section>

            <section class="territory-map-wrap">
                <div id="territory-leaflet-map" class="territory-map"></div>
            </section>

            <aside class="territory-panel">
                <button type="button" class="territory-panel-close" data-close-panel title="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div>
                    <p class="territory-panel-title">Territory Intel</p>
                    <p class="territory-panel-summary" data-selected-summary>Click a visible tile to inspect it.</p>
                </div>

                <dl class="territory-details">
                    <div><dt>ID</dt><dd data-selected-field="id">-</dd></div>
                    <div><dt>Grid</dt><dd data-selected-field="grid">-</dd></div>
                    <div><dt>Tile Type</dt><dd data-selected-field="tile_type">-</dd></div>
                    <div><dt>Terrain</dt><dd data-selected-field="terrain_type">-</dd></div>
                    <div><dt>Faction</dt><dd data-selected-field="faction">-</dd></div>
                    <div><dt>Strength</dt><dd data-selected-field="claim_strength">-</dd></div>
                    <div><dt>Claimed</dt><dd data-selected-field="claimed_at">-</dd></div>
                </dl>

                @if ($canManageTerritory)
                    <form class="territory-editor" data-territory-editor>
                        <div class="territory-admin-heading">
                            <p>Admin Tools</p>
                            <span>Switch to Admin mode to edit tiles.</span>
                        </div>
                        <label>
                            <span>Tile Type</span>
                            <select name="tile_type">
                                @foreach (\App\Models\MapHex::TILE_TYPES as $tileType)
                                    <option value="{{ $tileType }}">{{ str($tileType)->title() }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Terrain</span>
                            <input name="terrain_type" maxlength="80" placeholder="forest, coast, city">
                        </label>
                        <label>
                            <span>Faction</span>
                            <select name="faction_id">
                                <option value="">Unclaimed</option>
                                @foreach ($factions as $faction)
                                    <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Strength</span>
                            <input name="claim_strength" type="number" min="0" max="1000000" value="0">
                        </label>
                        <label class="territory-checkbox">
                            <input name="is_visible" type="checkbox" value="1">
                            <span>Visible</span>
                        </label>

                        <div class="territory-actions">
                            <button type="submit"><i class="fa-solid fa-check"></i> Save</button>
                            <button type="button" data-claim-selected><i class="fa-solid fa-flag"></i> Claim</button>
                            <button type="button" data-remove-claim><i class="fa-solid fa-xmark"></i> Remove Claim</button>
                        </div>
                    </form>
                @endif

                <div class="territory-legend">
                    <p class="territory-legend-title">Legend</p>
                    <div class="territory-legend-row"><span style="background:#8b949e"></span>Unclaimed land</div>
                    <div class="territory-legend-row"><span style="background:#3478c5"></span>Water</div>
                    <div class="territory-legend-row"><span style="background:#1f2937"></span>Blocked</div>
                    @foreach ($factions as $faction)
                        <div class="territory-legend-row"><span style="background:{{ $faction->color ?: '#7ead59' }}"></span>{{ $faction->name }}</div>
                    @endforeach
                </div>
            </aside>
        </div>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="{{ asset('js/territory-map.js') }}?v={{ filemtime(public_path('js/territory-map.js')) }}" defer></script>
    </body>
</html>
