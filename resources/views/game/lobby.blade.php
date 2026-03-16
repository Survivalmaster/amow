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

        .lobby-city-marker {
            min-width: 6rem;
            padding: 0.55rem 0.8rem;
            border: 1px solid rgba(126, 173, 89, 0.45);
            border-radius: 9999px;
            background: rgba(7, 16, 12, 0.88);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            text-align: center;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }

        .lobby-city-marker:hover {
            transform: scale(1.04);
            border-color: rgba(126, 173, 89, 0.95);
        }

        .lobby-city-marker__name {
            display: block;
            color: #f4ecd0;
            font-family: Teko, sans-serif;
            font-size: 1.25rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            line-height: 1;
        }

        .lobby-city-marker__cta {
            display: block;
            margin-top: 0.15rem;
            color: #c2a84f;
            font-size: 10px;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        .lobby-map-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            background: rgba(4, 8, 6, 0.92);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        }
    </style>
@endpush

@php
    $mapMaxZoom = 8;
    $cityPayload = $cities->map(function ($city) {
        return [
            'name' => $city->name,
            'slug' => $city->slug,
            'map_x' => (int) $city->map_x,
            'map_y' => (int) $city->map_y,
            'description' => $city->description,
            'url' => route('cities.show', $city->slug),
        ];
    })->values();
    $markerPayload = $mapMarkers->map(function ($marker) {
        return [
            'name' => $marker->name,
            'description' => $marker->description,
            'icon_class' => $marker->icon_class,
            'map_x' => (int) $marker->map_x,
            'map_y' => (int) $marker->map_y,
            'color' => $marker->color ?: '#c2a84f',
            'faction' => $marker->faction?->name,
        ];
    })->values();
@endphp

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
                    <p class="mt-2 text-sm leading-7 text-white/70">Travel by clicking a city or a managed marker on the map below.</p>
                </div>
                <div class="text-xs uppercase tracking-[0.24em] text-white/45">Interactive travel map</div>
            </div>
            <div class="relative mt-5 overflow-hidden rounded-[2rem] border border-white/10 bg-black/20">
                <div id="lobby-world-map"></div>
            </div>
        </section>
    </div>
</x-app-layout>

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

            const cities = @json($cityPayload);
            const markers = @json($markerPayload);
            const mapExtent = [0, -8192, 8192, 0];
            const mapMinZoom = 0;
            const mapMaxZoom = {{ $mapMaxZoom }};
            const mapMaxResolution = 0.03125;
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

            map.fitBounds(bounds);
            map.setMaxBounds(bounds.pad(0.05));

            const pointFromPercent = (xPercent, yPercent) => {
                const projectedX = (xPercent / 100) * mapExtent[2];
                const projectedY = (yPercent / 100) * mapExtent[1];
                return crs.unproject(L.point(projectedX, projectedY));
            };

            cities.forEach((city) => {
                const icon = L.divIcon({
                    className: '',
                    html: `
                        <a href="${city.url}" class="lobby-city-marker">
                            <span class="lobby-city-marker__name">${city.name}</span>
                            <span class="lobby-city-marker__cta">Travel</span>
                        </a>
                    `,
                    iconSize: [120, 56],
                    iconAnchor: [60, 28],
                });

                const marker = L.marker(pointFromPercent(city.map_x, city.map_y), { icon }).addTo(map);
                marker.on('click', () => {
                    window.location.href = city.url;
                });
            });

            markers.forEach((marker) => {
                const icon = L.divIcon({
                    className: '',
                    html: `<div class="lobby-map-marker" style="color: ${marker.color};"><i class="${marker.icon_class} text-lg"></i></div>`,
                    iconSize: [44, 44],
                    iconAnchor: [22, 22],
                });

                L.marker(pointFromPercent(marker.map_x, marker.map_y), { icon })
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
