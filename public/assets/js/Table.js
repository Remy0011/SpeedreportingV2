import {
  bindModalEvents,
  adaptTableSize,
  bindPagination,
} from "./functions.js";

document.addEventListener("DOMContentLoaded", function () {
  bindModalEvents();
  adaptTableSize();
  bindPagination();

  const checkAll = document.getElementById("check_all");
  const rowChecks = document.querySelectorAll(".row-check");
  const validateBtn = document.getElementById("validate-multiple");
  const form = document.getElementById("validate-multiple-form");

  if (!checkAll || !validateBtn || !form || rowChecks.length === 0) {
    return;
  }

  /**
   * @description Met à jour l'état du bouton de validation en fonction des cases à cocher sélectionnées
   * @returns {void}
   */
  function updateValidateButton() {
    const checked = document.querySelectorAll(".row-check:checked");
    validateBtn.disabled = checked.length === 0;
  }

  checkAll.addEventListener("change", () => {
    rowChecks.forEach((chk) => (chk.checked = checkAll.checked));
    updateValidateButton();
  });

  rowChecks.forEach((chk) => {
    chk.addEventListener("change", () => {
      updateValidateButton();
      if (!chk.checked) checkAll.checked = false;
    });
  });

  validateBtn.addEventListener("click", (e) => {
    e.preventDefault();

    const selectedIds = document.querySelectorAll(".row-check:checked");

    if (selectedIds.length === 0) {
      return;
    }

    selectedIds.forEach((chk) => {
      const weekTarget = chk.getAttribute("data-week-target");
      const inputs = document.querySelectorAll(
        `input[data-week="${weekTarget}"]`,
      );
      inputs.forEach((input) => {
        const clone = input.cloneNode(true);
        clone.value = input.value;
        form.appendChild(clone);
      });
    });

    form.submit();
  });
});
