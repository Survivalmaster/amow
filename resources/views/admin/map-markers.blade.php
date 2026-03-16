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
            width: 2.5rem;
            height: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            background: rgba(4, 8, 6, 0.92);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        }

        .world-marker.is-preview {
            color: #7ead59;
            border-color: rgba(126, 173, 89, 0.4);
        }
    </style>
@endpush

<x-app-layout>
    <x-slot name="header"><p class="font-['Teko'] text-5xl uppercase tracking-[0.12em]">Admin: Map Markers</p></x-slot>

    @include('admin.partials.nav')

    <div
        x-data="{
            x: 50,
            y: 50,
            setCoords(event) {
                const rect = event.currentTarget.getBoundingClientRect();
                this.x = Math.max(0, Math.min(100, Math.round(((event.clientX - rect.left) / rect.width) * 100)));
                this.y = Math.max(0, Math.min(100, Math.round(((event.clientY - rect.top) / rect.height) * 100)));
            }
        }"
        class="space-y-6"
    >
        <section class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Place Markers on the Map</p>
                    <p class="mt-2 text-sm leading-7 text-white/70">Click anywhere on the map to set the coordinates for the marker form below. The new Leaflet map uses the tiles in <code>/public/mapstyles/stylePlastica</code>.</p>
                </div>
                <div class="text-xs uppercase tracking-[0.24em] text-white/45">Click to capture X/Y</div>
            </div>
            <div class="relative mt-5 overflow-hidden rounded-[2rem] border border-white/10 bg-black/20">
                <div id="admin-world-map"></div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
            <form method="POST" action="{{ route('admin.map-markers.store') }}" class="rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30">
                @csrf
                <p class="font-['Teko'] text-3xl uppercase tracking-[0.12em]">Create Map Marker</p>
                <p class="mt-2 text-sm text-white/60">Places a visual marker on the world map for all factions or a faction-specific audience.</p>
                <div class="grid gap-4">
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" placeholder="Marker name" required>
                    <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                        <option value="">Visible to all factions</option>
                        @foreach ($factions as $faction)
                            <option value="{{ $faction->id }}">{{ $faction->name }}</option>
                        @endforeach
                    </select>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" placeholder="Font Awesome class, e.g. fa-solid fa-tower-observation" required>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs uppercase tracking-[0.2em] text-white/50">Map X %</label>
                            <input x-model="x" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs uppercase tracking-[0.2em] text-white/50">Map Y %</label>
                            <input x-model="y" class="w-full rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" required>
                        </div>
                    </div>
                    <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="color" placeholder="Hex color, e.g. #d94a3a">
                    <textarea class="min-h-28 rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="description" placeholder="Marker description"></textarea>
                    <div class="rounded-2xl border border-white/10 bg-black/20 px-4 py-4 text-sm text-white/65">
                        Click the map above to fill X/Y. Suggested icons: `fa-solid fa-flag`, `fa-solid fa-industry`, `fa-solid fa-coins`, `fa-solid fa-skull-crossbones`, `fa-solid fa-landmark`.
                    </div>
                    <button class="rounded-full bg-[#7ead59] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#07100c]">Create Marker</button>
                </div>
            </form>

            <div x-data="{ openId: null }" class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 shadow-2xl shadow-black/30">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-white/75">
                        <thead class="bg-black/30 text-xs uppercase tracking-[0.2em] text-white/40">
                            <tr>
                                <th class="px-5 py-4 text-left">Name</th>
                                <th class="px-5 py-4 text-left">Faction</th>
                                <th class="px-5 py-4 text-left">Coords</th>
                                <th class="px-5 py-4 text-left">Icon</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach ($markers as $marker)
                                <tr>
                                    <td class="px-5 py-4 font-semibold text-white">{{ $marker->name }}</td>
                                    <td class="px-5 py-4">{{ $marker->faction?->name ?? 'All factions' }}</td>
                                    <td class="px-5 py-4">{{ $marker->map_x }}%, {{ $marker->map_y }}%</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-black/20" style="color: {{ $marker->color ?: '#c2a84f' }};">
                                            <i class="{{ $marker->icon_class }}"></i>
                                        </span>
                                    </td>
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
                                    <td colspan="5" class="px-5 pb-5">
                                        <form method="POST" action="{{ route('admin.map-markers.update', $marker) }}" class="grid gap-4 rounded-[1.5rem] border border-white/10 bg-black/20 p-5">
                                            @csrf
                                            @method('PATCH')
                                            <div class="grid gap-4 xl:grid-cols-[1fr_220px]">
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="name" value="{{ $marker->name }}" required>
                                                <select class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="faction_id">
                                                    <option value="">Visible to all factions</option>
                                                    @foreach ($factions as $faction)
                                                        <option value="{{ $faction->id }}" @selected($marker->faction_id === $faction->id)>{{ $faction->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="grid gap-4 xl:grid-cols-[1fr_120px_120px_160px]">
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="icon_class" value="{{ $marker->icon_class }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_x" type="number" min="0" max="100" value="{{ $marker->map_x }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="map_y" type="number" min="0" max="100" value="{{ $marker->map_y }}" required>
                                                <input class="rounded-2xl border border-white/10 bg-black/25 px-4 py-3" name="color" value="{{ $marker->color }}" placeholder="#7ead59">
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
        </div>
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
            const mapElement = document.getElementById('admin-world-map');

            if (!mapElement || typeof L === 'undefined') {
                return;
            }

            const alpineRoot = mapElement.closest('[x-data]');
            const alpineData = alpineRoot ? Alpine.$data(alpineRoot) : null;
            const markers = @json(
                $markers->map(fn ($marker) => [
                    'id' => $marker->id,
                    'name' => $marker->name,
                    'description' => $marker->description,
                    'icon_class' => $marker->icon_class,
                    'map_x' => (int) $marker->map_x,
                    'map_y' => (int) $marker->map_y,
                    'color' => $marker->color ?: '#c2a84f',
                    'faction' => $marker->faction?->name,
                ])->values()
            );

            const mapExtent = [0, -8192, 8192, 0];
            const mapMinZoom = 0;
            const mapMaxZoom = 9;
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

            const percentFromLatLng = (latlng) => {
                const point = crs.project(latlng);
                return {
                    x: Math.max(0, Math.min(100, Math.round((point.x / mapExtent[2]) * 100))),
                    y: Math.max(0, Math.min(100, Math.round((point.y / mapExtent[1]) * 100))),
                };
            };

            const previewIcon = L.divIcon({
                className: '',
                html: '<div class="world-marker is-preview"><i class="fa-solid fa-crosshairs text-[10px]"></i></div>',
                iconSize: [40, 40],
                iconAnchor: [20, 20],
            });

            const previewMarker = L.marker(pointFromPercent(alpineData?.x ?? 50, alpineData?.y ?? 50), {
                icon: previewIcon,
                interactive: false,
                keyboard: false,
            }).addTo(map);

            const syncPreview = () => {
                if (!alpineData) {
                    return;
                }

                previewMarker.setLatLng(pointFromPercent(Number(alpineData.x), Number(alpineData.y)));
            };

            map.on('click', (event) => {
                const coords = percentFromLatLng(event.latlng);

                if (alpineData) {
                    alpineData.x = coords.x;
                    alpineData.y = coords.y;
                }

                syncPreview();
            });

            markers.forEach((marker) => {
                const icon = L.divIcon({
                    className: '',
                    html: `<div class="world-marker" style="color: ${marker.color};"><i class="${marker.icon_class} text-sm"></i></div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20],
                });

                const leafletMarker = L.marker(pointFromPercent(marker.map_x, marker.map_y), { icon }).addTo(map);
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

                    alpineData.x = marker.map_x;
                    alpineData.y = marker.map_y;
                    syncPreview();
                });
            });

            ['x', 'y'].forEach((key) => {
                alpineRoot?.querySelector(`[name="map_${key}"]`)?.addEventListener('input', syncPreview);
            });
        });
    </script>
@endpush
