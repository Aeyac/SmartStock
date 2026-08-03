const modal = document.getElementById("modal");
const title = document.getElementById("modalTitle");
const body = document.getElementById("modalBody");
const footer = document.getElementById("modalFooter");
const overlay = document.getElementById("modalOverlay");
const closeBtn = document.getElementById("closeModalBtn");

export function openModal({ titleText = "", bodyHTML = "", footerHTML = "" }) {
  title.textContent = titleText;
  body.innerHTML = bodyHTML;
  footer.innerHTML = footerHTML;

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

if (overlay) {
  overlay.addEventListener("click", closeModal);
}

if (closeBtn) {
  closeBtn.addEventListener("click", closeModal);
}