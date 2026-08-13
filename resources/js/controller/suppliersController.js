import {
  fetchSuppliers,
  createSupplier,
  updateSupplier,
  deleteSupplier,
  restoreSupplier,
} from "../api/suppliersApi.js";

// Save (Create or Update) Handler
import {
  openModal,
  closeModal,
  showModalErrors,
  clearModalErrors,
} from "./modalController.js";

import { toastSuccess, toastError, confirmToast } from "./toastController.js";

console.log("loaded");

// Helpers
function formatDate(dateStr) {
  if (!dateStr) return "—";

  const date = new Date(dateStr.replace(" ", "T"));
  if (isNaN(date.getTime())) return "—";

  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

async function saveSupplier(id = null) {
  clearModalErrors();

  const name = document.getElementById("supplierName").value.trim();
  const contact_number = document
    .getElementById("supplierContact")
    .value.trim();
  const email = document.getElementById("supplierEmail").value.trim();

  // Basic client-side check before fetching
  const localErrors = {};
  if (!name) localErrors["name"] = "Supplier name is required.";
  if (!contact_number)
    localErrors["contact_number"] = "Contact number is required.";
  if (!email) localErrors["email"] = "Email address is required.";

  if (Object.keys(localErrors).length > 0) {
    showModalErrors(localErrors);
    return;
  }

  try {
    const result = id
      ? await updateSupplier(id, name, contact_number, email)
      : await createSupplier(name, contact_number, email);

    if (result.status === "error") {
      if (result.errors) {
        // Display validation errors returned directly from PHP
        showModalErrors(result.errors);
      } else if (result.message) {
        // e.g. duplicate email conflict, or "archived — restore first"
        toastError(result.message, "Supplier");
      }
      return;
    }

    toastSuccess(result.message);
    closeModal();
    loadSuppliers();
  } catch (error) {
    console.error(error);
  }
}

// Archive / Restore
async function archiveSupplier(id) {
  const confirmed = await confirmToast({
    title: "Archive this supplier?",
    message: "You can restore it later from the Archived tab.",
    confirmText: "Archive",
    danger: true,
  });
  if (!confirmed) return;

  try {
    const result = await deleteSupplier(id);

    if (result.status === "success") {
      toastSuccess(result.message, "Supplier");
      loadSuppliers();
    } else {
      toastError(result.message, "Supplier");
    }
  } catch (error) {
    console.error(error);
    toastError("An error occurred while archiving.", "Supplier");
  }
}

async function restoreSupplierHandler(id) {
  try {
    const result = await restoreSupplier(id);

    if (result.status === "success") {
      toastSuccess(result.message, "Supplier");
      loadSuppliers();
    } else {
      // e.g. "An active supplier with this email already exists."
      toastError(result.message, "Supplier");
    }
  } catch (error) {
    console.error(error);
    toastError("An error occurred while restoring.", "Supplier");
  }
}

// ======================
// State
// ======================
let currentView = "active"; // "active" | "archived"
let allSuppliers = [];
let currentSearch = "";

// ======================
// Load & Render Table
// ======================
async function loadSuppliers() {
  try {
    const result = await fetchSuppliers();

    const totalCount = document.getElementById("totalSuppliers");
    const archivedCount = document.getElementById("totalArchived");

    console.log(result.suppliers);

    if (result.status === "success") {
      allSuppliers = result.suppliers;

      const activeSuppliers = allSuppliers.filter(
        (supplier) => supplier.deleted_at === null,
      );
      const archivedSuppliers = allSuppliers.filter(
        (supplier) => supplier.deleted_at !== null,
      );

      if (totalCount) totalCount.innerHTML = activeSuppliers.length;
      if (archivedCount) archivedCount.innerHTML = archivedSuppliers.length;

      applyFilters();
    } else {
      toastError(result.message || "Failed to load suppliers.", "Supplier");
    }
  } catch (error) {
    console.error("Load suppliers error:", error);
    toastError("Unable to load suppliers. Please try again.", "Supplier");
  }
}

// ======================
// Active / Archived Toggle
// ======================
// Just re-filters and re-renders the data already loaded above — no
// second network request needed when switching tabs.
const viewBtns = [
  { el: document.getElementById("activeSuppliersBtn"), value: "active" },
  { el: document.getElementById("archivedSuppliersBtn"), value: "archived" },
];

const viewActiveClasses = ["bg-white", "text-gray-900", "shadow-sm"];

viewBtns.forEach(({ el, value }) => {
  if (!el) return;
  el.addEventListener("click", () => {
    viewBtns.forEach(
      ({ el: btn }) => btn && btn.classList.remove(...viewActiveClasses),
    );
    el.classList.add(...viewActiveClasses);

    currentView = value;

    // "Add Supplier" only makes sense on the Suppliers tab — archived
    // suppliers can't be created fresh, only restored.
    const isArchivedView = value === "archived";
    const addBtnEl = document.getElementById("addBtn");
    if (addBtnEl) addBtnEl.classList.toggle("hidden", isArchivedView);

    applyFilters();
  });
});

// ======================
// Search
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allSuppliers.filter((supplier) => {
    const isArchived = supplier.deleted_at !== null;

    // Which tab (Suppliers vs Archived) this row belongs to
    const matchesView = currentView === "archived" ? isArchived : !isArchived;

    const matchesSearch =
      term === "" ||
      supplier.name?.toLowerCase().includes(term) ||
      supplier.contact_number?.toLowerCase().includes(term) ||
      supplier.email?.toLowerCase().includes(term);

    return matchesView && matchesSearch;
  });

  const totalForCurrentView = allSuppliers.filter((supplier) =>
    currentView === "archived"
      ? supplier.deleted_at !== null
      : supplier.deleted_at === null,
  ).length;

  renderSuppliers(filtered, totalForCurrentView);
}

const searchInput = document.getElementById("searchInput");

if (searchInput) {
  searchInput.addEventListener("input", () => {
    currentSearch = searchInput.value;
    applyFilters();
  });
}

// Render (table for desktop, cards for mobile)
function renderSuppliers(suppliers, totalSuppliersCount = 0) {
  const tbody = document.querySelector("#suppliersTableBody");
  const cardList = document.querySelector("#suppliersCardList");
  if (!tbody || !cardList) return;

  tbody.innerHTML = "";
  cardList.innerHTML = "";

  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");

  if (totalCount) totalCount.innerHTML = totalSuppliersCount;
  if (showingCount) showingCount.innerHTML = suppliers.length;

  if (suppliers.length === 0) {
    const emptyMessage =
      currentView === "archived"
        ? "No archived suppliers."
        : "Your suppliers table looks empty. Try adjusting your search.";

    const emptyHTML = `
      <div class="flex flex-col items-center gap-2 py-10 text-center text-gray-500">
        <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
        <p class="text-base font-medium">No suppliers found</p>
        <p class="text-sm text-gray-400">${emptyMessage}</p>
      </div>
    `;

    tbody.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-gray-500">${emptyHTML}</td></tr>`;
    cardList.innerHTML = emptyHTML;

    if (window.lucide) lucide.createIcons();
    return;
  }

  suppliers.forEach((supplier) => {
    const addedLabel = formatDate(supplier.created_at);

    const actionsHTML =
      currentView === "archived"
        ? `
          <button title="Restore Supplier" class="restore-btn p-1.5 text-gray-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition cursor-pointer">
              <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          </button>
        `
        : `
          <button title="Edit Supplier" class="edit-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
              <i data-lucide="pencil" class="w-4 h-4"></i>
          </button>
          <button title="Archive Supplier" class="archive-btn p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer">
              <i data-lucide="archive" class="w-4 h-4"></i>
          </button>
        `;

    // ---- Table row (desktop) ----
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/80 transition group";
    tr.innerHTML = `
      <td class="py-3.5 px-4 sm:px-6 text-left">
          <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
              ${supplier.name}
          </div>
          <div class="text-[11px] text-gray-400 font-medium">ID: SUP-${supplier.id}</div>
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
          ${supplier.contact_number}
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-left">
          <a href="mailto:${supplier.email}" class="text-blue-600 hover:underline font-medium">
              ${supplier.email}
          </a>
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-500 font-medium">
          ${addedLabel}
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
          <div class="flex items-center justify-end space-x-1">
              ${actionsHTML}
          </div>
      </td>
    `;

    // ---- Card (mobile) ----
    const card = document.createElement("div");
    card.className =
      "p-4 flex flex-col gap-2.5 hover:bg-gray-50/80 transition group";
    card.innerHTML = `
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="font-bold text-gray-900 group-hover:text-blue-600 transition truncate">
              ${supplier.name}
          </div>
          <div class="text-[11px] text-gray-400 font-medium">ID: SUP-${supplier.id}</div>
        </div>
        <span class="shrink-0 text-[11px] text-gray-400 font-medium">
            Added ${addedLabel}
        </span>
      </div>

      <div class="text-xs sm:text-sm text-gray-600 space-y-1">
        <div class="flex items-center gap-1.5">
          <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i>
          <span class="font-medium">${supplier.contact_number}</span>
        </div>
        <div class="flex items-center gap-1.5">
          <i data-lucide="mail" class="w-3.5 h-3.5 text-gray-400"></i>
          <a href="mailto:${supplier.email}" class="text-blue-600 hover:underline font-medium truncate">
              ${supplier.email}
          </a>
        </div>
      </div>

      <div class="flex items-center justify-end gap-1 pt-1 border-t border-gray-100">
          ${actionsHTML}
      </div>
    `;

    // Bind listeners on both
    if (currentView === "archived") {
      tr.querySelector(".restore-btn").addEventListener("click", () => {
        restoreSupplierHandler(supplier.id);
      });
      card.querySelector(".restore-btn").addEventListener("click", () => {
        restoreSupplierHandler(supplier.id);
      });
    } else {
      tr.querySelector(".edit-btn").addEventListener("click", () => {
        openSupplierModal({ title: "Edit Supplier", supplier });
      });
      tr.querySelector(".archive-btn").addEventListener("click", () => {
        archiveSupplier(supplier.id);
      });
      card.querySelector(".edit-btn").addEventListener("click", () => {
        openSupplierModal({ title: "Edit Supplier", supplier });
      });
      card.querySelector(".archive-btn").addEventListener("click", () => {
        archiveSupplier(supplier.id);
      });
    }

    tbody.appendChild(tr);
    cardList.appendChild(card);
  });

  if (window.lucide) {
    lucide.createIcons();
  }
}

// ======================
// Add Supplier Modal
// ======================
const addBtn = document.getElementById("addBtn");
if (addBtn) {
  addBtn.addEventListener("click", () => {
    openSupplierModal({ title: "Add Supplier" });
  });
}

// Helper function to build Add/Edit Modal dynamically
function openSupplierModal({ title, supplier = null }) {
  openModal({
    titleText: title,

    bodyHTML: `
    <form id="supplierForm" onsubmit="event.preventDefault();" class="flex flex-col gap-4">
      <div>
        <label for="supplierName" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Supplier Name <span class="text-red-500">*</span>
        </label>
        <input
          id="supplierName"
          name="name"
          type="text"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          placeholder="Acer Inc."
          value="${supplier ? supplier.name : ""}"
        >
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-name"></p>
      </div>

      <div>
        <label for="supplierContact" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Contact Number <span class="text-red-500">*</span>
        </label>
        <input
          id="supplierContact"
          name="contact_number"
          type="text"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          placeholder="0912345678"
          value="${supplier ? supplier.contact_number : ""}"
        >
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-contact_number"></p>
      </div>

      <div>
        <label for="supplierEmail" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Email Address <span class="text-red-500">*</span>
        </label>
        <input
          id="supplierEmail"
          name="email"
          type="email"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          placeholder="supplier@gmail.com"
          value="${supplier ? supplier.email : ""}"
        >
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-email"></p>
      </div>
    </form>
  `,

    footerHTML: `
      <button
        id="cancelModalBtn"
        type="button"
        class="cursor-pointer rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition"
      >
        Cancel
      </button>

      <button
        id="saveSupplierBtn"
        type="button"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 active:bg-blue-800 transition focus:outline-none focus:ring-2 focus:ring-blue-500/20"
      >
        Save Supplier
      </button>
    `,
  });

  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);

  document
    .getElementById("saveSupplierBtn")
    .addEventListener("click", () =>
      saveSupplier(supplier ? supplier.id : null),
    );
}

// Initialize data on load
loadSuppliers();
