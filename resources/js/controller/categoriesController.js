import {
  fetchCategories,
  createCategory,
  updateCategory,
  deleteCategory,
} from "../api/categoriesApi.js";

import {
  openModal,
  closeModal,
  showModalErrors,
  clearModalErrors,
} from "./modalController.js";


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
      }
      return;
    }

    alert(result.message || "Saved successfully!");
    closeModal();
    loadCategories();
  } catch (error) {
    console.error(error);
  }
}

// ======================
// Delete Supplier
// ======================
async function removeCategory(id) {
  if (!confirm("Are you sure you want to delete this category?")) return;

  try {
    const result = await deleteCategory(id);

    if (result.status === "success") {
      alert(result.message || "Category deleted successfully.");
      loadCategories();
    } else {
      alert(result.message || "Failed to delete category.");
    }
  } catch (error) {
    console.error(error);
    alert("An error occurred while deleting.");
  }
}

// ======================
// Load & Render Table
// ======================
// ======================
// State
// ======================
// let currentFilter = "all";
let allCategories = [];
let currentSearch = "";

async function loadCategories() {
  try {
    const result = await fetchCategories();
    const totalCount = document.getElementById("totalCategories");

    console.log(result.categories);

    if (result.status === "success") {
      allCategories = result.categories;
      totalCount.innerHTML = allCategories.length;

      applyFilters();
    } else {
      alert(result.message || "Failed to load categories.");
    }
  } catch (error) {
    console.error(error);
    alert("Unable to load categories.");
  }
}

// ======================
// Search + Filter
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allCategories.filter((categ) => {
    const matchesSearch =
      term === "" || categ.name?.toLowerCase().includes(term);

    return matchesSearch;
  });

  renderCategories(filtered);
}

const searchInput = document.getElementById("searchInput");

if (searchInput) {
  searchInput.addEventListener("input", () => {
    currentSearch = searchInput.value;
    applyFilters();
  });
}

function renderCategories(categories) {
  const tbody = document.querySelector("#categoriesTableBody");
  if (!tbody) return;

  tbody.innerHTML = "";

  if (categories.length === 0) {
    tbody.innerHTML = `
    <tr>
      <td colspan="5" class="py-10 text-center text-gray-500">
        <div class="flex flex-col items-center gap-2">
          <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
          <p class="text-base font-medium">No categories found</p>
          <p class="text-sm text-gray-400">
            Your categories table looks empty. Try adjusting your search or filter.
          </p>
        </div>
      </td>
    </tr>
  `;

    if (window.lucide) {
      lucide.createIcons();
    }

    return;
  }

  categories.forEach((category) => {
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
        8
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${category.created_at.slice(0, 10)}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
        <div class="flex items-center justify-end space-x-1">
            <button title="Edit Category" class="edit-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </button>
            <button title="Delete Category" class="delete-btn p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
    </td>
  `;

    // Bind Listeners
    tr.querySelector(".edit-btn").addEventListener("click", () => {
      openCategoryModal({ title: "Edit Category", category });
    });

    tr.querySelector(".delete-btn").addEventListener("click", () => {
      removeCategory(category.id);
    });

    tbody.appendChild(tr);
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
    openCategoryModal({ title: "Add Category" });
  });
}

// Helper function to build Add/Edit Modal dynamically
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
          placeholder="Acer Inc."
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
        Save Supplier
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
