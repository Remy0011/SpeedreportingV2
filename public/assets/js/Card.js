document.addEventListener("DOMContentLoaded", function () {
  const counter = document.getElementById("breakUserCounter");

  if (counter) {
    const target = parseInt(counter.dataset.count, 10);
    let current = 0;
    const steps = 30;
    const increment = target / steps;
    const interval = 1000 / steps;

    const counterInterval = setInterval(() => {
      current += increment;
      if (current >= target) {
        current = target;
        clearInterval(counterInterval);
      }
      counter.textContent = Math.floor(current);
    }, interval);
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.getElementById("toggleCardMenu");
  const menu = document.getElementById("cardMenu");

  fetch("/preferences", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  })
    .then(res => res.json())
    .then(preferences => {
      Object.entries(preferences).forEach(([key, value]) => {
        const checkbox = document.querySelector(`input[data-target="${key}"]`);
        const card = document.querySelector(`[data-card="${key}"]`);

        if (checkbox) {
          checkbox.checked = value === "1";
        }

        if (card) {
          card.style.display = value === "1" ? "block" : "none";
        }
      });
    })
    .catch(err => {
      console.error("Erreur lors de la récupération des préférences :", err);
    });

  toggleBtn.addEventListener("click", () => {
    menu.classList.toggle("hidden");
  });

  menu.querySelectorAll("input[type='checkbox']").forEach(checkbox => {
    checkbox.addEventListener("change", () => {
      const target = checkbox.getAttribute("data-target");
      const card = document.querySelector(`[data-card="${target}"]`);

      if (card) {
        card.style.display = checkbox.checked ? "block" : "none";
      }

      fetch("/preferences", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          key: target,
          value: checkbox.checked,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) {
            console.error("Échec de la sauvegarde de la préférence.");
          }
        })
        .catch((err) => {
          console.error("Erreur de requête :", err);
        });
    });
  });

  document.addEventListener("click", (e) => {
    if (!menu.contains(e.target) && !toggleBtn.contains(e.target)) {
      menu.classList.add("hidden");
    }
  });
});
