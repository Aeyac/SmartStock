const modal = document.getElementById("modal");
const modalCard = modal
  ? modal.querySelector("#modalCard") || modal.firstElementChild
  : null;
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

  // 1. Show modal element in DOM
  modal.classList.remove("hidden");
  document.body.classList.add("overflow-hidden");
  document.addEventListener("keydown", handleEscape);

  // 2. Trigger frame double-raf for smooth entrance animation
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      // Backdrop fade in
      modal.classList.remove("opacity-0");
      modal.classList.add("opacity-100");

      // Card scale & fade in from middle
      if (modalCard) {
        modalCard.classList.remove("opacity-0", "scale-95");
        modalCard.classList.add("opacity-100", "scale-100");
      }
    });
  });
}

export function closeModal() {
  // 1. Start exit animation
  modal.classList.remove("opacity-100");
  modal.classList.add("opacity-0");

  if (modalCard) {
    modalCard.classList.remove("opacity-100", "scale-100");
    modalCard.classList.add("opacity-0", "scale-95");
  }

  // 2. Wait for animation to end before adding 'hidden'
  const handleTransitionEnd = () => {
    modal.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
    document.removeEventListener("keydown", handleEscape);
  };

  modal.addEventListener("transitionend", handleTransitionEnd, { once: true });
}

function handleEscape(e) {
  if (e.key === "Escape") {
    closeModal();
  }
}

// Backdrop click close trigger
if (modal) {
  modal.addEventListener("click", (e) => {
    if (e.target === modal || e.target === closeBtn) {
      closeModal();
    }
  });
}

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
    input.classList.remove(
      "border-rose-500",
      "focus:ring-rose-500/20",
      "focus:border-rose-500",
    );
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
      inputEl.classList.add(
        "border-rose-500",
        "focus:ring-rose-500/20",
        "focus:border-rose-500",
      );
    }
  });
}
