import {
  fetchCategories,
  fetchArchivedCategories,
  createCategory,
  updateCategory,
  deleteCategory,
  restoreCategory,
} from "../api/categoriesApi.js";

import {
  openModal,
  closeModal,
  showModalErrors,
  clearModalErrors,
} from "./modalController.js";

import { toastSuccess, toastError, confirmToast } from "./toastController.js";

console.log("loaded");

async function saveCategory(id = null) {
  clearModalErrors();

  const name = document.getElementById("categoryName").value.trim();

  const localErrors = {};
  if (!name) localErrors["name"] = "Category name is required.";

  if (Object.keys(localErrors).length > 0) {
    showModalErrors(localErrors);
    return;
  }

  try {
    const result = id
      ? await updateCategory(id, name)
      : await createCategory(name);

    if (result.status === "error") {
      if (result.errors) {
        // Display validation errors returned directly from PHP
        showModalErrors(result.errors);
      } else if (result.message) {
        // e.g. "This category already exists." has no field to attach to
        toastError(result.message);
      }
      return;
    }

    toastSuccess(result.message || "Saved successfully!");
    closeModal();
    loadCategories();
  } catch (error) {
    console.error(error);
    toastError("An unexpected error occurred while saving.");
  }
}

// ======================
// Archive / Restore
// ======================
async function archiveCategory(id) {
  const confirmed = await confirmToast({
    title: "Archive Category?",
    message: "You can restore it later from the Archived tab.",
    confirmText: "Archive",
    danger: true,
  });

  if (!confirmed) return;

  try {
    const result = await deleteCategory(id);

    if (result.status === "success") {
      toastSuccess(result.message || "Category archived successfully.");
      loadCategories();
    } else {
      toastError(result.message || "Failed to archive category.");
    }
  } catch (error) {
    console.error(error);
    toastError("An error occurred while archiving.");
  }
}

async function restoreCategoryHandler(id) {
  try {
    const result = await restoreCategory(id);

    if (result.status === "success") {
      toastSuccess(result.message || "Category restored successfully.");
      loadCategories();
    } else {
      // e.g. "An active category with this name already exists."
      toastError(result.message || "Failed to restore category.");
    }
  } catch (error) {
    console.error(error);
    toastError("An error occurred while restoring.");
  }
}

// ======================
// State
// ======================
let currentView = "all"; // "all" | "archived"
let allCategories = [];
let currentSearch = "";

// ======================
// Load & Render Table
// ======================
async function loadCategories() {
  try {
    const result =
      currentView === "archived"
        ? await fetchArchivedCategories()
        : await fetchCategories();

    const totalCount = document.getElementById("totalCategories");

    console.log(result.categories);

    if (result.status === "success") {
      allCategories = result.categories;
      if (totalCount) totalCount.innerHTML = allCategories.length;

      applyFilters();
    } else {
      toastError(result.message || "Failed to load categories.");
    }
  } catch (error) {
    console.error(error);
    toastError("Unable to load categories.");
  }
}

// ======================
// All / Archived Toggle
// ======================
const viewBtns = [
  { el: document.getElementById("allCategoriesBtn"), value: "all" },
  { el: document.getElementById("archivedCategoriesBtn"), value: "archived" },
];

const activeClasses = ["bg-white", "text-gray-900", "shadow-sm"];

viewBtns.forEach(({ el, value }) => {
  if (!el) return;
  el.addEventListener("click", () => {
    viewBtns.forEach(
      ({ el: btn }) => btn && btn.classList.remove(...activeClasses),
    );
    el.classList.add(...activeClasses);

    currentView = value;

    // "Add Category" only makes sense on the All tab — archived
    // categories can't be created fresh, only restored.
    const isArchivedView = value === "archived";
    const addBtnEl = document.getElementById("addBtn");
    if (addBtnEl) addBtnEl.classList.toggle("hidden", isArchivedView);

    loadCategories();
  });
});

// ======================
// Search
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allCategories.filter((categ) => {
    const matchesSearch =
      term === "" || categ.name?.toLowerCase().includes(term);

    return matchesSearch;
  });

  renderCategories(filtered, allCategories.length);
}

const searchInput = document.getElementById("searchInput");

if (searchInput) {
  searchInput.addEventListener("input", () => {
    currentSearch = searchInput.value;
    applyFilters();
  });
}

// ======================
// Render (table for desktop, cards for mobile)
// ======================
function renderCategories(categories, totalCategoriesTotal = 0) {
  const tbody = document.querySelector("#categoriesTableBody");
  const cardList = document.querySelector("#categoriesCardList");
  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");

  if (showingCount) showingCount.innerHTML = categories.length;
  if (totalCount) totalCount.innerHTML = totalCategoriesTotal;

  if (!tbody || !cardList) return;

  tbody.innerHTML = "";
  cardList.innerHTML = "";

  if (categories.length === 0) {
    const emptyMessage =
      currentView === "archived"
        ? "No archived categories."
        : "Your categories table looks empty. Try adjusting your search.";

    const emptyHTML = `
      <div class="flex flex-col items-center gap-2 py-10 text-center text-gray-500">
        <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
        <p class="text-base font-medium">No categories found</p>
        <p class="text-sm text-gray-400">${emptyMessage}</p>
      </div>
    `;

    tbody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-gray-500">${emptyHTML}</td></tr>`;
    cardList.innerHTML = emptyHTML;

    if (window.lucide) {
      lucide.createIcons();
    }

    return;
  }

  categories.forEach((category) => {
    const itemCount = 8;

    const dateLabel =
      currentView === "archived"
        ? `Archived: ${category.deleted_at?.slice(0, 10) ?? "—"}`
        : category.created_at?.slice(0, 10);

    const actionsHTML =
      currentView === "archived"
        ? `
          <button title="Restore Category" class="restore-btn p-1.5 text-gray-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition cursor-pointer">
              <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          </button>
        `
        : `
          <button title="Edit Category" class="edit-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
              <i data-lucide="pencil" class="w-4 h-4"></i>
          </button>
          <button title="Archive Category" class="archive-btn p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer">
              <i data-lucide="archive" class="w-4 h-4"></i>
          </button>
        `;

    // ---- Table row (desktop) ----
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/80 transition group";

    tr.innerHTML = `
    <td class="py-3.5 px-4 sm:px-6 text-left">
        <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
            ${category.name}
        </div>
        <div class="text-[11px] text-gray-400 font-medium">ID: CATEG-${category.id}</div>
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${itemCount}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${dateLabel}
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
              ${category.name}
          </div>
          <div class="text-[11px] text-gray-400 font-medium">ID: CATEG-${category.id}</div>
        </div>
        <span class="shrink-0 text-[11px] text-gray-400 font-medium">
            ${dateLabel}
        </span>
      </div>

      <div class="text-xs sm:text-sm text-gray-600 flex items-center gap-1.5">
        <i data-lucide="box" class="w-3.5 h-3.5 text-gray-400"></i>
        <span class="font-medium">${itemCount} items</span>
      </div>

      <div class="flex items-center justify-end gap-1 pt-1 border-t border-gray-100">
          ${actionsHTML}
      </div>
    `;

    // Bind Listeners
    if (currentView === "archived") {
      tr.querySelector(".restore-btn").addEventListener("click", () => {
        restoreCategoryHandler(category.id);
      });
      card.querySelector(".restore-btn").addEventListener("click", () => {
        restoreCategoryHandler(category.id);
      });
    } else {
      tr.querySelector(".edit-btn").addEventListener("click", () => {
        openCategoryModal({ title: "Edit Category", category });
      });
      tr.querySelector(".archive-btn").addEventListener("click", () => {
        archiveCategory(category.id);
      });
      card.querySelector(".edit-btn").addEventListener("click", () => {
        openCategoryModal({ title: "Edit Category", category });
      });
      card.querySelector(".archive-btn").addEventListener("click", () => {
        archiveCategory(category.id);
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
// Add Category Modal
// ======================
const addBtn = document.getElementById("addBtn");
if (addBtn) {
  addBtn.addEventListener("click", () => {
    openCategoryModal({ title: "Add Category" });
  });
}

// Helper function to build Add/Edit Modal dynamically
function openCategoryModal({ title, category = null }) {
  openModal({
    titleText: title,

    bodyHTML: `
    <form id="categoryForm" onsubmit="event.preventDefault();" class="flex flex-col gap-4">
      <div>
        <label for="categoryName" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Category Name <span class="text-red-500">*</span>
        </label>
        <input
          id="categoryName"
          name="name"
          type="text"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          placeholder="Electronics"
          value="${category ? category.name : ""}"
        >
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-name"></p>
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
        id="saveCategoryBtn"
        type="button"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 active:bg-blue-800 transition focus:outline-none focus:ring-2 focus:ring-blue-500/20"
      >
        Save Category
      </button>
    `,
  });

  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);

  document
    .getElementById("saveCategoryBtn")
    .addEventListener("click", () =>
      saveCategory(category ? category.id : null),
    );
}

// Initialize data on load
loadCategories();