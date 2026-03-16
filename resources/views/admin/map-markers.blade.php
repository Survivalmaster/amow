@push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >
    <style>
        #admin-world-map {
            height: min(70vh, 720px);
            width: 100%;
            background: #0a120d;
        }

        .leaflet-container {
            font: inherit;
        }

        .world-marker {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            background: rgba(4, 8, 6, 0.92);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            font-size: 0.8rem;
        }

        .world-marker.is-preview {
            color: #7ead59;
            border-color: rgba(126, 173, 89, 0.4);
        }

        .polygon-point-handle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.5rem;
            height: 1.5rem;
            border: 1px solid rgba(244, 236, 208, 0.45);
            border-radius: 9999px;
            background: rgba(12, 20, 15, 0.94);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            color: #f4ecd0;
            font-size: 0.65rem;
            font-weight: 700;
        }
    </style>
@endpush

@php
    $mapMaxZoom = 8;
    $markerPayload = $markers->map(function ($marker) {
        return [
            'id' => $marker->id,
            'name' => $marker->name,
            'description' => $marker->description,
            'icon_class' => $marker->icon_class,
            'map_x' => (int) $marker->map_x,
            'map_y' => (int) $marker->map_y,
            'color' => $marker->color ?: '#c2a84f',
            'faction' => $marker->faction?->name,
        ];
    })->values();
    $polygonPayload = $polygons->map(function ($polygon) {
        return [
            'id' => $polygon->id,
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
        window.mapPolygonForm = (config = {}) => ({
            mapMode: config.mapMode ?? 'marker',
            x: config.x ?? 50,
            y: config.y ?? 50,
            iconClass: config.iconClass ?? 'fa-solid fa-flag',
            color: config.color ?? '#c2a84f',
            polygonStrokeColor: config.polygonStrokeColor ?? '#c2a84f',
            polygonFillColor: config.polygonFillColor ?? '#7ead59',
            polygonFillOpacity: config.polygonFillOpacity ?? 0.25,
            polygonStrokeWeight: config.polygonStrokeWeight ?? 2,
            selectedPolygonPointIndex: null,
            draggingPolygonPointIndex: null,
            suppressNextMapClick: false,
            polygonPoints: Array.isArray(config.polygonPoints) ? config.polygonPoints.map((point) => ({
                x: Number(point.x ?? 0),
                y: Number(point.y ?? 0),
            })) : [],
            polygonJson: config.polygonJson ?? '[]',
            interactivePreview: config.interactivePreview ?? false,
            init() {
                this.syncPolygonJson();
            },
            normalizePoint(point) {
                return {
                    x: Math.max(0, Math.min(100, Number(point.x ?? 0))),
                    y: Math.max(0, Math.min(100, Number(point.y ?? 0))),
                };
            },
            syncPolygonJson() {
                this.polygonPoints = this.polygonPoints.map((point) => this.normalizePoint(point));
                this.polygonJson = JSON.stringify(this.polygonPoints);

                if (this.interactivePreview) {
                    window.dispatchEvent(new CustomEvent('admin-map-preview-sync'));
                }
            },
            syncPolygonPointsFromJson() {
                try {
                    const parsed = JSON.parse(this.polygonJson);

                    if (!Array.isArray(parsed)) {
                        return;
                    }

                    this.polygonPoints = parsed
                        .filter((point) => point && typeof point === 'object' && 'x' in point && 'y' in point)
                        .map((point) => this.normalizePoint(point));

                    this.syncPolygonJson();
                } catch (error) {
                    // Keep the raw JSON in place until the user finishes editing it.
                }
            },
            removePolygonPoint(index) {
                this.polygonPoints.splice(index, 1);
                this.selectedPolygonPointIndex = this.polygonPoints.length ? Math.min(index, this.polygonPoints.length - 1) : null;
                this.syncPolygonJson();
            },
            clearPolygonPoints() {
                this.polygonPoints = [];
                this.selectedPolygonPointIndex = null;
                this.syncPolygonJson();
            },
            setPolygonPoints(points) {
                this.polygonPoints = Array.isArray(points) ? points.map((point) => this.normalizePoint(point)) : [];
                this.selectedPolygonPointIndex = null;
                this.syncPolygonJson();
            },
            syncPolygonJsonSilently() {
                this.polygonPoints = this.polygonPoints.map((point) => this.normalizePoint(point));
                this.polygonJson = JSON.stringify(this.polygonPoints);
            },
        });

        document.addEventListener('DOMContentLoaded', () => {
            const mapElement = document.getElementById('admin-world-map');

            if (!mapElement || typeof L === 'undefined') {
                return;
            }

            const alpineRoot = mapElement.closest('[x-data]');
            const alpineData = alpineRoot ? Alpine.$data(alpineRoot) : null;
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

            const percentFromLatLng = (latlng) => {
                const point = crs.project(latlng);
                const toPercent = (value, maxExtent) => Math.round(((value / maxExtent) * 100) * 10000) / 10000;

                return {
                    x: Math.max(0, Math.min(100, toPercent(point.x, mapExtent[2]))),
                    y: Math.max(0, Math.min(100, toPercent(point.y, mapExtent[1]))),
                };
            };

            const buildPreviewIcon = () => {
                const iconClass = alpineData?.iconClass?.trim() || 'fa-solid fa-crosshairs';
                const color = alpineData?.color?.trim() || '#7ead59';

                return L.divIcon({
                    className: '',
                    html: `<div class="world-marker is-preview" style="color: ${color};"><i class="${iconClass}" style="font-size: 0.72rem;"></i></div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                });
            };

            const previewMarker = L.marker(pointFromPercent(alpineData?.x ?? 50, alpineData?.y ?? 50), {
                icon: buildPreviewIcon(),
                interactive: false,
                keyboard: false,
                pane: 'markerPaneTop',
            }).addTo(map);

            const previewPolygon = L.polygon([], {
                pane: 'polygonPane',
                color: alpineData?.polygonStrokeColor ?? '#c2a84f',
                fillColor: alpineData?.polygonFillColor ?? '#7ead59',
                fillOpacity: Number(alpineData?.polygonFillOpacity ?? 0.25),
                weight: Number(alpineData?.polygonStrokeWeight ?? 2),
            }).addTo(map);
            const polygonPointHandles = L.layerGroup().addTo(map);

            const buildPointHandleIcon = (index) => L.divIcon({
                className: '',
                html: `<div class="polygon-point-handle">${index + 1}</div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12],
            });

            const syncPreview = () => {
                if (!alpineData) {
                    return;
                }

                previewMarker.setLatLng(pointFromPercent(Number(alpineData.x), Number(alpineData.y)));
                previewMarker.setIcon(buildPreviewIcon());
                previewPolygon.setStyle({
                    color: alpineData.polygonStrokeColor || '#c2a84f',
                    fillColor: alpineData.polygonFillColor || '#7ead59',
                    fillOpacity: Number(alpineData.polygonFillOpacity || 0.25),
                    weight: Number(alpineData.polygonStrokeWeight || 2),
                });
                previewPolygon.setLatLngs((alpineData.polygonPoints || []).map((point) => pointFromPercent(point.x, point.y)));
                polygonPointHandles.clearLayers();

                if (alpineData.mapMode !== 'polygon') {
                    return;
                }

                (alpineData.polygonPoints || []).forEach((point, index) => {
                    const handle = L.marker(pointFromPercent(point.x, point.y), {
                        pane: 'markerPaneTop',
                        draggable: true,
                        icon: buildPointHandleIcon(index),
                    });

                    handle.on('dragstart', () => {
                        alpineData.draggingPolygonPointIndex = index;
                        alpineData.selectedPolygonPointIndex = index;
                        alpineData.suppressNextMapClick = true;
                    });

                    handle.on('drag', (dragEvent) => {
                        const coords = percentFromLatLng(dragEvent.target.getLatLng());
                        alpineData.polygonPoints[index] = coords;
                        alpineData.selectedPolygonPointIndex = index;
                        alpineData.syncPolygonJsonSilently();
                        previewPolygon.setLatLngs((alpineData.polygonPoints || []).map((previewPoint) => pointFromPercent(previewPoint.x, previewPoint.y)));
                    });

                    handle.on('dragend', (dragEvent) => {
                        const coords = percentFromLatLng(dragEvent.target.getLatLng());
                        alpineData.polygonPoints[index] = coords;
                        alpineData.draggingPolygonPointIndex = null;
                        alpineData.selectedPolygonPointIndex = index;
                        alpineData.syncPolygonJson();
                    });

                    handle.on('click', () => {
                        alpineData.selectedPolygonPointIndex = index;
                    });

                    polygonPointHandles.addLayer(handle);
                });
            };

            map.on('click', (event) => {
                if (alpineData?.suppressNextMapClick) {
                    alpineData.suppressNextMapClick = false;
                    return;
                }

                const coords = percentFromLatLng(event.latlng);

                if (!alpineData) {
                    return;
                }

                if (alpineData.mapMode === 'polygon') {
                    alpineData.polygonPoints.push(coords);
                    alpineData.selectedPolygonPointIndex = alpineData.polygonPoints.length - 1;
                    alpineData.syncPolygonJson();
                } else {
                    alpineData.x = coords.x;
                    alpineData.y = coords.y;
                }

                syncPreview();
            });

            polygons.forEach((polygon) => {
                const layer = L.polygon(
                    polygon.coordinates.map((point) => pointFromPercent(point.x, point.y)),
                    {
                        pane: 'polygonPane',
                        color: polygon.stroke_color,
                        fillColor: polygon.fill_color,
                        fillOpacity: polygon.fill_opacity,
                        weight: polygon.stroke_weight,
                    }
                ).addTo(map);

                layer.bindPopup(`
                    <div style="min-width: 200px">
                        <strong>${polygon.name}</strong><br>
                        <span>${polygon.faction ?? 'Unclaimed / Shared'}</span>
                        ${polygon.description ? `<p style="margin: 8px 0 0;">${polygon.description}</p>` : ''}
                    </div>
                `);

                layer.on('click', () => {
                    if (!alpineData) {
                        return;
                    }

                    alpineData.mapMode = 'polygon';
                    alpineData.polygonPoints = polygon.coordinates;
                    alpineData.polygonStrokeColor = polygon.stroke_color;
                    alpineData.polygonFillColor = polygon.fill_color;
                    alpineData.polygonFillOpacity = polygon.fill_opacity;
                    alpineData.polygonStrokeWeight = polygon.stroke_weight;
                    alpineData.selectedPolygonPointIndex = null;
                    alpineData.syncPolygonJson();
                    syncPreview();
                });
            });

            markers.forEach((marker) => {
                const icon = L.divIcon({
                    className: '',
                    html: `<div class="world-marker" style="color: ${marker.color};"><i class="${marker.icon_class}" style="font-size: 0.8rem;"></i></div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                });

                const leafletMarker = L.marker(pointFromPercent(marker.map_x, marker.map_y), { icon, pane: 'markerPaneTop' }).addTo(map);
                leafletMarker.bindPopup(`
                    <div style="min-width: 180px">
                        <strong>${marker.name}</strong><br>
                        <span>${marker.faction ?? 'All factions'}</span><br>
                        <span>X: ${marker.map_x}% | Y: ${marker.map_y}%</span>
                        ${marker.description ? `<p style="margin: 8px 0 0;">${marker.description}</p>` : ''}
                    </div>
                `);

                leafletMarker.on('click', () => {
                    if (!alpineData) {
                        return;
                    }

                    alpineData.mapMode = 'marker';
                    alpineData.x = marker.map_x;
                    alpineData.y = marker.map_y;
                    alpineData.iconClass = marker.icon_class;
                    alpineData.color = marker.color;
                    alpineData.selectedPolygonPointIndex = null;
                    syncPreview();
                });
            });

            ['x', 'y'].forEach((key) => {
                alpineRoot?.querySelector(`[name="map_${key}"]`)?.addEventListener('input', syncPreview);
            });
            ['icon_class', 'color', 'stroke_color', 'fill_color', 'fill_opacity', 'stroke_weight'].forEach((name) => {
                alpineRoot?.querySelectorAll(`[name="${name}"]`)?.forEach((element) => element.addEventListener('input', syncPreview));
            });
            alpineRoot?.querySelector('[data-polygon-clear]')?.addEventListener('click', () => {
                alpineData.clearPolygonPoints();
            });
            window.addEventListener('admin-map-preview-sync', syncPreview);

            syncPreview();
        });
    </script>
@endpush

<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Map Markers</p></x-slot>

    @include('admin.partials.nav')

    <div x-data="mapPolygonForm({ interactivePreview: true })" class="space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Place Markers and Territories</p>
                    <p class="mt-2 text-sm leading-7 text-white/70">Switch between marker mode and polygon mode below. In polygon mode, each map click adds another point to the territory path.</p>
                </div>
                <div class="flex gap-2 text-xs uppercase tracking-[0.24em] text-white/45">
                    <button type="button" @click="mapMode = 'marker'" class="rounded-full border px-4 py-2" :class="mapMode === 'marker' ? 'border-[#7ead59]/40 bg-[#7ead59]/15 text-[#7ead59]' : 'border-white/10 bg-white/5'">Marker Mode</button>
                    <button type="button" @click="mapMode = 'polygon'" class="rounded-full border px-4 py-2" :class="mapMode === 'polygon' ? 'border-[#7ead59]/40 bg-[#7ead59]/15 text-[#7ead59]' : 'border-white/10 bg-white/5'">Polygon Mode</button>
                </div>
            </div>
            <div class="relative mt-5 overflow-hidden rounded-[2rem] border border-white/10 bg-black/20">
                <div id="admin-world-map"></div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <form method="POST" action="{{ route('admin.map-markers.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Map Marker</p>
                <p class="mt-2 text-sm text-white/60">Markers stay above the polygon layers so players can still click them.</p>
                <div class="mt-4 grid gap-4">
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Marker Name</span>
                        <span class="text-xs text-white/45">The label shown to players in the popup.</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Marker name" required>
                    </label>
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Faction Visibility</span>
                        <span class="text-xs text-white/45">Leave shared if every faction should see this marker.</span>
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                            <option value="">Visible to all factions</option>
                            @foreach ($factions as $faction)
                                <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Marker Icon</span>
                        <span class="text-xs text-white/45">Use a Font Awesome class for the marker symbol.</span>
                        <input x-model="iconClass" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" placeholder="Font Awesome class" required>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Map X</span>
                            <span class="text-xs text-white/45">Horizontal percentage position.</span>
                            <input x-model="x" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" placeholder="Map X %" required>
                        </label>
                        <label class="grid gap-2 text-sm text-white/70">
                            <span class="uppercase tracking-[0.18em] text-white/45">Map Y</span>
                            <span class="text-xs text-white/45">Vertical percentage position.</span>
                            <input x-model="y" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" placeholder="Map Y %" required>
                        </label>
                    </div>
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Marker Color</span>
                        <span class="text-xs text-white/45">Pick the icon color for this marker.</span>
                        <div class="grid grid-cols-[84px_1fr] gap-3">
                            <input x-model="color" class="h-12 w-full rounded-2xl border border-white/10 bg-black/25 p-1" name="color_picker" type="color" aria-label="Marker color picker">
                            <input x-model="color" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="color" placeholder="Hex color">
                        </div>
                    </label>
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Description</span>
                        <span class="text-xs text-white/45">Optional popup text for extra context.</span>
                        <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Marker description"></textarea>
                    </label>
                    <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Marker</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.map-polygons.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Territory Polygon</p>
                <p class="mt-2 text-sm text-white/60">Click the map in polygon mode to add territory points. At least 3 are required.</p>
                <div class="mt-4 grid gap-4">
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Polygon Name</span>
                        <span class="text-xs text-white/45">Use the territory or district name shown to players.</span>
                        <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Polygon name" required>
                    </label>
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Owning Faction</span>
                        <span class="text-xs text-white/45">Assign ownership or leave it shared.</span>
                        <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                            <option value="">Shared / unclaimed</option>
                            @foreach ($factions as $faction)
                                <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid grid-cols-[84px_1fr] gap-3">
                            <input x-model="polygonStrokeColor" class="h-12 w-full rounded-2xl border border-white/10 bg-black/25 p-1" type="color" aria-label="Polygon stroke color picker">
                            <input x-model="polygonStrokeColor" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="stroke_color" placeholder="Stroke color">
                        </div>
                        <div class="grid grid-cols-[84px_1fr] gap-3">
                            <input x-model="polygonFillColor" class="h-12 w-full rounded-2xl border border-white/10 bg-black/25 p-1" type="color" aria-label="Polygon fill color picker">
                            <input x-model="polygonFillColor" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="fill_color" placeholder="Fill color">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <input x-model="polygonFillOpacity" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="fill_opacity" type="number" min="0" max="1" step="0.05" value="0.25" required>
                        <input x-model="polygonStrokeWeight" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="stroke_weight" type="number" min="1" max="10" value="2" required>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-white/45">Polygon Points</p>
                            <div class="rounded-full border border-white/10 bg-black/20 px-4 py-2 text-xs uppercase tracking-[0.18em] text-white/55">
                                Points: <span x-text="polygonPoints.length"></span>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <template x-for="(point, index) in polygonPoints" :key="index">
                                <div class="grid grid-cols-[1fr_1fr_auto] gap-3">
                                    <input x-model.number="point.x" @input="syncPolygonJson()" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" min="0" max="100" step="0.0001" placeholder="X %">
                                    <input x-model.number="point.y" @input="syncPolygonJson()" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" min="0" max="100" step="0.0001" placeholder="Y %">
                                    <button type="button" @click="removePolygonPoint(index)" class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Remove</button>
                                </div>
                            </template>
                            <p x-show="polygonPoints.length === 0" class="text-sm text-white/45">No points yet. Click on the map in polygon mode to add them.</p>
                            <p class="text-xs text-white/45">Each point also appears as a draggable marker on the map preview.</p>
                        </div>
                    </div>
                    <label class="grid gap-2 text-sm text-white/70">
                        <span class="uppercase tracking-[0.18em] text-white/45">Raw Point JSON</span>
                        <span class="text-xs text-white/45">Advanced editing field. Changes here update the list and map preview too.</span>
                        <textarea x-model="polygonJson" @input="syncPolygonPointsFromJson()" class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 font-mono text-xs" name="coordinates_json" placeholder='[{"x":10.125,"y":10.875},{"x":20.25,"y":20.5},{"x":30.375,"y":10.125}]' required></textarea>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-polygon-clear class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Clear Points</button>
                    </div>
                    <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Polygon description"></textarea>
                    <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Polygon</button>
                </div>
            </form>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div x-data="{ openId: null }" class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
                <div class="border-b border-white/10 px-5 py-4">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Markers</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-white/75">
                        <tbody class="divide-y divide-white/10">
                            @foreach ($markers as $marker)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-white">{{ $marker->name }}</td>
                                    <td class="px-5 py-4">{{ $marker->map_x }}%, {{ $marker->map_y }}%</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="openId = openId === {{ $marker->id }} ? null : {{ $marker->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                            <form method="POST" action="{{ route('admin.map-markers.destroy', $marker) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr x-show="openId === {{ $marker->id }}" x-cloak>
                                    <td colspan="3" class="px-5 pb-5">
                                        <form method="POST" action="{{ route('admin.map-markers.update', $marker) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5">
                                            @csrf
                                            @method('PATCH')
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $marker->name }}" required>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                                                <option value="">Visible to all factions</option>
                                                @foreach ($factions as $faction)
                                                    <option value="{{ $faction->id }}" @selected($marker->faction_id === $faction->id)>{{ $faction->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="grid gap-4 xl:grid-cols-4">
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" value="{{ $marker->icon_class }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" value="{{ $marker->map_x }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" value="{{ $marker->map_y }}" required>
                                                <div class="grid grid-cols-[84px_1fr] gap-3 xl:col-span-1">
                                                    <input class="h-12 w-full rounded-2xl border border-white/10 bg-black/25 p-1" type="color" value="{{ $marker->color ?: '#c2a84f' }}" oninput="this.nextElementSibling.value = this.value" aria-label="Marker color picker">
                                                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="color" value="{{ $marker->color }}" placeholder="#7ead59">
                                                </div>
                                            </div>
                                            <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description">{{ $marker->description }}</textarea>
                                            <div class="flex justify-end">
                                                <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-data="{ openId: null }" class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
                <div class="border-b border-white/10 px-5 py-4">
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Polygons</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-white/75">
                        <tbody class="divide-y divide-white/10">
                            @foreach ($polygons as $polygon)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-white">{{ $polygon->name }}</td>
                                    <td class="px-5 py-4">{{ count($polygon->coordinates ?? []) }} pts</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="openId = openId === {{ $polygon->id }} ? null : {{ $polygon->id }}" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Edit</button>
                                            <form method="POST" action="{{ route('admin.map-polygons.destroy', $polygon) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr x-show="openId === {{ $polygon->id }}" x-cloak>
                                    <td colspan="3" class="px-5 pb-5">
                                        <form
                                            method="POST"
                                            action="{{ route('admin.map-polygons.update', $polygon) }}"
                                            x-data="mapPolygonForm({
                                                polygonStrokeColor: @js($polygon->stroke_color ?: '#c2a84f'),
                                                polygonFillColor: @js($polygon->fill_color ?: '#7ead59'),
                                                polygonFillOpacity: {{ (float) $polygon->fill_opacity }},
                                                polygonStrokeWeight: {{ (int) $polygon->stroke_weight }},
                                                polygonPoints: @js($polygon->coordinates ?? []),
                                                interactivePreview: false
                                            })"
                                            class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-xs uppercase tracking-[0.18em] text-white/45">Edit Polygon Points</p>
                                                <div class="rounded-full border border-white/10 bg-black/20 px-4 py-2 text-xs uppercase tracking-[0.18em] text-white/55">
                                                    Points: <span x-text="polygonPoints.length"></span>
                                                </div>
                                            </div>
                                            <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $polygon->name }}" required>
                                            <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                                                <option value="">Shared / unclaimed</option>
                                                @foreach ($factions as $faction)
                                                    <option value="{{ $faction->id }}" @selected($polygon->faction_id === $faction->id)>{{ $faction->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="grid gap-4 xl:grid-cols-2">
                                                <div class="grid grid-cols-[84px_1fr] gap-3">
                                                    <input x-model="polygonStrokeColor" class="h-12 w-full rounded-2xl border border-white/10 bg-black/25 p-1" type="color" aria-label="Polygon stroke color picker">
                                                    <input x-model="polygonStrokeColor" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="stroke_color">
                                                </div>
                                                <div class="grid grid-cols-[84px_1fr] gap-3">
                                                    <input x-model="polygonFillColor" class="h-12 w-full rounded-2xl border border-white/10 bg-black/25 p-1" type="color" aria-label="Polygon fill color picker">
                                                    <input x-model="polygonFillColor" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="fill_color">
                                                </div>
                                            </div>
                                            <div class="grid gap-4 xl:grid-cols-2">
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="fill_opacity" type="number" min="0" max="1" step="0.05" value="{{ $polygon->fill_opacity }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="stroke_weight" type="number" min="1" max="10" value="{{ $polygon->stroke_weight }}" required>
                                            </div>
                                            <div class="rounded-[1.5rem] border border-white/10 bg-black/20 p-4">
                                                <div class="space-y-3">
                                                    <template x-for="(point, index) in polygonPoints" :key="index">
                                                        <div class="grid grid-cols-[1fr_1fr_auto] gap-3">
                                                            <input x-model.number="point.x" @input="syncPolygonJson()" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" min="0" max="100" step="0.0001" placeholder="X %">
                                                            <input x-model.number="point.y" @input="syncPolygonJson()" class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" type="number" min="0" max="100" step="0.0001" placeholder="Y %">
                                                            <button type="button" @click="removePolygonPoint(index)" class="rounded-full border border-[#c65b3f]/40 bg-[#c65b3f]/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#f0b29f]">Remove</button>
                                                        </div>
                                                    </template>
                                                    <p x-show="polygonPoints.length === 0" class="text-sm text-white/45">No points remain. Add new ones in the JSON field or recreate from the map above.</p>
                                                </div>
                                            </div>
                                            <textarea x-model="polygonJson" @input="syncPolygonPointsFromJson()" class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3 font-mono text-xs" name="coordinates_json" required></textarea>
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" @click="clearPolygonPoints()" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]">Clear Points</button>
                                            </div>
                                            <textarea class="min-h-24 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description">{{ $polygon->description }}</textarea>
                                            <div class="flex justify-end">
                                                <button class="rounded-full bg-[#7ead59] px-5 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#07100c]">Save</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
