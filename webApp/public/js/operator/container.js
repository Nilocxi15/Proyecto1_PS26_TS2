// Estilos para los combobox y el porcentaje de llenado de los contenedores
function getFillRange(fillValue) {
	if (fillValue <= 25) {
		return 'low';
	}

	if (fillValue <= 60) {
		return 'medium';
	}

	if (fillValue <= 85) {
		return 'high';
	}

	return 'critical';
}

function normalizeText(value) {
	return String(value || '')
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.trim()
		.toLowerCase();
}

function applyContainerFilters() {
	const materialFilter = document.getElementById('materialFilter');
	const fillLevelFilter = document.getElementById('fillLevelFilter');
	const rows = document.querySelectorAll('#containersTable tbody tr');
	const emptyState = document.getElementById('emptyState');

	if (!materialFilter || !fillLevelFilter || !emptyState) {
		return;
	}

	const materialSelected = normalizeText(materialFilter.value);
	const fillLevelSelected = fillLevelFilter.value;

	let visibleRows = 0;

	rows.forEach((row) => {
		const materialCell = row.children[1];
		const fillCell = row.querySelector('td[data-fill]');
		const material = normalizeText(materialCell ? materialCell.textContent : '');
		const fillValue = Number(fillCell ? fillCell.dataset.fill : 0);
		const fillRange = getFillRange(fillValue);

		const matchesMaterial = !materialSelected || material === materialSelected;
		const matchesFillLevel = !fillLevelSelected || fillRange === fillLevelSelected;
		const shouldShow = matchesMaterial && matchesFillLevel;

		row.style.display = shouldShow ? '' : 'none';

		if (shouldShow) {
			visibleRows += 1;
		}
	});

	emptyState.classList.toggle('d-none', visibleRows > 0);
}

window.addEventListener('DOMContentLoaded', () => {
	const materialFilter = document.getElementById('materialFilter');
	const fillLevelFilter = document.getElementById('fillLevelFilter');

	if (!materialFilter || !fillLevelFilter) {
		return;
	}

	materialFilter.addEventListener('change', applyContainerFilters);
	fillLevelFilter.addEventListener('change', applyContainerFilters);
	applyContainerFilters();
});
