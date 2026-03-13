function parseJSON(value, fallback) {
    try {
        return JSON.parse(value);
    } catch {
        return fallback;
    }
}

const QUETZALTENANGO_CENTER = [14.8347, -91.5186];
const QUETZALTENANGO_ZOOM = 13;

function roundCoordinate(value) {
    return Number(Number(value).toFixed(7));
}

function haversineDistanceKm(points) {
    if (!Array.isArray(points) || points.length < 2) {
        return 0;
    }

    let distance = 0;

    for (let i = 1; i < points.length; i += 1) {
        const lat1 = (points[i - 1].lat * Math.PI) / 180;
        const lon1 = (points[i - 1].lng * Math.PI) / 180;
        const lat2 = (points[i].lat * Math.PI) / 180;
        const lon2 = (points[i].lng * Math.PI) / 180;

        const dLat = lat2 - lat1;
        const dLon = lon2 - lon1;

        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos(lat1) * Math.cos(lat2) * Math.sin(dLon / 2) ** 2;

        distance += 6371 * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    }

    return Number(distance.toFixed(2));
}

function createZonePicker(config) {
    const { mapContainerId, latField, lngField, clearButton } = config;

    if (typeof L === 'undefined') {
        return null;
    }

    const map = L.map(mapContainerId).setView(QUETZALTENANGO_CENTER, QUETZALTENANGO_ZOOM);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let marker = null;

    const setPoint = (lat, lng) => {
        const normalizedLat = roundCoordinate(lat);
        const normalizedLng = roundCoordinate(lng);

        latField.value = normalizedLat;
        lngField.value = normalizedLng;

        if (marker) {
            marker.setLatLng([normalizedLat, normalizedLng]);
        } else {
            marker = L.marker([normalizedLat, normalizedLng]).addTo(map);
        }
    };

    map.on('click', (event) => {
        setPoint(event.latlng.lat, event.latlng.lng);
    });

    clearButton.addEventListener('click', () => {
        latField.value = '';
        lngField.value = '';

        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        map.setView(QUETZALTENANGO_CENTER, QUETZALTENANGO_ZOOM);
    });

    return {
        reset() {
            latField.value = '';
            lngField.value = '';

            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }

            map.setView(QUETZALTENANGO_CENTER, QUETZALTENANGO_ZOOM);
        },
        invalidate() {
            setTimeout(() => map.invalidateSize(), 120);
        },
    };
}

function createRouteEditor(config) {
    const {
        mapContainerId,
        hiddenField,
        latStartField,
        lngStartField,
        latEndField,
        lngEndField,
        distanceField,
        clearButton,
    } = config;

    if (typeof L === 'undefined') {
        return null;
    }

    const map = L.map(mapContainerId).setView(QUETZALTENANGO_CENTER, QUETZALTENANGO_ZOOM);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let points = [];
    let markers = [];
    let polyline = null;

    const refresh = () => {
        markers.forEach((marker) => map.removeLayer(marker));
        markers = points.map((point, index) =>
            L.marker([point.lat, point.lng])
                .addTo(map)
                .bindTooltip(index === 0 ? 'Inicio' : index === points.length - 1 ? 'Fin' : `Parada ${index}`),
        );

        if (polyline) {
            map.removeLayer(polyline);
            polyline = null;
        }

        if (points.length > 1) {
            polyline = L.polyline(points, { color: '#0d6efd', weight: 4 }).addTo(map);
        }

        hiddenField.value = JSON.stringify(points);

        const start = points[0];
        const end = points[points.length - 1];

        latStartField.value = start ? start.lat : '';
        lngStartField.value = start ? start.lng : '';
        latEndField.value = end ? end.lat : '';
        lngEndField.value = end ? end.lng : '';
        distanceField.value = haversineDistanceKm(points) || '';
    };

    map.on('click', (event) => {
        points.push({
            lat: roundCoordinate(event.latlng.lat),
            lng: roundCoordinate(event.latlng.lng),
        });

        refresh();
    });

    clearButton.addEventListener('click', () => {
        points = [];
        refresh();
    });

    return {
        setPoints(newPoints) {
            points = Array.isArray(newPoints)
                ? newPoints
                      .map((point) => ({ lat: roundCoordinate(point.lat), lng: roundCoordinate(point.lng) }))
                      .filter((point) => Number.isFinite(point.lat) && Number.isFinite(point.lng))
                : [];

            refresh();

            if (points.length > 0) {
                map.fitBounds(L.latLngBounds(points), { padding: [20, 20] });
            } else {
                map.setView(QUETZALTENANGO_CENTER, QUETZALTENANGO_ZOOM);
            }

            setTimeout(() => map.invalidateSize(), 120);
        },
        invalidate() {
            setTimeout(() => map.invalidateSize(), 120);
        },
    };
}

window.addEventListener('DOMContentLoaded', () => {
    const zonePicker = createZonePicker({
        mapContainerId: 'createZoneMap',
        latField: document.getElementById('create_zone_latitud'),
        lngField: document.getElementById('create_zone_longitud'),
        clearButton: document.getElementById('createZoneClearBtn'),
    });

    const createZoneModal = document.getElementById('createZoneModal');
    if (createZoneModal && zonePicker) {
        createZoneModal.addEventListener('show.bs.modal', () => {
            zonePicker.reset();
        });

        createZoneModal.addEventListener('shown.bs.modal', () => {
            zonePicker.invalidate();
        });
    }

    const createEditor = createRouteEditor({
        mapContainerId: 'createRouteMap',
        hiddenField: document.getElementById('create_coordenadas_json'),
        latStartField: document.getElementById('create_lat_inicio'),
        lngStartField: document.getElementById('create_lon_inicio'),
        latEndField: document.getElementById('create_lat_fin'),
        lngEndField: document.getElementById('create_lon_fin'),
        distanceField: document.getElementById('create_distancia_km'),
        clearButton: document.getElementById('createClearRouteBtn'),
    });

    const editEditor = createRouteEditor({
        mapContainerId: 'editRouteMap',
        hiddenField: document.getElementById('edit_coordenadas_json'),
        latStartField: document.getElementById('edit_lat_inicio'),
        lngStartField: document.getElementById('edit_lon_inicio'),
        latEndField: document.getElementById('edit_lat_fin'),
        lngEndField: document.getElementById('edit_lon_fin'),
        distanceField: document.getElementById('edit_distancia_km'),
        clearButton: document.getElementById('editClearRouteBtn'),
    });

    const createModal = document.getElementById('createRouteModal');
    if (createModal && createEditor) {
        createModal.addEventListener('show.bs.modal', () => {
            createEditor.setPoints([]);
        });

        createModal.addEventListener('shown.bs.modal', () => {
            createEditor.invalidate();
        });
    }

    const editRouteModal = document.getElementById('editRouteModal');
    const editRouteForm = document.getElementById('editRouteForm');

    if (editRouteModal && editRouteForm) {
        const fields = {
            nombre: document.getElementById('edit_nombre'),
            idZona: document.getElementById('edit_id_zona'),
            tipoResiduo: document.getElementById('edit_tipo_residuo'),
            distanciaKm: document.getElementById('edit_distancia_km'),
            horarioInicio: document.getElementById('edit_horario_inicio'),
            horarioFin: document.getElementById('edit_horario_fin'),
            latInicio: document.getElementById('edit_lat_inicio'),
            lonInicio: document.getElementById('edit_lon_inicio'),
            latFin: document.getElementById('edit_lat_fin'),
            lonFin: document.getElementById('edit_lon_fin'),
        };

        editRouteModal.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;

            if (!trigger) {
                return;
            }

            const routeId = trigger.getAttribute('data-route-id') || '';
            const baseAction = editRouteForm.getAttribute('data-base-action') || '';
            editRouteForm.action = `${baseAction}/${routeId}`;

            fields.nombre.value = trigger.getAttribute('data-route-name') || '';
            fields.idZona.value = trigger.getAttribute('data-route-zone') || '';
            fields.tipoResiduo.value = trigger.getAttribute('data-route-type') || '';
            fields.distanciaKm.value = trigger.getAttribute('data-route-distance') || '';
            fields.horarioInicio.value = trigger.getAttribute('data-route-start-time') || '';
            fields.horarioFin.value = trigger.getAttribute('data-route-end-time') || '';
            fields.latInicio.value = trigger.getAttribute('data-route-start-lat') || '';
            fields.lonInicio.value = trigger.getAttribute('data-route-start-lng') || '';
            fields.latFin.value = trigger.getAttribute('data-route-end-lat') || '';
            fields.lonFin.value = trigger.getAttribute('data-route-end-lng') || '';

            const selectedDays = parseJSON(trigger.getAttribute('data-route-days') || '[]', []);
            document.querySelectorAll('.edit-day').forEach((checkbox) => {
                checkbox.checked = selectedDays.includes(Number(checkbox.value));
            });

            if (editEditor) {
                const routeCoords = parseJSON(trigger.getAttribute('data-route-coords') || '[]', []);
                editEditor.setPoints(routeCoords);
            }
        });

        editRouteModal.addEventListener('shown.bs.modal', () => {
            if (editEditor) {
                editEditor.invalidate();
            }
        });
    }

    const overviewMapContainer = document.getElementById('routesOverviewMap');
    const overviewDataNode = document.getElementById('routesMapData');

    if (overviewMapContainer && overviewDataNode && typeof L !== 'undefined') {
        const overviewMap = L.map('routesOverviewMap').setView(QUETZALTENANGO_CENTER, QUETZALTENANGO_ZOOM);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(overviewMap);

        const routesData = parseJSON(overviewDataNode.textContent || '[]', []);
        const allPoints = [];

        routesData.forEach((route, routeIndex) => {
            const points = Array.isArray(route.coordenadas) ? route.coordenadas : [];

            if (points.length === 0) {
                return;
            }

            points.forEach((point, pointIndex) => {
                allPoints.push([point.lat, point.lng]);

                L.circleMarker([point.lat, point.lng], {
                    radius: 5,
                    color: '#198754',
                    weight: 2,
                    fillOpacity: 0.8,
                })
                    .addTo(overviewMap)
                    .bindPopup(`${route.nombre} - Parada ${pointIndex + 1}`);
            });

            if (points.length > 1) {
                L.polyline(points, {
                    color: ['#0d6efd', '#dc3545', '#198754', '#6f42c1'][routeIndex % 4],
                    weight: 4,
                    opacity: 0.85,
                })
                    .addTo(overviewMap)
                    .bindPopup(route.nombre || 'Ruta');
            }
        });

        if (allPoints.length > 0) {
            overviewMap.fitBounds(L.latLngBounds(allPoints), { padding: [25, 25] });
        }
    }
});
