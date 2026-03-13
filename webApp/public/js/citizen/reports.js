document.addEventListener("DOMContentLoaded", function () {
    const reportModal = document.getElementById("staticBackdrop");
    const detailModal = document.getElementById("viewReportModal");
    const mapContainer = document.getElementById("reportMap");
    const addressInput = document.getElementById("reportAddressSearch");
    const searchBtn = document.getElementById("reportSearchBtn");
    const myLocationBtn = document.getElementById("reportMyLocationBtn");
    const messageEl = document.getElementById("reportMapMessage");
    const latInput = document.getElementById("latitud");
    const lngInput = document.getElementById("longitud");
    const locationInput = document.querySelector('input[name="ubicacion"]');

    if (detailModal) {
        const idEl = document.getElementById("modalReportId");
        const fechaEl = document.getElementById("modalReportFecha");
        const ubicacionEl = document.getElementById("modalReportUbicacion");
        const descripcionEl = document.getElementById("modalReportDescripcion");
        const tamanoEl = document.getElementById("modalReportTamano");
        const estadoEl = document.getElementById("modalReportEstado");
        const fotoEl = document.getElementById("modalReportFoto");
        const nombreEl = document.getElementById("modalReportNombre");
        const telefonoEl = document.getElementById("modalReportTelefono");
        const emailEl = document.getElementById("modalReportEmail");
        const noFoto = "https://via.placeholder.com/400x300?text=Sin+foto";

        detailModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;
            if (!button) {
                return;
            }

            const reportId = button.getAttribute("data-report-id") || "-";
            const reportFecha =
                button.getAttribute("data-report-fecha") || "Sin fecha";
            const reportUbicacion =
                button.getAttribute("data-report-ubicacion") || "Sin ubicación";
            const reportDescripcion =
                button.getAttribute("data-report-descripcion") || "Sin descripción";
            const reportTamano =
                button.getAttribute("data-report-tamano") || "Sin tamaño";
            const reportTamanoBadge =
                button.getAttribute("data-report-tamano-badge") || "bg-secondary";
            const reportEstado =
                button.getAttribute("data-report-estado") || "Sin estado";
            const reportEstadoBadge =
                button.getAttribute("data-report-estado-badge") || "bg-secondary";
            const reportFoto = button.getAttribute("data-report-foto") || "";
            const reportNombre =
                button.getAttribute("data-report-nombre") || "Sin nombre";
            const reportTelefono =
                button.getAttribute("data-report-telefono") || "Sin teléfono";
            const reportEmail =
                button.getAttribute("data-report-email") || "Sin email";

            if (idEl) idEl.textContent = reportId;
            if (fechaEl) fechaEl.textContent = reportFecha;
            if (ubicacionEl) ubicacionEl.textContent = reportUbicacion;
            if (descripcionEl) descripcionEl.textContent = reportDescripcion;
            if (nombreEl) nombreEl.textContent = reportNombre;
            if (telefonoEl) telefonoEl.textContent = reportTelefono;
            if (emailEl) emailEl.textContent = reportEmail;

            if (tamanoEl) {
                tamanoEl.textContent = reportTamano;
                tamanoEl.className = "badge " + reportTamanoBadge;
            }

            if (estadoEl) {
                estadoEl.textContent = reportEstado;
                estadoEl.className = "badge " + reportEstadoBadge;
            }

            if (fotoEl) {
                fotoEl.src = reportFoto || noFoto;
            }
        });
    }

    if (
        !reportModal ||
        !mapContainer ||
        typeof L === "undefined" ||
        !latInput ||
        !lngInput
    ) {
        return;
    }

    const defaultCenter = [14.6349, -90.5069];
    let map = null;
    let pickedMarker = null;
    let gpsMarker = null;
    let gpsCircle = null;

    function setMessage(message, isError) {
        if (!messageEl) {
            return;
        }

        messageEl.textContent = message || "";
        messageEl.classList.toggle("text-danger", Boolean(isError));
        messageEl.classList.toggle("text-muted", !isError);
    }

    function setCoordinates(lat, lng, fromAddress) {
        latInput.value = Number(lat).toFixed(6);
        lngInput.value = Number(lng).toFixed(6);

        if (pickedMarker) {
            pickedMarker.setLatLng([lat, lng]);
        } else {
            pickedMarker = L.marker([lat, lng], { draggable: true }).addTo(map);
            pickedMarker.on("dragend", function (event) {
                const position = event.target.getLatLng();
                setCoordinates(position.lat, position.lng, false);
                reverseGeocode(position.lat, position.lng);
            });
        }

        if (!fromAddress) {
            setMessage("Punto seleccionado en el mapa.");
        }
    }

    async function reverseGeocode(lat, lng) {
        try {
            const url =
                "https://nominatim.openstreetmap.org/reverse?format=json&lat=" +
                encodeURIComponent(lat) +
                "&lon=" +
                encodeURIComponent(lng);

            const response = await fetch(url, {
                headers: { Accept: "application/json" },
            });
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            if (locationInput && data && data.display_name) {
                locationInput.value = data.display_name;
            }
        } catch (_error) {}
    }

    async function searchAddress() {
        const query = (addressInput?.value || "").trim();

        if (!query) {
            setMessage("Ingresa una dirección para buscar.", true);
            return;
        }

        setMessage("Buscando dirección...");

        try {
            const url =
                "https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" +
                encodeURIComponent(query);
            const response = await fetch(url, {
                headers: { Accept: "application/json" },
            });

            if (!response.ok) {
                throw new Error("No se pudo buscar la dirección");
            }

            const results = await response.json();
            if (!Array.isArray(results) || results.length === 0) {
                setMessage("No se encontró la dirección.", true);
                return;
            }

            const result = results[0];
            const lat = Number(result.lat);
            const lng = Number(result.lon);

            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                setMessage(
                    "La dirección encontrada no tiene coordenadas válidas.",
                    true,
                );
                return;
            }

            map.setView([lat, lng], 16);
            setCoordinates(lat, lng, true);
            if (locationInput) {
                locationInput.value = result.display_name || query;
            }
            setMessage("Dirección encontrada.");
        } catch (_error) {
            setMessage("Error al buscar la dirección. Intenta de nuevo.", true);
        }
    }

    function useDeviceLocation() {
        if (!navigator.geolocation) {
            setMessage("Tu navegador no soporta geolocalización.", true);
            return;
        }

        setMessage("Obteniendo ubicación del dispositivo...");

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                if (gpsMarker) {
                    map.removeLayer(gpsMarker);
                }
                if (gpsCircle) {
                    map.removeLayer(gpsCircle);
                }

                gpsMarker = L.circleMarker([lat, lng], {
                    radius: 6,
                    color: "#1d4ed8",
                    fillColor: "#3b82f6",
                    fillOpacity: 1,
                }).addTo(map);

                gpsCircle = L.circle([lat, lng], {
                    radius: Math.max(accuracy, 20),
                    color: "#1d4ed8",
                    fillColor: "#3b82f6",
                    fillOpacity: 0.15,
                    weight: 1,
                }).addTo(map);

                map.setView([lat, lng], 16);
                setCoordinates(lat, lng, true);
                reverseGeocode(lat, lng);
                setMessage("Ubicación detectada.");
            },
            function () {
                setMessage("No fue posible obtener tu ubicación.", true);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000,
            },
        );
    }

    function initMap() {
        if (map) {
            setTimeout(function () {
                map.invalidateSize();
            }, 100);
            return;
        }

        map = L.map("reportMap").setView(defaultCenter, 12);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: "&copy; OpenStreetMap contributors",
        }).addTo(map);

        map.on("click", function (event) {
            setCoordinates(event.latlng.lat, event.latlng.lng, false);
            reverseGeocode(event.latlng.lat, event.latlng.lng);
        });

        const initialLat = Number(latInput.value);
        const initialLng = Number(lngInput.value);
        if (
            !Number.isNaN(initialLat) &&
            !Number.isNaN(initialLng) &&
            latInput.value &&
            lngInput.value
        ) {
            map.setView([initialLat, initialLng], 15);
            setCoordinates(initialLat, initialLng, true);
        }

        setTimeout(function () {
            map.invalidateSize();
        }, 200);
    }

    reportModal.addEventListener("shown.bs.modal", function () {
        initMap();
    });

    searchBtn?.addEventListener("click", function () {
        searchAddress();
    });

    addressInput?.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            searchAddress();
        }
    });

    myLocationBtn?.addEventListener("click", function () {
        useDeviceLocation();
    });
});
