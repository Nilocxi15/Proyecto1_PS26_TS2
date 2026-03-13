(function () {
    const charts = window.reportsData || {};

    function renderChart(id, config) {
        const el = document.getElementById(id);
        if (!el) return;
        new Chart(el, config);
    }

    renderChart('chartRecoleccionMensual', {
        type: 'line',
        data: {
            labels: charts.recoleccionMensual.labels,
            datasets: [{
                label: 'Toneladas',
                data: charts.recoleccionMensual.data,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.15)',
                fill: true,
                tension: 0.25,
            }]
        }
    });

    renderChart('chartRecoleccionAnual', {
        type: 'bar',
        data: {
            labels: charts.recoleccionAnual.labels,
            datasets: [{
                label: 'Toneladas',
                data: charts.recoleccionAnual.data,
                backgroundColor: '#0d6efd',
            }]
        }
    });

    renderChart('chartMateriales', {
        type: 'doughnut',
        data: {
            labels: charts.materiales.labels,
            datasets: [{
                label: 'Kg por material',
                data: charts.materiales.data,
                backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545', '#20c997', '#6f42c1', '#fd7e14']
            }]
        }
    });

    renderChart('chartTendenciaCiudadana', {
        type: 'line',
        data: {
            labels: charts.tendenciaCiudadana.labels,
            datasets: [{
                label: 'Kg reciclados',
                data: charts.tendenciaCiudadana.kg,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                yAxisID: 'y',
                tension: 0.25,
            }, {
                label: 'Ciudadanos activos',
                data: charts.tendenciaCiudadana.ciudadanos,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                yAxisID: 'y1',
                tension: 0.25,
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, position: 'left' },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
            }
        }
    });

    renderChart('chartDenunciasEstado', {
        type: 'pie',
        data: {
            labels: charts.denunciasEstado.labels,
            datasets: [{
                label: 'Denuncias',
                data: charts.denunciasEstado.data,
                backgroundColor: ['#ffc107', '#0d6efd', '#198754', '#dc3545', '#6c757d', '#20c997']
            }]
        }
    });
})();
