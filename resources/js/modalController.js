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