const modal = document.getElementById("modal");
const title = document.getElementById("modalTitle");
const body = document.getElementById("modalBody");
const footer = document.getElementById("modalFooter");
const closeBtn = document.getElementById("closeModalBtn");

export function openModal({ titleText = "", bodyHTML = "", footerHTML = "" }) {
  title.textContent = titleText;
  body.innerHTML = bodyHTML;
  footer.innerHTML = footerHTML;

  // Re-render lucide icons inside modal if available
  if (window.lucide) {
    lucide.createIcons();
  }

  modal.classList.remove("hidden");
  document.body.classList.add("overflow-hidden");
  document.addEventListener("keydown", handleEscape);
}

export function closeModal() {
  modal.classList.add("hidden");
  document.body.classList.remove("overflow-hidden");
  document.removeEventListener("keydown", handleEscape);
}

function handleEscape(e) {
  if (e.key === "Escape") {
    closeModal();
  }
}

// Backdrop click close trigger
if (closeBtn) {
  closeBtn.addEventListener("click", closeModal);
}



// Clear all error states inside the modal body
export function clearModalErrors() {
  const errorEls = body.querySelectorAll(".error-msg");
  errorEls.forEach((el) => {
    el.textContent = "";
    el.classList.add("hidden");
  });

  const inputs = body.querySelectorAll("input, select");
  inputs.forEach((input) => {
    input.classList.remove("border-rose-500", "focus:ring-rose-500/20", "focus:border-rose-500");
  });
}

// Map key-value errors returned from PHP to input IDs
export function showModalErrors(errors = {}) {
  clearModalErrors();

  Object.entries(errors).forEach(([field, message]) => {
    const errorEl = body.querySelector(`#error-${field}`);
    const inputEl = body.querySelector(`[name="${field}"]`);

    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.remove("hidden");
    }

    if (inputEl) {
      inputEl.classList.add("border-rose-500", "focus:ring-rose-500/20", "focus:border-rose-500");
    }
  });
}