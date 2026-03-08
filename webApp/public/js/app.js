const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");
const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
const confirm_passwordInput = document.getElementById("confirm-password");

togglePassword.addEventListener("click", function (e) {
    e.preventDefault();

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        togglePassword.textContent = "🙉";
    } else {
        passwordInput.type = "password";
        togglePassword.textContent = "🙈";
    }
});

toggleConfirmPassword.addEventListener("click", function (e) {
    e.preventDefault();

    if (confirm_passwordInput.type === "password") {
        confirm_passwordInput.type = "text";
        toggleConfirmPassword.textContent = "🙉";
    } else {
        confirm_passwordInput.type = "password";
        toggleConfirmPassword.textContent = "🙈";
    }
});