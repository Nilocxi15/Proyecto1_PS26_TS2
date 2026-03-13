(function () {
	const bootstrapDataEl = document.getElementById('system-settings-bootstrap');
	const activeTab = (bootstrapDataEl && bootstrapDataEl.dataset.activeTab) || 'roles';
	const roleBase = (bootstrapDataEl && bootstrapDataEl.dataset.roleBase) || '/admin/system-settings/roles';
	const materialBase = (bootstrapDataEl && bootstrapDataEl.dataset.materialBase) || '/admin/system-settings/materiales';
	const puntoBase = (bootstrapDataEl && bootstrapDataEl.dataset.puntoBase) || '/admin/system-settings/puntos-verdes';
	let puntosData = [];

	try {
		puntosData = JSON.parse((bootstrapDataEl && bootstrapDataEl.dataset.puntos) || '[]');
	} catch (error) {
		puntosData = [];
	}

	function buildActionUrl(base, id) {
		return String(base).replace(/\/+$/, '') + '/' + id;
	}

	function ensureLeaflet(onReady) {
		if (typeof window.L !== 'undefined') {
			onReady();
			return;
		}

		const existingLoader = document.getElementById('leaflet-fallback-script');
		if (existingLoader) {
			existingLoader.addEventListener('load', onReady, { once: true });
			return;
		}

		const script = document.createElement('script');
		script.id = 'leaflet-fallback-script';
		script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
		script.onload = onReady;
		document.head.appendChild(script);
	}

	function openEditRole(id, nombre) {
		document.getElementById('formEditRole').action = buildActionUrl(roleBase, id);
		document.getElementById('editRoleNombre').value = nombre;
		new bootstrap.Modal(document.getElementById('modalEditRole')).show();
	}

	function confirmDeleteRole(id, nombre) {
		document.getElementById('formDeleteRole').action = buildActionUrl(roleBase, id);
		document.getElementById('deleteRoleName').textContent = nombre;
		new bootstrap.Modal(document.getElementById('modalDeleteRole')).show();
	}

	function openEditMaterial(id, nombre) {
		document.getElementById('formEditMaterial').action = buildActionUrl(materialBase, id);
		document.getElementById('editMaterialNombre').value = nombre;
		new bootstrap.Modal(document.getElementById('modalEditMaterial')).show();
	}

	function confirmDeleteMaterial(id, nombre) {
		document.getElementById('formDeleteMaterial').action = buildActionUrl(materialBase, id);
		document.getElementById('deleteMaterialName').textContent = nombre;
		new bootstrap.Modal(document.getElementById('modalDeleteMaterial')).show();
	}

	window.openEditRole = openEditRole;
	window.confirmDeleteRole = confirmDeleteRole;
	window.openEditMaterial = openEditMaterial;
	window.confirmDeleteMaterial = confirmDeleteMaterial;

	// ------------------------------------------------------------------
	// PUNTOS VERDES – Leaflet maps
	// ------------------------------------------------------------------
	const GUATEMALA_CENTER = [14.6349, -90.5069];
	let overviewMap = null;
	let createMap = null;
	let createMarker = null;
	let editMap = null;
	let editMarker = null;

	function initLeafletMaps() {
		const overviewMapEl = document.getElementById('map-overview');
		if (overviewMapEl && !overviewMap) {
			overviewMap = L.map('map-overview').setView(GUATEMALA_CENTER, 12);
			L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 19,
				attribution: '© OpenStreetMap contributors'
			}).addTo(overviewMap);

			const markerCoords = [];

			puntosData.forEach(function (p) {
				const lat = parseFloat(p.lat);
				const lng = parseFloat(p.lng);
				if (isNaN(lat) || isNaN(lng)) {
					return;
				}

				markerCoords.push([lat, lng]);

				L.marker([lat, lng])
					.addTo(overviewMap)
					.bindPopup(
						'<strong>' + p.nombre + '</strong><br>' +
						'Dirección: ' + p.dir + '<br>' +
						'GPS: ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '<br>' +
						'Capacidad: ' + p.cap + ' m³<br>' +
						'Horario: ' + p.horario + '<br>' +
						'Encargado: ' + p.enc
					);
			});

			if (markerCoords.length > 0) {
				overviewMap.fitBounds(markerCoords, { padding: [24, 24] });
			}
		}

		const modalCreatePunto = document.getElementById('modalCreatePunto');
		if (modalCreatePunto && !modalCreatePunto.dataset.mapBound) {
			modalCreatePunto.addEventListener('shown.bs.modal', function () {
				if (!createMap) {
					createMap = L.map('map-create').setView(GUATEMALA_CENTER, 12);
					L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
						maxZoom: 19,
						attribution: '© OpenStreetMap contributors'
					}).addTo(createMap);

					createMap.on('click', function (e) {
						const lat = e.latlng.lat;
						const lng = e.latlng.lng;
						document.getElementById('createLatitud').value = lat.toFixed(7);
						document.getElementById('createLongitud').value = lng.toFixed(7);
						if (createMarker) {
							createMarker.setLatLng(e.latlng);
						} else {
							createMarker = L.marker(e.latlng).addTo(createMap);
						}
					});
				}
				createMap.invalidateSize();
			});

			modalCreatePunto.addEventListener('hidden.bs.modal', function () {
				if (createMarker && createMap) {
					createMap.removeLayer(createMarker);
					createMarker = null;
				}
				document.getElementById('createLatitud').value = '';
				document.getElementById('createLongitud').value = '';
			});

			modalCreatePunto.dataset.mapBound = '1';
		}

		const modalEditPunto = document.getElementById('modalEditPunto');
		if (modalEditPunto && !modalEditPunto.dataset.mapBound) {
			modalEditPunto.addEventListener('shown.bs.modal', function () {
				const lat = parseFloat(document.getElementById('editLatitud').value);
				const lng = parseFloat(document.getElementById('editLongitud').value);
				const center = (!isNaN(lat) && !isNaN(lng)) ? [lat, lng] : GUATEMALA_CENTER;

				if (!editMap) {
					editMap = L.map('map-edit').setView(center, 15);
					L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
						maxZoom: 19,
						attribution: '© OpenStreetMap contributors'
					}).addTo(editMap);

					editMap.on('click', function (e) {
						document.getElementById('editLatitud').value = e.latlng.lat.toFixed(7);
						document.getElementById('editLongitud').value = e.latlng.lng.toFixed(7);
						if (editMarker) {
							editMarker.setLatLng(e.latlng);
						} else {
							editMarker = L.marker(e.latlng).addTo(editMap);
						}
					});
				} else {
					editMap.setView(center, 15);
				}

				if (!isNaN(lat) && !isNaN(lng)) {
					if (editMarker) {
						editMarker.setLatLng([lat, lng]);
					} else {
						editMarker = L.marker([lat, lng]).addTo(editMap);
					}
				}

				editMap.invalidateSize();
			});

			modalEditPunto.dataset.mapBound = '1';
		}

		const settingsTabs = document.getElementById('settingsTabs');
		if (settingsTabs && !settingsTabs.dataset.mapBound) {
			settingsTabs.addEventListener('shown.bs.tab', function (event) {
				if (event.target && event.target.id === 'tab-puntos' && overviewMap) {
					overviewMap.invalidateSize();
				}
			});
			settingsTabs.dataset.mapBound = '1';
		}
	}

	function openEditPunto(dataPunto) {
		document.getElementById('formEditPunto').action = buildActionUrl(puntoBase, dataPunto.id);
		document.getElementById('editPuntoNombre').value = dataPunto.nombre;
		document.getElementById('editPuntoDireccion').value = dataPunto.direccion;
		document.getElementById('editPuntoCapacidad').value = dataPunto.capacidad_m3;
		document.getElementById('editPuntoHorario').value = dataPunto.horario;
		document.getElementById('editLatitud').value = dataPunto.latitud;
		document.getElementById('editLongitud').value = dataPunto.longitud;

		const sel = document.getElementById('editPuntoEncargado');
		sel.value = dataPunto.id_encargado !== null ? dataPunto.id_encargado : '';

		new bootstrap.Modal(document.getElementById('modalEditPunto')).show();
	}

	window.openEditPunto = openEditPunto;

	ensureLeaflet(initLeafletMaps);

	function confirmDeletePunto(id, nombre) {
		document.getElementById('formDeletePunto').action = buildActionUrl(puntoBase, id);
		document.getElementById('deletePuntoName').textContent = nombre;
		new bootstrap.Modal(document.getElementById('modalDeletePunto')).show();
	}

	window.confirmDeletePunto = confirmDeletePunto;

	const filterPuntos = document.getElementById('filterPuntos');
	if (filterPuntos) {
		filterPuntos.addEventListener('input', function () {
			const q = this.value.toLowerCase();
			document.querySelectorAll('#tablePuntosBody tr').forEach(function (row) {
				const text = row.textContent.toLowerCase();
				row.style.display = text.includes(q) ? '' : 'none';
			});
		});
	}

	const tabEl = document.getElementById('tab-' + activeTab);
	if (tabEl) {
		const tab = new bootstrap.Tab(tabEl);
		tab.show();
	}

})();
