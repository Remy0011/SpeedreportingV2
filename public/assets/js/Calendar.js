import { bindModalEvents, fetchAndReplace } from './functions.js';

document.addEventListener('DOMContentLoaded', () => {
  window.calendar = { initAll };
  initAll();

  document.addEventListener("click", (e) => {
    const daySelector = e.target.closest("#day_selector");
    if (!daySelector) return;

    if (daySelector.options.length <= 1) {
      console.warn("Sélecteur vide, injection dynamique des jours...");

      daySelector.innerHTML = `<option value="">Jour non précisé</option>`;

      const weekDates = getCurrentWeekDates();

      for (const day of weekDates) {
        const option = document.createElement("option");
        option.value = day.index;
        option.dataset.date = day.dateStr;
        option.textContent = `${day.label} (${day.dateStr})`;
        daySelector.appendChild(option);
      }
    }
  });

});

function initAll() {
  loadHoursEntryForm();
  bindModalEvents();
}

/**
 * Navigation par mois via délégation
 */
document.body.addEventListener('click', (e) => {
  const btn = e.target.closest('#prev-month, #next-month, #today-month');
  if (btn) {
    e.preventDefault();
    const { month, year } = btn.dataset;
    if (month && year) {
      const params = new URLSearchParams({ month, year });
      console.log(`Navigating to month: ${month}, year: ${year}`);
      fetchAndReplace(`?${params.toString()}`, '#calendar-container', window.calendar.initAll);
    }
  }
});

const formHandlers = new WeakMap();

function getCurrentWeekDates() {
  const now = new Date();
  const currentDay = now.getDay();
  const monday = new Date(now);
  monday.setDate(now.getDate() - ((currentDay + 6) % 7));

  const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
  return days.map((label, i) => {
    const date = new Date(monday);
    date.setDate(monday.getDate() + i);
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");
    return {
      label,
      index: i + 1,
      dateStr: `${yyyy}-${mm}-${dd}`,
    };
  });
}

function loadHoursEntryForm() {
  const form = document.getElementById("hours-entry-form");
  const fullCalendarContainer = document.getElementById("full_calendar");
  const daySelector = document.getElementById("day_selector");
  const workDateInput = document.getElementById("work_date");

  if (!form || !daySelector || !workDateInput || !fullCalendarContainer) {
    console.warn("Certains éléments requis pour loadHoursEntryForm sont absents");
    return;
  }

  // Remplir dynamiquement le select
  const weekDates = getCurrentWeekDates();
  daySelector.innerHTML = `<option value="">Jour non précisé</option>`;
  for (const day of weekDates) {
    const option = document.createElement("option");
    option.value = day.index;
    option.dataset.date = day.dateStr;
    option.textContent = `${day.label} (${day.dateStr})`;
    daySelector.appendChild(option);
  }

  // Gérer la sélection de jour
  daySelector.addEventListener("change", () => {
    const selected = daySelector.selectedOptions[0];
    const date = selected?.dataset.date;
    if (date) {
      workDateInput.value = date;
      workDateInput.disabled = false;
    } else {
      workDateInput.value = "";
      workDateInput.disabled = true;
    }
  });

  // Gestion de l'envoi du formulaire
  const existingHandler = formHandlers.get(form);
  if (existingHandler) {
    form.removeEventListener("submit", existingHandler);
  }

  const submitHandler = async function (e) {
    e.preventDefault();
    console.log("submit intercepted");

    workDateInput.disabled = false;
    const formData = new FormData(form);

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "FetchRequest" },
      });

      console.log("Status:", response.status);
      console.log("Content-Type:", response.headers.get("content-type"));

      if (response.ok) {
        const html = await response.text();
        if (html) {
          document.getElementById("full_calendar").innerHTML = html;
          initAll();
          showToast("Saisie des heures enregistrée avec succès.", "success");
        } else {
          console.warn("Réponse vide reçue, rechargement de la page.");
          location.reload();
        }
      } else {
        showToast("Erreur lors de la soumission du formulaire.", "error");
      }
    } catch (err) {
      console.error("Erreur fetch submit:", err);
      showToast("Erreur réseau lors de la soumission du formulaire.", "error");
    }
  };

  form.addEventListener("submit", submitHandler);
  formHandlers.set(form, submitHandler);
}
