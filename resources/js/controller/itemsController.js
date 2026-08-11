import {
  fetchItems,
  createItem,
  updateItem,
  deleteItem,
} from "../api/itemsApi.js";
import { fetchCategories } from "../api/categoriesApi.js";

import {
  openModal,
  closeModal,
  showModalErrors,
  clearModalErrors,
} from "./modalController.js";

import { toastSuccess, toastError, confirmToast } from "./toastController.js";

// import { fetchSuppliers } from "../api/suppliersApi.js";

console.log("Items controller loaded");

// State
let allItems = [];
let categoriesList = [];
let suppliersList = [];
let currentSearch = "";
let currentFilter = "all"; // 'all' or 'low_stock'

// Helper to safely trigger Lucide icon rendering
function refreshIcons() {
  if (window.lucide && typeof window.lucide.createIcons === "function") {
    window.lucide.createIcons();
  }
}

// ======================
// Load Categories & Items
// ======================
async function init() {
  await Promise.all([loadCategories(), loadItems()]); // loadSuppliers()
  setupEventListeners();
  refreshIcons();
}

async function loadCategories() {
  try {
    const result = await fetchCategories();
    if (result && result.status === "success") {
      categoriesList = result.categories || result.data || [];
    }
  } catch (error) {
    console.error("Error loading categories:", error);
  }
}

// async function loadSuppliers() {
//   try {
//     const result = await fetchSuppliers();
//     if (result && result.status === "success") {
//       suppliersList = result.suppliers || result.data || [];
//     }
//   } catch (error) {
//     console.error("Error loading suppliers:", error);
//   }
// }

async function loadItems() {
  try {
    const result = await fetchItems();

    console.log(result);
    const totalEl = document.getElementById("totalItems");
    const lowStockEl = document.getElementById("totalLowStock");

    if (result && result.status === "success") {
      allItems = result.items;

      // Update Header Stats
      if (totalEl) totalEl.textContent = allItems.length;

      const lowStockCount = allItems.filter(
        (item) => Number(item.stock) <= Number(item.safety_stock),
      ).length;
      if (lowStockEl) lowStockEl.textContent = lowStockCount;

      applyFilters();
    } else {
      toastError(result?.message || "Failed to load items.");
    }
  } catch (error) {
    console.error("Error loading items:", error);
    toastError("Unable to connect to the server.");
  }
}

// ======================
// Filtering & Render Logic
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allItems.filter((item) => {
    const matchesSearch =
      term === "" ||
      item.name.toLowerCase().includes(term) ||
      (item.category_name && item.category_name.toLowerCase().includes(term));
    // || (item.supplier_name && item.supplier_name.toLowerCase().includes(term));

    const matchesFilter =
      currentFilter === "all" ||
      (currentFilter === "low_stock" &&
        Number(item.stock) <= Number(item.safety_stock));

    return matchesSearch && matchesFilter;
  });

  renderItems(filtered, allItems.length);
}

function renderItems(items, totalItemsCount = 0) {
  const tbody = document.querySelector("#itemsTableBody");

  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");

  if (showingCount) showingCount.innerHTML = items.length;
  if (totalCount) totalCount.innerHTML = totalItemsCount;

  if (!tbody) return;

  tbody.innerHTML = "";

  if (items.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="6" class="py-10 text-center text-gray-500">
          <div class="flex flex-col items-center gap-2">
            <i data-lucide="package-x" class="w-10 h-10 text-gray-300"></i>
            <p class="text-base font-medium">No items found</p>
            <p class="text-sm text-gray-400">Try adjusting your search query or add a new item.</p>
          </div>
        </td>
      </tr>
    `;
    refreshIcons();
    return;
  }

  items.forEach((item) => {
    const currentStock = Number(item.stock);
    const safetyStock = Number(item.safety_stock);
    const isLowStock = currentStock <= safetyStock;

    const stockBadge = isLowStock
      ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-rose-600">
            Low Stock (${currentStock})
         </span>`
      : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">
            (${currentStock}) in stock
         </span>`;

    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/80 transition group";

    tr.innerHTML = `
      <td class="py-3.5 px-4 sm:px-6 text-left">
        <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">${item.name}</div>
        <div class="text-[11px] text-gray-400 font-medium">ID: ITM-${item.id}</div>
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${item.category_name || "Uncategorized"}
      </td>
      
      <td class="py-3.5 px-4 sm:px-6 text-left">${stockBadge}</td>
      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${safetyStock}
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-900 font-bold">
        ₱${Number(item.selling_price).toFixed(2)}
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
        <div class="flex items-center justify-end space-x-1">
          <button title="Edit Item" class="edit-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
            <i data-lucide="pencil" class="w-4 h-4"></i>
          </button>
          <button title="Delete Item" class="delete-btn p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
        </div>
      </td>
    `;

    tr.querySelector(".edit-btn").addEventListener("click", () => {
      openItemModal({ title: "Edit Item", item });
    });

    tr.querySelector(".delete-btn").addEventListener("click", () => {
      removeItem(item.id);
    });

    tbody.appendChild(tr);
  });

  refreshIcons();
}

// ======================
// Event Listeners Setup
// ======================
function setupEventListeners() {
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", () => {
      currentSearch = searchInput.value;
      applyFilters();
    });
  }

  const allButton = document.getElementById("allButton");
  const lowStockBtn = document.getElementById("lowStockBtn");

  if (allButton && lowStockBtn) {
    allButton.addEventListener("click", () => {
      currentFilter = "all";
      allButton.className =
        "px-3 py-1.5 rounded-md bg-white text-gray-900 shadow-sm flex-1 sm:flex-none text-center cursor-pointer";
      lowStockBtn.className =
        "px-3 py-1.5 rounded-md hover:text-gray-900 transition flex-1 sm:flex-none text-center cursor-pointer";
      applyFilters();
    });

    lowStockBtn.addEventListener("click", () => {
      currentFilter = "low_stock";
      lowStockBtn.className =
        "px-3 py-1.5 rounded-md bg-white text-gray-900 shadow-sm flex-1 sm:flex-none text-center cursor-pointer";
      allButton.className =
        "px-3 py-1.5 rounded-md hover:text-gray-900 transition flex-1 sm:flex-none text-center cursor-pointer";
      applyFilters();
    });
  }

  const addBtn = document.getElementById("addBtn");
  if (addBtn) {
    addBtn.addEventListener("click", () => {
      openItemModal({ title: "Add New Item" });
    });
  }
}

// ======================
// Save & Delete Actions
// ======================
async function saveItem(id = null) {
  clearModalErrors();

  const name = document.getElementById("itemName").value.trim();
  const category_id = document.getElementById("itemCategory").value;
  // const supplier_id = document.getElementById("itemSupplier").value;
  const safety_stock = document.getElementById("itemSafetyStock").value.trim();
  const selling_price = document
    .getElementById("itemSellingPrice")
    .value.trim();

  const localErrors = {};
  if (!name) localErrors["name"] = "Item name is required.";
  if (!category_id) localErrors["category_id"] = "Please select a category.";
  // if (!supplier_id) localErrors["supplier_id"] = "Please select a supplier.";
  if (safety_stock === "")
    localErrors["safety_stock"] = "Safety stock is required.";
  if (selling_price === "")
    localErrors["selling_price"] = "Selling price is required.";

  if (Object.keys(localErrors).length > 0) {
    showModalErrors(localErrors);
    return;
  }

  try {
    let result;
    if (id) {
      result = await updateItem(
        id,
        name,
        category_id,
        // supplier_id,
        safety_stock,
        selling_price,
      );
    } else {
      result = await createItem(
        name,
        category_id,
        // supplier_id,
        safety_stock,
        selling_price,
      );
    }

    if (result && result.status === "error") {
      if (result.errors) {
        showModalErrors(result.errors);
      } else {
        toastError(result.message || "Failed to save item.");
      }
      return;
    }

    toastSuccess(result.message || "Saved successfully!");
    closeModal();
    loadItems();
  } catch (error) {
    console.error(error);
    toastError("An error occurred while saving the item.");
  }
}

async function removeItem(id) {
  const confirmed = await confirmToast({
    title: "Delete Item?",
    message:
      "Are you sure you want to delete this item? This action cannot be undone.",
    confirmText: "Delete",
    danger: true,
  });

  if (!confirmed) return;

  try {
    const result = await deleteItem(id);

    if (result && result.status === "success") {
      toastSuccess(result.message || "Item deleted successfully.");
      loadItems();
    } else {
      toastError(result.message || "Failed to delete item.");
    }
  } catch (error) {
    console.error(error);
    toastError("An error occurred while deleting.");
  }
}

// ======================
// Modal Handler
// ======================
async function openItemModal({ title, item = null }) {
  if (categoriesList.length === 0) {
    await loadCategories();
  }

  const categoryOptions =
    categoriesList.length > 0
      ? categoriesList
          .map(
            (cat) =>
              `<option value="${cat.id}" ${item && Number(item.category_id) === Number(cat.id) ? "selected" : ""}>
               ${cat.name}
             </option>`,
          )
          .join("")
      : `<option value="" disabled>No categories available.</option>`;

  // const supplierOptions =
  //   suppliersList.length > 0
  //     ? suppliersList
  //         .map(
  //           (sup) =>
  //             `<option value="${sup.id}" ${item && Number(item.supplier_id) === Number(sup.id) ? "selected" : ""}>
  //              ${sup.name}
  //            </option>`,
  //         )
  //         .join("")
  //     : `<option value="" disabled>No suppliers available</option>`;

  openModal({
    titleText: title,
    bodyHTML: `
      <form id="itemForm" onsubmit="event.preventDefault();" class="flex flex-col gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
            Item Name <span class="text-red-500">*</span>
          </label>
          <input
            id="itemName"
            name="name"
            type="text"
            class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
            placeholder="e.g. Wireless Mouse"
            value="${item ? item.name : ""}"
          >
          <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-name"></p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
            Category <span class="text-red-500">*</span>
          </label>
          <select
            id="itemCategory"
            name="category_id"
            class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 bg-white focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
          >
            <option value="">Select Category</option>
            ${categoryOptions}
          </select>
          <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-category_id"></p>
        </div>

        

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
              Safety Stock <span class="text-red-500">*</span>
            </label>
            <input
              id="itemSafetyStock"
              name="safety_stock"
              type="number"
              min="0"
              class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
              placeholder="5"
              value="${item ? item.safety_stock : "5"}"
            >
            <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-safety_stock"></p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
              Selling Price <span class="text-red-500">*</span>
            </label>
            <input
              id="itemSellingPrice"
              name="selling_price"
              type="number"
              step="0.01"
              min="0"
              class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
              placeholder="0.00"
              value="${item ? item.selling_price : ""}"
            >
            <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-selling_price"></p>
          </div>
        </div>
      </form>
    `,
    footerHTML: `
      <button
        id="cancelModalBtn"
        type="button"
        class="cursor-pointer rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 transition"
      >
        Cancel
      </button>

      <button
        id="saveItemBtn"
        type="button"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 transition"
      >
        Save Item
      </button>
    `,
  });

  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);
  document.getElementById("saveItemBtn").addEventListener("click", () => {
    saveItem(item ? item.id : null);
  });

  refreshIcons();
}

// Initialize on page load
init();
