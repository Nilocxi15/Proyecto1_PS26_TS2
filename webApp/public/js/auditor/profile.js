document.addEventListener("DOMContentLoaded", function () {
    const changePhoneForm = document.getElementById("changePhoneForm");

    if (!changePhoneForm) {
        return;
    }

    changePhoneForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const messageDiv = document.getElementById("phoneMessage");
        const submitButton = changePhoneForm.querySelector(
            'button[type="submit"]',
        );
        const phoneInput = document.getElementById("newPhone");
        const phoneField = document.getElementById("phone");
        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]',
        )?.content;
        const changePhoneUrl = changePhoneForm.dataset.changePhoneUrl;

        if (
            !messageDiv ||
            !submitButton ||
            !phoneInput ||
            !csrfToken ||
            !changePhoneUrl
        ) {
            return;
        }

        messageDiv.style.display = "none";
        messageDiv.className = "alert";
        submitButton.disabled = true;

        fetch(changePhoneUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify({ telefono: phoneInput.value }),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function (result) {
                const status = result.status;
                const data = result.data;

                messageDiv.style.display = "block";

                if (status === 200 && data.success) {
                    messageDiv.classList.add("alert-success");
                    messageDiv.textContent = data.message;

                    if (phoneField) {
                        phoneField.value = data.telefono;
                    }

                    phoneInput.value = "";
                    return;
                }

                messageDiv.classList.add("alert-danger");
                messageDiv.textContent = data.errors
                    ? Object.values(data.errors).flat()[0]
                    : data.message || "Error al actualizar el telefono.";
            })
            .catch(function () {
                messageDiv.style.display = "block";
                messageDiv.classList.add("alert-danger");
                messageDiv.textContent = "Error de conexion. Intenta de nuevo.";
            })
            .finally(function () {
                submitButton.disabled = false;
            });
    });
});
