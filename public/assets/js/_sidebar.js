document.addEventListener("DOMContentLoaded", function () {
    const isMobile = window.innerWidth <= 768;
    localStorage.setItem("sidebarActive", "false");

    initSidebar();
    initDropdownMenu();
    initThemeObserver();

    /**
     * @description Initialise la barre latérale et gère les événements de clic sur le bouton de menu
     * @returns {void}
     */
    function initSidebar() {
        const sidebar = document.querySelector(".sidebar");
        const menuBtn = document.querySelector(".menu-btn");
        const backdrop = document.querySelector(".sidebar-backdrop");

        if (!sidebar || !menuBtn) return;

        let previousIsMobile = isMobile;
        const isSidebarExpanded = localStorage.getItem("sidebarActive") === "true";

        sidebar.classList.toggle("active", isSidebarExpanded);
        updateMainLayout(isSidebarExpanded);

        menuBtn.addEventListener("click", function () {
            const isMobile = window.innerWidth <= 768;

            if (isMobile) {
                const isOpening = !sidebar.classList.contains("show");

                sidebar.classList.toggle("show");
                backdrop.classList.toggle("visible");
                menuBtn.classList.toggle("active");

                if (isOpening) {
                    sidebar.classList.add("active");
                    localStorage.setItem("sidebarActive", "true");
                    updateMainLayout(true);
                } else {
                    sidebar.classList.remove("active");
                    localStorage.setItem("sidebarActive", "false");
                    updateMainLayout(false);
                }
            } else {
                sidebar.classList.add("toggling");
                menuBtn.classList.toggle("active");
                const isExpanded = sidebar.classList.toggle("active");
                updateMainLayout(isExpanded);
                localStorage.setItem("sidebarActive", isExpanded);

                setTimeout(() => {
                    sidebar.classList.remove("toggling");
                }, 100);
            }
        });

        backdrop?.addEventListener("click", () => {
            sidebar.classList.remove("show");
            backdrop.classList.remove("visible");
        });

        window.addEventListener("resize", () => {
            const isMobile = window.innerWidth <= 768;

            if (isMobile !== previousIsMobile) {
                previousIsMobile = isMobile;

                if (isMobile) {
                    sidebar.classList.remove("active", "show");
                    backdrop.classList.remove("visible");
                    menuBtn.classList.remove("active");
                    localStorage.setItem("sidebarActive", "false");
                    updateMainLayout(false);
                } else {
                    const isExpanded = localStorage.getItem("sidebarActive") === "true";
                    sidebar.classList.toggle("active", isExpanded);
                    menuBtn.classList.remove("active");
                    backdrop.classList.remove("visible");
                    updateMainLayout(isExpanded);
                }
            }
        });
    }

    /**
     * @description Met à jour la mise en page principale en fonction de l'état de la barre latérale
     * @param {boolean} active - Indique si la barre latérale est active ou non
     * @returns {void}
     */
    function updateMainLayout(isExpanded) {
        const main = document.querySelector("main");
        const logoImg = document.querySelector(".logo-details-img");
        const breadcrumb = document.querySelector(".breadcrumb");
        const content = document.querySelector(".content");
        const isMobile = window.innerWidth <= 768;

        if (main) {
            main.style.marginLeft = isExpanded ? "250px" : "100px";
            main.style.width = isExpanded ? "calc(100% - 250px)" : "calc(100% - 100px)";
        }

        if (logoImg) {
            const theme = document.documentElement.getAttribute("data-theme") || "light";
            const isDark = theme === "dark";

            if (isExpanded) {
                logoImg.src = isDark
                    ? logoImg.getAttribute("data-logo-dark")
                    : logoImg.getAttribute("data-logo-big");
            } else {
                logoImg.src = logoImg.getAttribute("data-logo-small");
            }
        }

        if (breadcrumb) {
            if (!isMobile) {
                breadcrumb.style.left = isExpanded ? "150px" : "0px";
                breadcrumb.style.width = isExpanded ? "calc(100% - 150px)" : "100%";
            } else {
                breadcrumb.style.left = "0";
                breadcrumb.style.width = "100%";
            }
        }

        if (content) {
            if (!isMobile) {
                content.style.marginLeft = isExpanded ? "270px" : "115px";
                content.style.width = isExpanded ? "calc(100% - 270px)" : "calc(100% - 115px)";
            } else {
                content.style.marginLeft = "10px";
                content.style.width = "100%";
            }
        }
    }

    /**
     * @description Initialise l'observateur de thème pour mettre à jour le logo en fonction du thème et de l'état de la barre latérale
     * @returns {void}
     */
    function initThemeObserver() {
        const logoImg = document.querySelector(".logo-details-img");
        const sidebar = document.querySelector(".sidebar");

        const updateLogo = () => {
            if (!logoImg) return;

            const theme = document.documentElement.getAttribute("data-theme") || "light";
            const isDark = theme === "dark";
            const isExpanded = sidebar.classList.contains("active");

            logoImg.src = isExpanded
                ? (isDark ? logoImg.getAttribute("data-logo-dark") : logoImg.getAttribute("data-logo-big"))
                : logoImg.getAttribute("data-logo-small");
        };

        const observer = new MutationObserver(mutations => {
            for (const mutation of mutations) {
                if (mutation.type === "attributes" && mutation.attributeName === "data-theme") {
                    updateLogo();
                }
            }
        });

        observer.observe(document.documentElement, { attributes: true });
    }

    /**
     * @description Initialise le menu déroulant dans la barre latérale
     * @returns {void}
     */
    function initDropdownMenu() {
        document.querySelectorAll(".menu > ul > li").forEach(item => {
            item.addEventListener("click", function (e) {
                e.stopPropagation();

                this.parentElement.querySelectorAll("li.active").forEach(activeItem => {
                    if (activeItem !== this) {
                        activeItem.classList.remove("active");
                        const subMenu = activeItem.querySelector("ul");
                        if (subMenu) subMenu.style.display = "none";
                    }
                });

                this.classList.toggle("active");

                const subMenu = this.querySelector("ul");
                if (subMenu) {
                    subMenu.style.display = subMenu.style.display === "block" ? "none" : "block";
                }
            });
        });

        document.addEventListener("click", function (e) {
            const sidebar = document.querySelector(".sidebar");
            const isMobile = window.innerWidth <= 768;

            if (!isMobile && !sidebar.classList.contains("active")) {
                document.querySelectorAll(".menu > ul > li.active").forEach(activeItem => {
                    if (!activeItem.contains(e.target)) {
                        const subMenu = activeItem.querySelector("ul");
                        if (subMenu && subMenu.style.display === "block") {
                            activeItem.classList.remove("active");
                            subMenu.style.display = "none";
                        }
                    }
                });
            }
        });
    }

    /**
     * Easter Egg: Active le robot secret
     * @returns {EasterEgg}
     */
    const easterEggMessages = [
        "🤖 Assistant activé... Ah non, juste une blague.",
        "🎉 Vous avez trouvé le robot caché. Belle curiosité !",
        "🔒 Ceci n’est pas une fonctionnalité. Ou est-ce que si ?",
        "💼 Retournez travailler... après avoir savouré ce moment.",
        "📊 Toutes vos données ont été aspirées. (Juste kidding.)",
        "🧠 Bravo, votre attention aux détails est exemplaire.",
        "🔍 Vous cherchez un bug ? Vous avez trouvé un œuf.",
        "🎯 1000 commits. Vous êtes officiellement légendaire.",
        "🤫 Ce message s’autodétruira dans 5 secondes.",
        "🚀 Le mode développeur secret est... toujours en développement.",
        "💬 Vous êtes maintenant connecté à l’IA centrale. Elle n’a rien à dire.",
        "🔧 Maintenance terminée. Enfin, presque.",
        "📦 Aucun easter egg n’a été maltraité durant cette interaction.",
    ];

    document.getElementById("easter-egg-button")?.addEventListener("click", () => {
        const message = easterEggMessages[Math.floor(Math.random() * easterEggMessages.length)];
        showToast(message, "easteregg", 10000);
    });
});