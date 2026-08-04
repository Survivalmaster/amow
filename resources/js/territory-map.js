import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const TILE_TYPES_HIDDEN_BY_DEFAULT = new Set(['inactive', 'decorative']);

const asLeafletPoints = (points) => points.map((point) => [Number(point.y), Number(point.x)]);

const pretty = (value) => {
    if (value === null || value === undefined || value === '') return '-';
    return String(value).replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
};

const jsonFetch = async (url, options = {}) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        },
        ...options,
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(payload.message || Object.values(payload.errors || {})?.flat()?.[0] || 'Request failed.');
    }

    return payload;
};

const tileStyle = (hex, mode, selectedId = null) => {
    const factionColour = hex.faction?.colour || hex.faction?.color;
    const isSelected = selectedId === hex.id;
    const base = {
        color: '#e7e1c7',
        weight: mode === 'territory' ? 0.45 : 1,
        opacity: mode === 'territory' ? 0.35 : 0.65,
        fillOpacity: mode === 'territory' ? 0.38 : 0.18,
        fillColor: '#8b949e',
    };

    if (hex.tile_type === 'water') {
        base.fillColor = '#3478c5';
        base.color = '#a9d6e5';
        base.fillOpacity = 0.18;
    } else if (hex.tile_type === 'blocked') {
        base.fillColor = '#111827';
        base.color = '#6b7280';
        base.fillOpacity = 0.3;
    } else if (hex.tile_type === 'claimable' && factionColour) {
        base.fillColor = factionColour;
        base.color = factionColour;
        base.fillOpacity = mode === 'territory' ? 0.5 : 0.24;
    } else if (mode === 'unclaimed' && hex.tile_type === 'claimable' && !hex.faction) {
        base.fillColor = '#f4d77a';
        base.color = '#f4d77a';
        base.fillOpacity = 0.42;
    }

    if (isSelected) {
        base.color = '#ffffff';
        base.weight = 3;
        base.opacity = 1;
        base.fillOpacity = Math.max(base.fillOpacity, 0.45);
    }

    return base;
};

export function bootTerritoryMap(root) {
    if (!root) return;

    const mapWidth = Number(root.dataset.mapWidth || 1536);
    const mapHeight = Number(root.dataset.mapHeight || 1024);
    const bounds = [[0, 0], [mapHeight, mapWidth]];
    const canManage = root.dataset.canManage === 'true';
    const csrfToken = root.dataset.csrfToken || '';
    const factions = JSON.parse(root.dataset.factions || '[]');
    const status = root.querySelector('[data-territory-status]');
    const mapElement = root.querySelector('#territory-leaflet-map');
    const selectedFields = root.querySelectorAll('[data-selected-field]');
    const selectedSummary = root.querySelector('[data-selected-summary]');
    const editor = root.querySelector('[data-territory-editor]');
    const brushEnabled = root.querySelector('[data-brush-enabled]');
    const brushType = root.querySelector('[data-brush-type]');
    const closePanel = root.querySelector('[data-close-panel]');
    let mode = 'hex';
    let selectedHex = null;
    const layers = new Map();

    const map = L.map(mapElement, {
        crs: L.CRS.Simple,
        zoomControl: false,
        minZoom: -2,
        maxZoom: 2,
        maxBounds: bounds,
        maxBoundsViscosity: 0.9,
        zoomSnap: 0.25,
    });

    L.imageOverlay(root.dataset.mapImageUrl, bounds).addTo(map);
    map.fitBounds(bounds);
    requestAnimationFrame(() => {
        map.invalidateSize();
        map.fitBounds(bounds, { animate: false });
    });

    const setStatus = (message, tone = 'neutral') => {
        if (!status) return;
        status.textContent = message;
        status.dataset.tone = tone;
    };

    const setMode = async (nextMode) => {
        mode = nextMode;
        root.classList.toggle('is-admin-mode', mode === 'admin' && canManage);
        root.querySelectorAll('[data-territory-mode]').forEach((candidate) => {
            candidate.classList.toggle('is-active', candidate.dataset.territoryMode === mode);
        });
        await loadHexes();
    };

    const updateSelectedPanel = () => {
        root.classList.toggle('has-selection', Boolean(selectedHex));

        const values = {
            id: selectedHex?.id,
            grid: selectedHex ? `${selectedHex.grid_column}, ${selectedHex.grid_row}` : null,
            tile_type: pretty(selectedHex?.tile_type),
            terrain_type: selectedHex?.terrain_type || '-',
            faction: selectedHex?.faction?.name || 'Unclaimed',
            claim_strength: selectedHex?.claim_strength,
            claimed_at: selectedHex?.claimed_at ? new Date(selectedHex.claimed_at).toLocaleString() : '-',
        };

        selectedFields.forEach((field) => {
            field.textContent = values[field.dataset.selectedField] ?? '-';
        });

        if (selectedSummary) {
            selectedSummary.textContent = selectedHex ? `Hex ${selectedHex.id} selected.` : 'Click a visible tile to inspect it.';
        }

        if (editor && selectedHex) {
            editor.tile_type.value = selectedHex.tile_type;
            editor.terrain_type.value = selectedHex.terrain_type || '';
            editor.faction_id.value = selectedHex.faction?.id || '';
            editor.claim_strength.value = selectedHex.claim_strength || 0;
            editor.is_visible.checked = Boolean(selectedHex.is_visible);
        }
    };

    closePanel?.addEventListener('click', () => {
        selectedHex = null;
        layers.forEach((candidate) => candidate.setStyle(tileStyle(candidate.hexData, mode, null)));
        updateSelectedPanel();

        if (mode === 'admin') {
            setMode('hex');
        }
    });

    const shouldDraw = (hex) => {
        if (mode === 'admin' && canManage) return true;
        return hex.is_visible && !TILE_TYPES_HIDDEN_BY_DEFAULT.has(hex.tile_type);
    };

    const renderHex = (hex) => {
        let layer = layers.get(hex.id);

        if (!shouldDraw(hex)) {
            if (layer) {
                layer.remove();
                layers.delete(hex.id);
            }
            return;
        }

        if (!layer) {
            layer = L.polygon(asLeafletPoints(hex.polygon_coordinates), tileStyle(hex, mode, selectedHex?.id));
            layer.on('click', async () => {
                const currentHex = layer.hexData;

                selectedHex = currentHex;
                if (canManage && brushEnabled?.checked && brushType?.value) {
                    await updateHex(currentHex, { tile_type: brushType.value });
                    return;
                }
                layers.forEach((candidate) => candidate.setStyle(tileStyle(candidate.hexData, mode, selectedHex?.id)));
                layer.setStyle(tileStyle(currentHex, mode, selectedHex?.id));
                updateSelectedPanel();
            });
            layer.on('mouseover', () => layer.setStyle({ fillOpacity: Math.min(0.72, (tileStyle(layer.hexData, mode, selectedHex?.id).fillOpacity || 0.2) + 0.22) }));
            layer.on('mouseout', () => layer.setStyle(tileStyle(layer.hexData, mode, selectedHex?.id)));
            layer.hexData = hex;
            layer.addTo(map);
            layers.set(hex.id, layer);
        } else {
            layer.hexData = hex;
            layer.setLatLngs(asLeafletPoints(hex.polygon_coordinates));
            layer.setStyle(tileStyle(hex, mode, selectedHex?.id));
        }
    };

    const savedHexFrom = (payload) => {
        const hex = payload?.data?.id ? payload.data : payload;

        if (!hex?.id) {
            return null;
        }

        return hex;
    };

    const refreshedHex = async (hexId) => {
        const payload = await jsonFetch(`/api/map/hexes/${hexId}`);
        const hex = savedHexFrom(payload);

        if (!hex?.id) {
            throw new Error(payload?.message || 'Tile saved, but the updated tile could not be reloaded.');
        }

        return hex;
    };

    const upsertHex = (hex) => {
        if (!hex?.id) {
            throw new Error('Tile saved, but the server did not return the updated tile data.');
        }

        const existing = layers.get(hex.id);
        if (existing) existing.hexData = hex;
        if (selectedHex?.id === hex.id) selectedHex = hex;
        renderHex(hex);
        updateSelectedPanel();
    };

    const updateHex = async (hex, payload) => {
        try {
            setStatus('Saving tile...');
            const data = await jsonFetch(`/api/map/hexes/${hex.id}/update`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify(payload),
            });
            upsertHex(savedHexFrom(data) || await refreshedHex(hex.id));
            setStatus('Tile saved.', 'success');
        } catch (error) {
            setStatus(error.message, 'error');
        }
    };

    root.querySelectorAll('[data-territory-mode]').forEach((button) => {
        button.addEventListener('click', async () => {
            await setMode(button.dataset.territoryMode);
        });
    });

    if (editor) {
        editor.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!selectedHex) return;
            await updateHex(selectedHex, {
                tile_type: editor.tile_type.value,
                terrain_type: editor.terrain_type.value || null,
                faction_id: editor.faction_id.value || null,
                claim_strength: Number(editor.claim_strength.value || 0),
                is_visible: editor.is_visible.checked,
            });
        });

        root.querySelector('[data-claim-selected]')?.addEventListener('click', async () => {
            if (!selectedHex || !editor.faction_id.value) return setStatus('Select a faction before claiming.', 'error');
            try {
                const data = await jsonFetch(`/api/map/hexes/${selectedHex.id}/claim`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ faction_id: editor.faction_id.value }),
                });
                upsertHex(savedHexFrom(data) || await refreshedHex(selectedHex.id));
                setStatus('Tile claimed.', 'success');
            } catch (error) {
                setStatus(error.message, 'error');
            }
        });

        root.querySelector('[data-remove-claim]')?.addEventListener('click', async () => {
            if (!selectedHex) return;
            try {
                const data = await jsonFetch(`/api/map/hexes/${selectedHex.id}/claim/remove`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                upsertHex(savedHexFrom(data) || await refreshedHex(selectedHex.id));
                setStatus('Claim removed.', 'success');
            } catch (error) {
                setStatus(error.message, 'error');
            }
        });
    }

    async function loadHexes() {
        try {
            setStatus('Loading territory grid...');
            const includeHidden = mode === 'admin' && canManage ? '?include_hidden=1' : '';
            const payload = await jsonFetch(`${root.dataset.hexesUrl}${includeHidden}`);
            const incomingIds = new Set();
            payload.data.forEach((hex) => {
                incomingIds.add(hex.id);
                renderHex(hex);
            });
            layers.forEach((layer, id) => {
                if (!incomingIds.has(id)) {
                    layer.remove();
                    layers.delete(id);
                }
            });
            setStatus(`${payload.data.length.toLocaleString()} hexes loaded.`);
        } catch (error) {
            setStatus(error.message, 'error');
        }
    }

    loadHexes();
}
