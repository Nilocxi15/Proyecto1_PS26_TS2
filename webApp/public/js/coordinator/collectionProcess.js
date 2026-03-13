window.addEventListener('DOMContentLoaded', function () {
    const incidentModal = document.getElementById('incidentModal');
    const incidentForm = document.getElementById('incidentForm');
    const routeLabel = document.getElementById('incidentRouteLabel');

    if (!incidentModal || !incidentForm || !routeLabel) {
        return;
    }

    incidentModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;

        if (!trigger) {
            return;
        }

        incidentForm.action = trigger.getAttribute('data-incident-action') || '#';
        routeLabel.textContent = trigger.getAttribute('data-incident-route') || '--';
    });
});
