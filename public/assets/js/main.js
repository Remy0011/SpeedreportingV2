document.documentElement.style.visibility = 'hidden';

// Initialisation du thème
(function () {
    let savedTheme = localStorage.getItem('theme');

    if (!savedTheme) {
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        savedTheme = systemPrefersDark ? 'dark' : 'light';

        localStorage.setItem('theme', savedTheme);
    }

    document.documentElement.setAttribute('data-theme', savedTheme);
    document.documentElement.style.visibility = 'visible';
})();

document.addEventListener('DOMContentLoaded', () => {
    const themeSwitch = document.getElementById('theme-switch');

    if (themeSwitch) {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        themeSwitch.checked = currentTheme === 'dark';

        themeSwitch.addEventListener('change', () => {
            const isDark = themeSwitch.checked;
            const newTheme = isDark ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            showToast("Le thème a été initialisé en mode " + (themeSwitch.checked ? "sombre" : "clair") + ".", "success", 3000);
        });
    }
});

/**
 * @description Affiche un toast avec un message et un type (success, error, info)
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de toast (success, error, info, easteregg)
 * @param {number} duration - Durée d'affichage du toast en millisecondes
 * @return {void}
 */
function showToast(message, type = "success", duration = 5000) {
    const container = document.getElementById('toast-container');

    const toast = document.createElement("div");
    toast.className = `toast ${type}`;

    // Easter Egg
    let bots = "";
    if (type === "easteregg") {
        bots = `<span class="bot-backer">🤖</span>`;
    }

    toast.innerHTML = `
        ${bots}
        <span>${message}</span>
        <button class="close-btn" onclick="this.parentElement.remove()">
            <i class='bx bx-x'></i>
        </button>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.addEventListener("animationend", (e) => {
            if (e.animationName === "fadeOutToast") {
                toast.remove();
            }
        });
    }, duration - 500);
}
