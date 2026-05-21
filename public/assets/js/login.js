document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("login-form");
    const emailField = document.querySelector("input[name='email']");
    const passwordField = document.querySelector("input[name='password']");
    const passwordToggle = document.querySelector(".password-toggle");
    const messageContainer = document.getElementById("message");

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // [emailField, passwordField].forEach(field => {
    //     field.addEventListener("input", () => {
    //         messageContainer.innerHTML = "";
    //     });
    // });

    loginForm.addEventListener("submit", (e) => {
        const emailValid = emailRegex.test(emailField.value);
        if (!emailValid) {
            e.preventDefault();
            messageContainer.innerHTML = `<p class="error-message">Adresse email invalide.</p>`;
        }
    });

    passwordToggle.addEventListener("click", () => {
        const isPasswordVisible = passwordField.type === "text";
        const icon = passwordToggle.querySelector("i");

        passwordField.type = isPasswordVisible ? "password" : "text";
        passwordToggle.setAttribute("aria-pressed", String(!isPasswordVisible));
        passwordToggle.setAttribute(
            "aria-label",
            isPasswordVisible ? "Afficher le mot de passe" : "Masquer le mot de passe"
        );
        icon.classList.toggle("bx-show", isPasswordVisible);
        icon.classList.toggle("bx-hide", !isPasswordVisible);
    });
});
