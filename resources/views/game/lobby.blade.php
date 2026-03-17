@push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
    <style>
        #lobby-world-map {
            height: min(72vh, 760px);
            width: 100%;
            background: #0a120d;
        }

        .leaflet-container {
            font: inherit;
        }

        .lobby-map-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            background: rgba(4, 8, 6, 0.92);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            font-size: 0.85rem;
        }
    </style>
@endpush

@php
    $mapMaxZoom = 8;
    $markerPayload = $mapMarkers->map(function ($marker) {
        return [
            'name' => $marker->name,
            'description' => $marker->description,
            'icon_type' => $marker->icon_type ?? 'fontawesome',
            'icon_class' => $marker->icon_class,
            'icon_asset_url' => $marker->icon_asset_url,
            'map_x' => (int) $marker->map_x,
            'map_y' => (int) $marker->map_y,
            'color' => $marker->color ?: '#c2a84f',
            'faction' => $marker->faction?->name,
        ];
    })->values();
    $polygonPayload = $mapPolygons->map(function ($polygon) {
        return [
            'name' => $polygon->name,
            'description' => $polygon->description,
            'stroke_color' => $polygon->stroke_color,
            'fill_color' => $polygon->fill_color,
            'fill_opacity' => (float) $polygon->fill_opacity,
            'stroke_weight' => (int) $polygon->stroke_weight,
            'coordinates' => collect($polygon->coordinates)->map(fn ($point) => [
                'x' => (float) ($point['x'] ?? 0),
                'y' => (float) ($point['y'] ?? 0),
            ])->values(),
            'faction' => $polygon->faction?->name,
        ];
    })->values();
@endphp

@push('scripts')
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mapElement = document.getElementById('lobby-world-map');

            if (!mapElement || typeof L === 'undefined') {
                return;
            }

            const markers = @json($markerPayload);
            const polygons = @json($polygonPayload);
            const mapExtent = [0, -8192, 8192, 0];
            const mapMinZoom = 0;
            const mapMaxZoom = {{ $mapMaxZoom }};
            const mapMaxResolution = 0.0625;
            const mapMinResolution = Math.pow(2, mapMaxZoom) * mapMaxResolution;
            const tileExtent = [0, -8192, 8192, 0];
            const crs = L.extend({}, L.CRS.Simple);

            crs.transformation = new L.Transformation(1, -tileExtent[0], -1, tileExtent[3]);
            crs.scale = function (zoom) {
                return Math.pow(2, zoom) / mapMinResolution;
            };
            crs.zoom = function (scale) {
                return Math.log(scale * mapMinResolution) / Math.LN2;
            };

            const map = L.map(mapElement, {
                crs,
                minZoom: mapMinZoom,
                maxZoom: mapMaxZoom,
                zoomControl: true,
            });

            map.createPane('polygonPane');
            map.getPane('polygonPane').style.zIndex = 410;
            map.createPane('markerPaneTop');
            map.getPane('markerPaneTop').style.zIndex = 650;

            L.tileLayer('{{ asset('mapstyles/stylePlastica') }}/{z}/{x}/{y}.png', {
                minZoom: mapMinZoom,
                maxZoom: mapMaxZoom,
                tileSize: L.point(512, 512),
                noWrap: true,
                tms: false,
                attribution: 'Rendered with MapTiler Engine',
            }).addTo(map);

            const bounds = L.latLngBounds([
                crs.unproject(L.point(mapExtent[2], mapExtent[3])),
                crs.unproject(L.point(mapExtent[0], mapExtent[1])),
            ]);

            map.fitBounds(bounds, { padding: [0, 0] });
            map.setMinZoom(map.getZoom());
            map.setMaxBounds(bounds.pad(0.05));

            const pointFromPercent = (xPercent, yPercent) => {
                const projectedX = (xPercent / 100) * mapExtent[2];
                const projectedY = (yPercent / 100) * mapExtent[1];
                return crs.unproject(L.point(projectedX, projectedY));
            };

            polygons.forEach((polygon) => {
                L.polygon(
                    polygon.coordinates.map((point) => pointFromPercent(point.x, point.y)),
                    {
                        pane: 'polygonPane',
                        color: polygon.stroke_color,
                        fillColor: polygon.fill_color,
                        fillOpacity: polygon.fill_opacity,
                        weight: polygon.stroke_weight,
                    }
                )
                    .addTo(map)
                    .bindPopup(`
                        <div style="min-width: 200px">
                            <strong>${polygon.name}</strong><br>
                            <span>${polygon.faction ?? 'Unclaimed / Shared'}</span>
                            ${polygon.description ? `<p style="margin: 8px 0 0;">${polygon.description}</p>` : ''}
                        </div>
                    `);
            });

            markers.forEach((marker) => {
                const icon = marker.icon_type === 'image' && marker.icon_asset_url
                    ? L.divIcon({
                        className: '',
                        html: `<div class="lobby-map-marker" style="padding: 0; overflow: hidden;"><img src="${marker.icon_asset_url}" alt="" style="width: 100%; height: 100%; object-fit: contain;"></div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                    })
                    : L.divIcon({
                        className: '',
                        html: `<div class="lobby-map-marker" style="color: ${marker.color};"><i class="${marker.icon_class}" style="font-size: 0.85rem;"></i></div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                    });

                L.marker(pointFromPercent(marker.map_x, marker.map_y), { icon, pane: 'markerPaneTop' })
                    .addTo(map)
                    .bindPopup(`
                        <div style="min-width: 180px">
                            <strong>${marker.name}</strong><br>
                            <span>${marker.faction ?? 'All factions'}</span>
                            ${marker.description ? `<p style="margin: 8px 0 0;">${marker.description}</p>` : ''}
                        </div>
                    `);
            });
        });
    </script>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <div>
                <p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">{{ $character->faction->name }} Lobby</p>
                <p class="text-sm uppercase tracking-[0.22em] text-white/55">Welcome, {{ $character->name }}. Plastica is active.</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Map of Plastica</p>
                    <p class="mt-2 text-sm leading-7 text-white/70">Travel by clicking a city or a managed marker on the map below. Territory polygons now show claimed areas while markers stay clickable above them.</p>
                </div>
                <div class="text-xs uppercase tracking-[0.24em] text-white/45">Interactive travel map</div>
            </div>
            <div class="relative mt-5 overflow-hidden rounded-[2rem] border border-white/10 bg-black/20">
                <div id="lobby-world-map"></div>
            </div>
        </section>
    </div>
</x-app-layout>
