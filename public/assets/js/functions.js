/**
 * @description Initialise une modale avec un bouton pour l'ouvrir, un sélecteur de contenu et des onglets
 * @param {Object} param0 - Les paramètres de la modale
 */
export function initModal({ buttonId, modalSelector, defaultTab, contentSelector, dataAttr }) {
    const button = document.getElementById(buttonId);
    const modal = document.querySelector(modalSelector);
    const contents = document.querySelectorAll(`${contentSelector} > div`);
    const menuItems = document.querySelectorAll(`.nav-${dataAttr} li`);

    if (button) {
        button.addEventListener("click", () => {
            modal.classList.add("active");
            document.body.classList.add("modal-open");
            showContent(defaultTab);
        });
    }

    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.classList.remove("active");
            document.body.classList.remove("modal-open");
        }
    });

    menuItems.forEach(item => {
        item.addEventListener("click", () => {
            const target = item.getAttribute(`data-${dataAttr}`);
            showContent(target);
        });
    });

    function showContent(target) {
        contents.forEach(content => {
            const contentTarget = content.getAttribute("data-content");
            content.classList.toggle("active", contentTarget === target);
        });
    }
}

/**
 * @description Initialise les onglets dans un conteneur donné
 * @param {string} containerSelector - Le sélecteur du conteneur des onglets
 */
export function initTabs(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const tabs = container.querySelectorAll('[data-tab]');
    const contents = container.querySelectorAll('[data-tab-content]');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            contents.forEach(content => {
                const isMatch = content.getAttribute('data-tab-content') === target;
                content.style.display = isMatch ? 'block' : 'none';
            });
        });
    });

    // Premier onglet par défaut
    if (tabs.length > 0) tabs[0].click();
}

/**
 * @description Initialise un accordéon pour afficher/masquer des réponses
 * @param {string} itemSelector - Le sélecteur des éléments de l'accordéon
 * @param {string} answerSelector - Le sélecteur des réponses à afficher/masquer (par défaut ".answer")
 */
export function initAccordion(itemSelector, answerSelector = ".answer") {
    const items = document.querySelectorAll(itemSelector);

    items.forEach(item => {
        item.addEventListener("click", () => {
            const answer = item.querySelector(answerSelector);
            const isOpen = answer.style.display === "block";

            items.forEach(otherItem => {
                const otherAnswer = otherItem.querySelector(answerSelector);
                if (otherAnswer) {
                    otherAnswer.style.display = "none";
                }
            });

            if (!isOpen) {
                answer.style.display = "block";
            }
        });
    });
}

/**
 * @description Gère les actions asynchrones sur les boutons avec un attribut spécifique
 * @param {string} selector - Le sélecteur des boutons à lier
 * @param {string} attr - L'attribut contenant l'URL de l'action
 * @param {function} successCallback - Fonction à appeler en cas de succès (optionnel)
 */
function handleAsyncAction(selector, attr, successCallback) {
    document.querySelectorAll(selector).forEach(button => {
        if (button.classList.contains('async-bound')) return;

        button.classList.add('async-bound');
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            const form = button.closest('form');
            const container = button.closest(`[${attr}]`);
            const url = container ? container.getAttribute(attr) : button.getAttribute(attr);
            if (!url) return;

            let fetchOptions = {
                method: 'POST',
                headers: { 'X-Requested-With': 'FetchRequest' }
            };

            if (form) {
                // Check required fields
                const requiredFields = form.querySelectorAll('[required]');
                let allValid = true;
                requiredFields.forEach(field => {
                    if (!field.value) {
                        field.classList.add('input-error');
                        allValid = false;
                    } else {
                        field.classList.remove('input-error');
                    }
                });
                if (!allValid) {
                    showToast("Veuillez remplir tous les champs obligatoires.", "error");
                    return;
                }

                const formData = new FormData(form);
                fetchOptions.body = formData;
            }

            try {
                const response = await fetch(url, fetchOptions);
                if (!response.ok) throw new Error('Erreur de chargement');
                const html = await response.text();
                if (container && container.parentNode) {
                    container.outerHTML = html;
                    bindModalEvents();
                    if (typeof successCallback === 'function') successCallback();
                }

                showToast("Action réalisée avec succès.", "success");
            } catch (err) {
                console.error(err);
                showToast("Erreur de chargement de la page.", "error");
            }
        });
    });
}

/** 
 * @description Lie les événements des modales, des sliders et des actions asynchrones
 * @param {string} selector - Le sélecteur des boutons pour ouvrir les modales
 * @returns {void}
 */
export function bindModalEvents() {
    document.querySelectorAll('[data-modal]').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
                document.body.classList.add("modal-open");
            }
        });
    });

    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
                document.body.classList.remove("modal-open");
            }
        });
    });

    document.querySelectorAll(".container-slider").forEach(slider => {
        const content = slider.querySelector(".slider-content");
        const slides = slider.querySelectorAll(".slide-content");
        const counter = slider.querySelector(".slide-counter");
        let index = 0;

        // Fix largeur slider
        content.style.width = `${slides.length * 100}%`;
        slides.forEach(slide => {
            slide.style.width = `${100 / slides.length}%`;
        });

        function updateSlider() {
            content.style.transform = `translateX(-${index * (100 / slides.length)}%)`;
            if (counter) {
                counter.textContent = `${index + 1} / ${slides.length}`;
            }
        }

        slider.querySelector(".next").addEventListener("click", () => {
            index = (index + 1) % slides.length;
            updateSlider();
        });

        slider.querySelector(".prev").addEventListener("click", () => {
            index = (index - 1 + slides.length) % slides.length;
            updateSlider();
        });

        updateSlider();
    });

    handleAsyncAction('.update-save', 'data-update');
    handleAsyncAction('.confirm-delete', 'data-delete');
    handleAsyncAction('.confirm-create', 'data-create');
    handleAsyncAction('.confirm-validate', 'data-validate');
}

/**
 * @description Lie les événements de pagination pour charger dynamiquement le contenu
 * @returns {void}
 */
export function bindPagination() {
    const container = document.getElementById('table-container');
    if (!container) return;
    const paginationLinks = container.querySelectorAll('.pagination-link');

    paginationLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const page = link.dataset.page;

            const params = new URLSearchParams(window.location.search);
            params.set('page', page);
            const url = `?${params.toString()}`;

            fetchAndReplace(url, '#table-container', () => {
                bindModalEvents();
                adaptTableSize();
                bindPagination();
            });
        });
    });
}

/**
 * @description Affiche un toast avec un message et un type (success, error, info)
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de toast (success, error, info)
 * @return {void}
 */
export async function fetchAndReplace(url, containerSelector, onSuccess = () => { }) {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    try {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'FetchRequest' }
        });

        if (!response.ok) throw new Error('Erreur de chargement');

        const html = await response.text();
        container.innerHTML = html;

        try {
            onSuccess();
        } catch (e) {
            console.error("Erreur dans onSuccess :", e);
        }
    } catch (err) {
        console.error(err);
        showToast("Erreur de chargement de la page.", "error");
    }
}

/**
 * @description Affiche un toast avec un message et un type (success, error, info)
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de toast (success, error, info)
 * @return {void}
 */
export function adaptTableSize() {
    document.querySelectorAll(".table-gen").forEach(table => {
        table.classList.remove("small", "auto-width");
        const colCount = table.querySelectorAll("thead th").length;
        if (colCount <= 3) {
            table.classList.add("small");
        } else {
            table.classList.add("auto-width");
        }
    });
}

/**
 * @description Affiche un toast avec un message et un type (success, error, info)
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de toast (success, error, info)
 * @return {void}
 */
export function showLoader(target = null) {
    if (!target) {
        target = document.querySelector("[data-loader='local']");
    }

    if (!target) {
        document.getElementById("loader")?.classList.remove("hidden");
        return;
    }

    if (target.querySelector("[data-loader-target]")) return;

    const isTable = target.tagName === "TABLE" || target.querySelector("tbody");
    const loader = document.createElement("div");
    loader.setAttribute("data-loader-target", "true");

    if (isTable) {
        const colCount = target.querySelector("thead tr")?.children.length || 5;
        const rowCount = 1;

        const skeletonTable = document.createElement("table");
        skeletonTable.className = "table-skeleton";

        const tbody = document.createElement("tbody");
        const tr = document.createElement("tr");

        for (let j = 0; j < colCount; j++) {
            const td = document.createElement("td");
            const cell = document.createElement("div");
            cell.className = "skeleton-cell";
            td.appendChild(cell);
            tr.appendChild(td);
        }

        tbody.appendChild(tr);
        skeletonTable.appendChild(tbody);
        loader.appendChild(skeletonTable);
    } else {
        loader.className = "skeleton-loader";
        const line = document.createElement("div");
        line.className = "skeleton skeleton-line short";
        loader.appendChild(line);
    }

    target.innerHTML = "";
    target.appendChild(loader);
}

/**
 * @description Masque le loader en cours d'affichage
 * @param {HTMLElement|null} target - L'élément cible où le loader est affiché, ou null pour le loader global
 */
export function hideLoader(target = null) {
    if (!target) {
        target = document.querySelector("[data-loader='local']");
    }

    if (target) {
        target.querySelector("[data-loader-target]")?.remove();
    } else {
        document.getElementById("loader")?.classList.add("hidden");
    }
}

// Intercepter les requêtes fetch pour afficher un loader
const originalFetch = window.fetch;
window.fetch = async (...args) => {
    let target = args[1]?.loaderTargetSelector
        ? document.querySelector(args[1].loaderTargetSelector)
        : null;

    showLoader(target);

    try {
        const response = await originalFetch(...args);
        return response;
    } catch (error) {
        throw error;
    } finally {
        setTimeout(() => {
            hideLoader(target);
            adaptTableSize();
        }, 100);
    }
};

window.addEventListener("pageshow", (event) => {
    hideLoader();
});

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("form").forEach(form => {
        form.addEventListener("submit", () => {
            showLoader();
        });
    });
});

