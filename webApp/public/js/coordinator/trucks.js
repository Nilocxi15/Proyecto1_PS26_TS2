window.addEventListener('DOMContentLoaded', () => {
	if (typeof L === 'undefined') {
		return;
	}

	const mapNode = document.getElementById('assignmentPointsMap');
	const dataNode = document.getElementById('assignmentMapData');

	if (!mapNode || !dataNode) {
		return;
	}

	let mapData = [];

	try {
		mapData = JSON.parse(dataNode.textContent || '[]');
	} catch {
		mapData = [];
	}

	const quetzaltenangoCenter = [14.8347, -91.5186];
	const map = L.map('assignmentPointsMap').setView(quetzaltenangoCenter, 13);

	L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; OpenStreetMap contributors',
	}).addTo(map);

	const bounds = [];

	mapData.forEach((assignment, assignmentIndex) => {
		const points = Array.isArray(assignment.puntos) ? assignment.puntos : [];
		const trajectory = Array.isArray(assignment.trayectoria) ? assignment.trayectoria : [];
		const color = ['#0d6efd', '#198754', '#dc3545', '#fd7e14'][assignmentIndex % 4];

		if (trajectory.length > 1) {
			const line = trajectory.map((point) => [point.lat, point.lng]);

			line.forEach((point) => bounds.push(point));

			L.polyline(line, {
				color,
				weight: 3,
				opacity: 0.85,
			}).addTo(map);
		}

		points.forEach((point, index) => {
			bounds.push([point.lat, point.lng]);

			L.circleMarker([point.lat, point.lng], {
				radius: 5,
				color,
				weight: 2,
				fillOpacity: 0.9,
			})
				.addTo(map)
				.bindPopup(
					`${assignment.ruta}<br>${assignment.camion}<br>Punto #${index + 1}<br>Estimado: ${Number(point.basura_kg || 0).toFixed(2)} kg`,
				);
		});
	});

	if (bounds.length > 0) {
		map.fitBounds(bounds, { padding: [25, 25] });
	}
});
