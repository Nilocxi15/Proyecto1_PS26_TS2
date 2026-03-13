function normalizeText(value) {
    return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase();
}

function parseCoordinate(value) {
    const coordinate = Number(value);
    return Number.isFinite(coordinate) ? coordinate : null;
}

function toPlaceholderPhoto(url) {
    return url || 'https://via.placeholder.com/640x360?text=Sin+foto';
}

const INCIDENT_STATUS_FLOW = [
    'recibida',
    'en_revision',
    'asignada',
    'en_atencion',
    'atendida',
    'cerrada',
];

function applyIncidentsFilters() {
    const searchInput = document.getElementById('incidentSearch');
    const statusFilter = document.getElementById('statusFilter');
    const sizeFilter = document.getElementById('sizeFilter');
    const tableRows = document.querySelectorAll('#incidentsTable tbody tr');
    const emptyState = document.getElementById('incidentsEmptyState');

    if (!searchInput || !statusFilter || !sizeFilter || !emptyState) {
        return;
    }

    const searchTerm = normalizeText(searchInput.value);
    const selectedStatus = normalizeText(statusFilter.value);
    const selectedSize = normalizeText(sizeFilter.value);

    let visibleRows = 0;

    tableRows.forEach((row) => {
        const rowText = normalizeText(row.textContent);
        const rowStatus = normalizeText(row.dataset.status);
        const rowSize = normalizeText(row.dataset.size);

        const matchesSearch = !searchTerm || rowText.includes(searchTerm);
        const matchesStatus = !selectedStatus || rowStatus === selectedStatus;
        const matchesSize = !selectedSize || rowSize === selectedSize;
        const shouldShow = matchesSearch && matchesStatus && matchesSize;

        row.style.display = shouldShow ? '' : 'none';

        if (shouldShow) {
            visibleRows += 1;
        }
    });

    emptyState.classList.toggle('d-none', visibleRows > 0);
}

window.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('incidentSearch');
    const statusFilter = document.getElementById('statusFilter');
    const sizeFilter = document.getElementById('sizeFilter');

    if (!searchInput || !statusFilter || !sizeFilter) {
        return;
    }

    searchInput.addEventListener('input', applyIncidentsFilters);
    statusFilter.addEventListener('change', applyIncidentsFilters);
    sizeFilter.addEventListener('change', applyIncidentsFilters);

    applyIncidentsFilters();

    const incidentModal = document.getElementById('incidentDetailModal');
    const modalIncidentId = document.getElementById('modalIncidentId');
    const modalIncidentName = document.getElementById('modalIncidentName');
    const modalIncidentPhone = document.getElementById('modalIncidentPhone');
    const modalIncidentEmail = document.getElementById('modalIncidentEmail');
    const modalIncidentDate = document.getElementById('modalIncidentDate');
    const modalIncidentLocation = document.getElementById('modalIncidentLocation');
    const modalIncidentSize = document.getElementById('modalIncidentSize');
    const modalIncidentCurrentStatus = document.getElementById('modalIncidentCurrentStatus');
    const modalIncidentDescription = document.getElementById('modalIncidentDescription');
    const modalIncidentPhoto = document.getElementById('modalIncidentPhoto');
    const modalIncidentStatusSelect = document.getElementById('modalIncidentStatusSelect');
    const incidentStatusForm = document.getElementById('incidentStatusForm');
    const incidentMapMessage = document.getElementById('incidentMapMessage');

    let incidentMap = null;
    let incidentMarker = null;

    function ensureMap() {
        if (incidentMap || typeof L === 'undefined') {
            return;
        }

        incidentMap = L.map('incidentMap').setView([14.6349, -90.5069], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(incidentMap);
    }

    function updateMap(lat, lng, locationLabel) {
        ensureMap();

        if (!incidentMap) {
            return;
        }

        if (lat !== null && lng !== null) {
            incidentMap.setView([lat, lng], 16);

            if (incidentMarker) {
                incidentMarker.setLatLng([lat, lng]);
            } else {
                incidentMarker = L.marker([lat, lng]).addTo(incidentMap);
            }

            incidentMarker.bindPopup(locationLabel || 'Ubicacion de la denuncia').openPopup();
            if (incidentMapMessage) {
                incidentMapMessage.textContent = '';
            }
        } else {
            incidentMap.setView([14.6349, -90.5069], 12);

            if (incidentMarker) {
                incidentMap.removeLayer(incidentMarker);
                incidentMarker = null;
            }

            if (incidentMapMessage) {
                incidentMapMessage.textContent = 'No hay coordenadas disponibles para esta denuncia.';
            }
        }

        setTimeout(() => {
            incidentMap.invalidateSize();
        }, 150);
    }

    if (
        incidentModal &&
        modalIncidentId &&
        modalIncidentName &&
        modalIncidentPhone &&
        modalIncidentEmail &&
        modalIncidentDate &&
        modalIncidentLocation &&
        modalIncidentSize &&
        modalIncidentCurrentStatus &&
        modalIncidentDescription &&
        modalIncidentPhoto &&
        modalIncidentStatusSelect &&
        incidentStatusForm
    ) {
        incidentModal.addEventListener('show.bs.modal', (event) => {
            const triggerButton = event.relatedTarget;

            if (!triggerButton) {
                return;
            }

            const incidentId = triggerButton.getAttribute('data-incident-id') || '';
            const incidentName = triggerButton.getAttribute('data-incident-name') || 'Sin nombre';
            const incidentPhone = triggerButton.getAttribute('data-incident-phone') || 'Sin telefono';
            const incidentEmail = triggerButton.getAttribute('data-incident-email') || 'Sin email';
            const incidentDate = triggerButton.getAttribute('data-incident-date') || 'Sin fecha';
            const incidentLocation = triggerButton.getAttribute('data-incident-location') || 'Sin ubicacion';
            const incidentSize = triggerButton.getAttribute('data-incident-size') || 'Sin definir';
            const incidentStatus = triggerButton.getAttribute('data-incident-status') || 'Sin estado';
            const incidentStatusKey = triggerButton.getAttribute('data-incident-status-key') || '';
            const incidentDescription = triggerButton.getAttribute('data-incident-description') || 'Sin descripcion';
            const incidentPhoto = triggerButton.getAttribute('data-incident-photo') || '';
            const incidentLat = parseCoordinate(triggerButton.getAttribute('data-incident-lat'));
            const incidentLng = parseCoordinate(triggerButton.getAttribute('data-incident-lng'));

            modalIncidentId.textContent = incidentId || '-';
            modalIncidentName.textContent = incidentName;
            modalIncidentPhone.textContent = incidentPhone;
            modalIncidentEmail.textContent = incidentEmail;
            modalIncidentDate.textContent = incidentDate;
            modalIncidentLocation.textContent = incidentLocation;
            modalIncidentSize.textContent = incidentSize;
            modalIncidentCurrentStatus.textContent = incidentStatus;
            modalIncidentDescription.textContent = incidentDescription;
            modalIncidentPhoto.src = toPlaceholderPhoto(incidentPhoto);

            const currentStatusIndex = INCIDENT_STATUS_FLOW.indexOf(incidentStatusKey);

            Array.from(modalIncidentStatusSelect.options).forEach((option) => {
                const optionIndex = INCIDENT_STATUS_FLOW.indexOf(option.value);
                const shouldDisable = currentStatusIndex !== -1 && optionIndex !== -1 && optionIndex < currentStatusIndex;

                option.disabled = shouldDisable;
                option.hidden = shouldDisable;
            });

            const matchingOption = Array.from(modalIncidentStatusSelect.options).some(
                (option) => option.value === incidentStatusKey,
            );

            if (matchingOption) {
                modalIncidentStatusSelect.value = incidentStatusKey;
            }

            const baseAction = incidentStatusForm.getAttribute('data-base-action') || '';
            incidentStatusForm.action = `${baseAction}/${incidentId}/status`;

            updateMap(incidentLat, incidentLng, incidentLocation);
        });

        incidentModal.addEventListener('shown.bs.modal', () => {
            if (incidentMap) {
                incidentMap.invalidateSize();
            }
        });
    }
});
