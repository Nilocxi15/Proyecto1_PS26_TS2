// Filtros y paginación para tabla de rutas
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const dayFilter = document.getElementById('dayFilter');
    const wasteTypeFilter = document.getElementById('wasteTypeFilter');
    const scheduleTable = document.getElementById('scheduleTable');
    const emptyState = document.getElementById('emptyState');
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const pageInfo = document.getElementById('pageInfo');

    if (!scheduleTable || !searchInput || !dayFilter || !wasteTypeFilter || !emptyState) {
        return;
    }

    const rows = Array.from(scheduleTable.querySelectorAll('tbody tr'));
    const rowsPerPage = 6;
    let currentPage = 1;

    function normalizeText(value) {
        return (value || '')
            .toString()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function getFilteredRows() {
        const query = normalizeText(searchInput.value);
        const selectedDay = normalizeText(dayFilter.value);
        const selectedWasteType = normalizeText(wasteTypeFilter.value);

        return rows.filter(function (row) {
            const route = normalizeText(row.cells[0]?.textContent);
            const wasteType = normalizeText(row.cells[1]?.textContent);
            const startTime = normalizeText(row.cells[2]?.textContent);
            const endTime = normalizeText(row.cells[3]?.textContent);
            const day = normalizeText(row.cells[4]?.textContent);

            const textMatches =
                query === '' ||
                route.includes(query) ||
                wasteType.includes(query) ||
                startTime.includes(query) ||
                endTime.includes(query) ||
                day.includes(query);

            const dayMatches = selectedDay === '' || day === selectedDay;
            const wasteTypeMatches = selectedWasteType === '' || wasteType === selectedWasteType;

            return textMatches && dayMatches && wasteTypeMatches;
        });
    }

    function applyFilters() {
        const filteredRows = getFilteredRows();
        const totalRows = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const visibleRows = filteredRows.slice(start, end);

        rows.forEach(function (row) {
            row.classList.add('d-none');
        });

        visibleRows.forEach(function (row) {
            row.classList.remove('d-none');
        });

        emptyState.classList.toggle('d-none', totalRows > 0);

        if (pageInfo) {
            pageInfo.textContent = 'Pagina ' + currentPage + ' de ' + totalPages;
        }

        if (prevPageBtn) {
            prevPageBtn.disabled = currentPage <= 1 || totalRows === 0;
        }

        if (nextPageBtn) {
            nextPageBtn.disabled = currentPage >= totalPages || totalRows === 0;
        }

        const paginationContainer = document.getElementById('tablePagination');
        if (paginationContainer) {
            paginationContainer.classList.toggle('d-none', totalRows === 0);
        }
    }

    function handleFilterChange() {
        currentPage = 1;
        applyFilters();
    }

    searchInput.addEventListener('input', handleFilterChange);
    dayFilter.addEventListener('change', handleFilterChange);
    wasteTypeFilter.addEventListener('change', handleFilterChange);

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage -= 1;
                applyFilters();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', function () {
            const totalPages = Math.max(1, Math.ceil(getFilteredRows().length / rowsPerPage));
            if (currentPage < totalPages) {
                currentPage += 1;
                applyFilters();
            }
        });
    }

    applyFilters();

// Mapa de rutas
    const mapElement = document.getElementById('routesMap');
    const mapAddressInput = document.getElementById('mapAddressInput');
    const mapSearchBtn = document.getElementById('mapSearchBtn');
    const myLocationBtn = document.getElementById('myLocationBtn');
    const mapSearchMessage = document.getElementById('mapSearchMessage');
    const routesData = Array.isArray(window.citizenRoutes) ? window.citizenRoutes : [];

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const map = L.map('routesMap');
    let locationMarker = null;
    let locationCircle = null;
    let searchMarker = null;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const bounds = [];
    const colors = ['#0b7285', '#2f9e44', '#5f3dc4', '#c92a2a', '#e67700'];

    routesData.forEach(function (route, index) {
        const points = Array.isArray(route.puntos)
            ? route.puntos.filter(function (point) {
                  return Array.isArray(point) && point.length === 2;
              })
            : [];

        if (points.length < 2) {
            return;
        }

        points.forEach(function (point) {
            bounds.push(point);
        });

        const color = colors[index % colors.length];

        L.polyline(points, {
            color: color,
            weight: 4,
            opacity: 0.85,
        })
            .addTo(map)
            .bindPopup('<strong>' + (route.nombre || 'Ruta') + '</strong><br>Residuo: ' + (route.tipo_residuo || 'Sin definir'));

        L.circleMarker(points[0], {
            radius: 6,
            color: color,
            fillColor: color,
            fillOpacity: 1,
        })
            .addTo(map)
            .bindTooltip('Inicio: ' + (route.nombre || 'Ruta'));

        L.circleMarker(points[points.length - 1], {
            radius: 6,
            color: color,
            fillColor: '#ffffff',
            fillOpacity: 1,
            weight: 2,
        })
            .addTo(map)
            .bindTooltip('Fin: ' + (route.nombre || 'Ruta'));
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [24, 24] });
    } else {
        map.setView([14.6349, -90.5069], 12);
    }
    
    setTimeout(function () {
        map.invalidateSize();
    }, 200);

    function setMapMessage(message, isError) {
        if (!mapSearchMessage) {
            return;
        }

        mapSearchMessage.textContent = message || '';
        mapSearchMessage.classList.toggle('text-danger', Boolean(isError));
        mapSearchMessage.classList.toggle('text-muted', !isError);
    }

    function showDeviceLocation() {
        if (!navigator.geolocation) {
            setMapMessage('Tu navegador no soporta geolocalizacion.', true);
            return;
        }

        setMapMessage('Obteniendo tu ubicacion...');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                if (locationMarker) {
                    map.removeLayer(locationMarker);
                }
                if (locationCircle) {
                    map.removeLayer(locationCircle);
                }

                locationMarker = L.marker([lat, lng])
                    .addTo(map)
                    .bindPopup('Estas aqui')
                    .openPopup();

                locationCircle = L.circle([lat, lng], {
                    radius: Math.max(accuracy, 20),
                    color: '#1d4ed8',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.18,
                    weight: 1,
                }).addTo(map);

                map.setView([lat, lng], Math.max(map.getZoom(), 14));
                setMapMessage('Ubicacion del dispositivo detectada.');
            },
            function () {
                setMapMessage('No fue posible obtener la ubicacion del dispositivo.', true);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000,
            }
        );
    }

    async function searchAddress() {
        const query = (mapAddressInput?.value || '').trim();

        if (query === '') {
            setMapMessage('Escribe una direccion para buscar.', true);
            return;
        }

        setMapMessage('Buscando direccion...');

        try {
            const url =
                'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' +
                encodeURIComponent(query);

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Respuesta invalida del servicio de mapas');
            }

            const results = await response.json();

            if (!Array.isArray(results) || results.length === 0) {
                setMapMessage('No se encontro la direccion solicitada.', true);
                return;
            }

            const found = results[0];
            const lat = Number(found.lat);
            const lon = Number(found.lon);

            if (Number.isNaN(lat) || Number.isNaN(lon)) {
                setMapMessage('No se pudo interpretar la ubicacion encontrada.', true);
                return;
            }

            if (searchMarker) {
                map.removeLayer(searchMarker);
            }

            searchMarker = L.marker([lat, lon])
                .addTo(map)
                .bindPopup(found.display_name || query)
                .openPopup();

            map.setView([lat, lon], 15);
            setMapMessage('Direccion encontrada.');
        } catch (_error) {
            setMapMessage('Error al buscar la direccion. Intenta nuevamente.', true);
        }
    }

    if (mapSearchBtn) {
        mapSearchBtn.addEventListener('click', function () {
            searchAddress();
        });
    }

    if (mapAddressInput) {
        mapAddressInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchAddress();
            }
        });
    }

    if (myLocationBtn) {
        myLocationBtn.addEventListener('click', function () {
            showDeviceLocation();
        });
    }

    showDeviceLocation();

// Mapa de puntos verdes    
    const greenMapElement = document.getElementById('greenPointsMap');
    const greenMapAddressInput = document.getElementById('greenMapAddressInput');
    const greenMapSearchBtn = document.getElementById('greenMapSearchBtn');
    const greenMyLocationBtn = document.getElementById('greenMyLocationBtn');
    const greenMapSearchMessage = document.getElementById('greenMapSearchMessage');
    const greenPointsData = Array.isArray(window.greenPoints) ? window.greenPoints : [];

    if (!greenMapElement || typeof L === 'undefined') {
        return;
    }

    const greenMap = L.map('greenPointsMap');
    let greenLocationMarker = null;
    let greenLocationCircle = null;
    let greenSearchMarker = null;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(greenMap);

    const greenBounds = [];

    greenPointsData.forEach(function (punto) {
        const lat = Number(punto.latitud);
        const lng = Number(punto.longitud);

        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return;
        }

        greenBounds.push([lat, lng]);

        L.marker([lat, lng])
            .addTo(greenMap)
            .bindPopup(
                '<strong>' +
                    (punto.nombre || 'Punto verde') +
                    '</strong><br>' +
                    (punto.direccion || 'Sin direccion') +
                    '<br>Horario: ' +
                    (punto.horario || 'Sin definir')
            );
    });

    if (greenBounds.length > 0) {
        greenMap.fitBounds(greenBounds, { padding: [24, 24] });
    } else {
        greenMap.setView([14.6349, -90.5069], 12);
    }

    setTimeout(function () {
        greenMap.invalidateSize();
    }, 250);

    function setGreenMapMessage(message, isError) {
        if (!greenMapSearchMessage) {
            return;
        }

        greenMapSearchMessage.textContent = message || '';
        greenMapSearchMessage.classList.toggle('text-danger', Boolean(isError));
        greenMapSearchMessage.classList.toggle('text-muted', !isError);
    }

    function showGreenMapDeviceLocation() {
        if (!navigator.geolocation) {
            setGreenMapMessage('Tu navegador no soporta geolocalizacion.', true);
            return;
        }

        setGreenMapMessage('Obteniendo tu ubicacion...');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                if (greenLocationMarker) {
                    greenMap.removeLayer(greenLocationMarker);
                }
                if (greenLocationCircle) {
                    greenMap.removeLayer(greenLocationCircle);
                }

                greenLocationMarker = L.marker([lat, lng])
                    .addTo(greenMap)
                    .bindPopup('Estas aqui')
                    .openPopup();

                greenLocationCircle = L.circle([lat, lng], {
                    radius: Math.max(accuracy, 20),
                    color: '#1d4ed8',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.18,
                    weight: 1,
                }).addTo(greenMap);

                greenMap.setView([lat, lng], Math.max(greenMap.getZoom(), 14));
                setGreenMapMessage('Ubicacion del dispositivo detectada.');
            },
            function () {
                setGreenMapMessage('No fue posible obtener la ubicacion del dispositivo.', true);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000,
            }
        );
    }

    async function searchGreenMapAddress() {
        const query = (greenMapAddressInput?.value || '').trim();

        if (query === '') {
            setGreenMapMessage('Escribe una direccion para buscar.', true);
            return;
        }

        setGreenMapMessage('Buscando direccion...');

        try {
            const url =
                'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' +
                encodeURIComponent(query);

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Respuesta invalida del servicio de mapas');
            }

            const results = await response.json();

            if (!Array.isArray(results) || results.length === 0) {
                setGreenMapMessage('No se encontro la direccion solicitada.', true);
                return;
            }

            const found = results[0];
            const lat = Number(found.lat);
            const lon = Number(found.lon);

            if (Number.isNaN(lat) || Number.isNaN(lon)) {
                setGreenMapMessage('No se pudo interpretar la ubicacion encontrada.', true);
                return;
            }

            if (greenSearchMarker) {
                greenMap.removeLayer(greenSearchMarker);
            }

            greenSearchMarker = L.marker([lat, lon])
                .addTo(greenMap)
                .bindPopup(found.display_name || query)
                .openPopup();

            greenMap.setView([lat, lon], 15);
            setGreenMapMessage('Direccion encontrada.');
        } catch (_error) {
            setGreenMapMessage('Error al buscar la direccion. Intenta nuevamente.', true);
        }
    }

    if (greenMapSearchBtn) {
        greenMapSearchBtn.addEventListener('click', function () {
            searchGreenMapAddress();
        });
    }

    if (greenMapAddressInput) {
        greenMapAddressInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchGreenMapAddress();
            }
        });
    }

    if (greenMyLocationBtn) {
        greenMyLocationBtn.addEventListener('click', function () {
            showGreenMapDeviceLocation();
        });
    }

    showGreenMapDeviceLocation();
});
