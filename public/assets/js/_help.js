import { initModal, initAccordion, initTabs } from './functions.js';

document.addEventListener("DOMContentLoaded", () => {
    initModal({
        buttonId: "help-button",
        modalSelector: ".section-help",
        defaultTab: "notice",
        contentSelector: ".help-content",
        dataAttr: "help"
    });

    initTabs(".notice-help");
    initAccordion(".FAQ-help li");
});