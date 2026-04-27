document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("login-form");
    const emailField = document.querySelector("input[name='email']");
    const passwordField = document.querySelector("input[name='password']");
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
});
