import { initModal } from './functions.js';

document.addEventListener("DOMContentLoaded", () => {
    initModal({
        buttonId: "settings-button",
        modalSelector: ".section-settings",
        defaultTab: "account",
        contentSelector: ".settings-content",
        dataAttr: "settings"
    });

    initModal({
        buttonId: "profile-photo",
        modalSelector: ".section-settings",
        defaultTab: "account",
        contentSelector: ".settings-content",
        dataAttr: "settings"
    });
});
