import {
  fetchItems,
  fetchDeletedItems,
  createItem,
  updateItem,
  deleteItem,
  restoreItem,
} from "../api/itemsApi.js";
import { fetchCategories } from "../api/categoriesApi.js";

import {
  openModal,
  closeModal,
  showModalErrors,
  clearModalErrors,
} from "./modalController.js";

import { toastSuccess, toastError, confirmToast } from "./toastController.js";

console.log("Items controller loaded");

// State
let allItems = [];
let archivedItems = [];
let categoriesList = [];
let currentSearch = "";
let currentFilter = "all"; // 'all' | 'low_stock' | 'archived'

// Load Categories & Items
async function init() {
  await Promise.all([loadCategories(), loadItems()]);
  setupEventListeners();

  if (window.lucide) {
    lucide.createIcons();
  }
}

async function loadCategories() {
  try {
    const result = await fetchCategories();

    if (result && result.status === "success") {
      categoriesList = result.categories.filter((c) => c.deleted_at === null);
    }
  } catch (error) {
    console.error(error);
    toastError("Failed to load categories.", "Categories");
  }
}

async function loadItems() {
  try {
    const result = await fetchItems();

    const totalEl = document.getElementById("totalItems");
    const lowStockEl = document.getElementById("totalLowStock");

    if (result && result.status === "success") {
      allItems = result.items;

      // Total number of items
      if (totalEl) {
        totalEl.textContent = allItems.length;
      }

      // Low stock = stock is BELOW safety stock
      const lowStockCount = allItems.filter(
        (item) => Number(item.stock) < Number(item.safety_stock),
      ).length;

      if (lowStockEl) {
        lowStockEl.textContent = lowStockCount;
      }

      applyFilters();
    } else {
      toastError(result?.message || "Failed to load items.");
    }
  } catch (error) {
    console.error(error);
    toastError("Failed to load items.", "Items");
  }
}

async function loadArchivedItems() {
  try {
    const result = await fetchDeletedItems();

    if (result && result.status === "success") {
      archivedItems = result.items;
      applyFilters();
    } else {
      toastError(result?.message || "Failed to load archived items.");
    }
  } catch (error) {
    console.error(error);
    toastError("Failed to load archived items.", "Items");
  }
}

// Filtering & Render Logic
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  // Archived view has its own source array and no low-stock concept
  if (currentFilter === "archived") {
    const filtered = archivedItems.filter((item) => {
      return (
        term === "" ||
        item.name.toLowerCase().includes(term) ||
        (item.category_name && item.category_name.toLowerCase().includes(term))
      );
    });

    renderItems(filtered, filtered.length, { archived: true });
    return;
  }

  const filtered = allItems.filter((item) => {
    const matchesSearch =
      term === "" ||
      item.name.toLowerCase().includes(term) ||
      (item.category_name && item.category_name.toLowerCase().includes(term));

    // LOW STOCK: Stock must be BELOW safety stock.
    const matchesFilter =
      currentFilter === "all" ||
      (currentFilter === "low_stock" &&
        Number(item.stock) < Number(item.safety_stock));

    return matchesSearch && matchesFilter;
  });

  renderItems(filtered, filtered.length, { archived: false });
}

function renderItems(items, totalItemsCount = 0, { archived = false } = {}) {
  const tbody = document.querySelector("#itemsTableBody");
  const cardList = document.querySelector("#itemsCardList");
  if (!tbody || !cardList) return;

  tbody.innerHTML = "";
  cardList.innerHTML = "";

  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");

  if (showingCount) showingCount.textContent = items.length;
  if (totalCount) totalCount.textContent = totalItemsCount;

  if (items.length === 0) {
    const emptyHTML = archived
      ? `
      <div class="flex flex-col items-center gap-2 py-10 text-center text-gray-500">
        <i data-lucide="archive-x" class="w-10 h-10 text-gray-300"></i>
        <p class="text-base font-medium">No archived items</p>
        <p class="text-sm text-gray-400">
          Deleted items will show up here.
        </p>
      </div>
    `
      : `
      <div class="flex flex-col items-center gap-2 py-10 text-center text-gray-500">
        <i data-lucide="package-x" class="w-10 h-10 text-gray-300"></i>
        <p class="text-base font-medium">No items found</p>
        <p class="text-sm text-gray-400">
          Try adjusting your search query or add a new item.
        </p>
      </div>
    `;

    tbody.innerHTML = `<tr><td colspan="6" class="py-10 text-center text-gray-500">${emptyHTML}</td></tr>`;
    cardList.innerHTML = emptyHTML;

    if (window.lucide) {
      lucide.createIcons();
    }

    return;
  }

  items.forEach((item) => {
    const currentStock = Number(item.stock);
    const safetyStock = Number(item.safety_stock);
    const isLowStock = currentStock < safetyStock;

    const stockBadge = archived
      ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-gray-500">
            (${currentStock}) in stock
         </span>`
      : isLowStock
        ? `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-rose-600">
            Low Stock (${currentStock})
         </span>`
        : `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold text-emerald-600">
            (${currentStock}) in stock
         </span>`;

    const actionsHTML = archived
      ? `
      <button
        title="Restore Item"
        class="restore-btn p-1.5 text-gray-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition cursor-pointer"
      >
        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
      </button>
    `
      : `
      <button
        title="Edit Item"
        class="edit-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer"
      >
        <i data-lucide="pencil" class="w-4 h-4"></i>
      </button>

      <button
        title="Delete Item"
        class="delete-btn p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer"
      >
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    `;

    // ---- Desktop Table Row ----
    const tr = document.createElement("tr");
    tr.className = `hover:bg-gray-50/80 transition group${
      archived ? " opacity-70" : ""
    }`;
    tr.innerHTML = `
      <td class="py-3.5 px-4 sm:px-6 text-left">
        <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
          ${item.name}
        </div>
        <div class="text-[11px] text-gray-400 font-medium">
          ID: ITM-${item.id}
        </div>
      </td>

      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${item.category_name || "Uncategorized"}
      </td>

      <td class="py-3.5 px-4 sm:px-6 text-left">
        ${stockBadge}
      </td>

      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${safetyStock}
      </td>

      <td class="py-3.5 px-4 sm:px-6 text-left text-gray-900 font-bold">
        ₱${Number(item.selling_price).toFixed(2)}
      </td>

      <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
        <div class="flex items-center justify-end space-x-1">
          ${actionsHTML}
        </div>
      </td>
    `;

    // ---- Mobile Card ----
    const card = document.createElement("div");
    card.className = `p-4 flex flex-col gap-2.5 hover:bg-gray-50/80 transition group border-b border-gray-100 last:border-b-0${
      archived ? " opacity-70" : ""
    }`;
    card.innerHTML = `
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="font-bold text-gray-900 group-hover:text-blue-600 transition truncate">
            ${item.name}
          </div>
          <div class="text-[11px] text-gray-400 font-medium">ID: ITM-${item.id}</div>
        </div>
        <div class="shrink-0 text-right">
          <div class="text-sm font-bold text-gray-900">₱${Number(item.selling_price).toFixed(2)}</div>
        </div>
      </div>

      <div class="flex items-center justify-between gap-2 text-xs text-gray-600">
        <span class="font-medium px-2 py-0.5 rounded bg-gray-100 text-gray-700 truncate max-w-[150px]">
          ${item.category_name || "Uncategorized"}
        </span>
        <div>${stockBadge}</div>
      </div>

      <div class="flex items-center justify-between text-xs text-gray-500 pt-1">
        <span>Safety Stock: <strong class="text-gray-700">${safetyStock}</strong></span>
        <div class="flex items-center justify-end space-x-1">
          ${actionsHTML}
        </div>
      </div>
    `;

    if (archived) {
      // Bind Listeners (Archived — restore only)
      tr.querySelector(".restore-btn").addEventListener("click", () => {
        restoreItemHandler(item.id);
      });
      card.querySelector(".restore-btn").addEventListener("click", () => {
        restoreItemHandler(item.id);
      });
    } else {
      // Bind Listeners (Desktop)
      tr.querySelector(".edit-btn").addEventListener("click", () => {
        openItemModal({ title: "Edit Item", item });
      });
      tr.querySelector(".delete-btn").addEventListener("click", () => {
        removeItem(item.id);
      });

      // Bind Listeners (Mobile)
      card.querySelector(".edit-btn").addEventListener("click", () => {
        openItemModal({ title: "Edit Item", item });
      });
      card.querySelector(".delete-btn").addEventListener("click", () => {
        removeItem(item.id);
      });
    }

    tbody.appendChild(tr);
    cardList.appendChild(card);
  });

  if (window.lucide) {
    lucide.createIcons();
  }
}

// Event Listeners Setup
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
  const archivedBtn = document.getElementById("archivedBtn");

  const activeClass =
    "px-3 py-1.5 rounded-md bg-white text-gray-900 shadow-sm flex-1 sm:flex-none text-center cursor-pointer";
  const inactiveClass =
    "px-3 py-1.5 rounded-md hover:text-gray-900 transition flex-1 sm:flex-none text-center cursor-pointer";

  function setActiveToggle(activeBtn) {
    [allButton, lowStockBtn, archivedBtn].forEach((btn) => {
      if (!btn) return;
      btn.className = btn === activeBtn ? activeClass : inactiveClass;
    });
  }

  if (allButton) {
    allButton.addEventListener("click", () => {
      currentFilter = "all";
      setActiveToggle(allButton);
      applyFilters();
    });
  }

  if (lowStockBtn) {
    lowStockBtn.addEventListener("click", () => {
      currentFilter = "low_stock";
      setActiveToggle(lowStockBtn);
      applyFilters();
    });
  }

  if (archivedBtn) {
    archivedBtn.addEventListener("click", async () => {
      currentFilter = "archived";
      setActiveToggle(archivedBtn);
      await loadArchivedItems();
    });
  }

  const addBtn = document.getElementById("addBtn");

  if (addBtn) {
    addBtn.addEventListener("click", () => {
      openItemModal({
        title: "Add New Item",
      });
    });
  }
}

// Save & Delete Actions
async function saveItem(id = null) {
  clearModalErrors();

  const name = document.getElementById("itemName").value.trim();
  const category_id = document.getElementById("itemCategory").value;
  const safety_stock = document.getElementById("itemSafetyStock").value.trim();

  const selling_price = document
    .getElementById("itemSellingPrice")
    .value.trim();

  const localErrors = {};

  if (!name) {
    localErrors["name"] = "Item name is required.";
  }

  if (!category_id) {
    localErrors["category_id"] = "Please select a category.";
  }

  if (safety_stock === "") {
    localErrors["safety_stock"] = "Safety stock is required.";
  }

  if (selling_price === "") {
    localErrors["selling_price"] = "Selling price is required.";
  }

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
        safety_stock,
        selling_price,
      );
    } else {
      result = await createItem(name, category_id, safety_stock, selling_price);
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

    await loadItems();
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

      await loadItems();
    } else {
      toastError(result?.message || "Failed to delete item.");
    }
  } catch (error) {
    console.error(error);
    toastError("An error occurred while deleting.");
  }
}

async function restoreItemHandler(id) {
  try {
    const result = await restoreItem(id);

    if (result && result.status === "success") {
      toastSuccess(result.message || "Item restored successfully.");

      // Refresh both lists since the item moves from archived back to active
      await Promise.all([loadArchivedItems(), loadItems()]);

      if (currentFilter === "archived") {
        applyFilters();
      }
    } else {
      toastError(result?.message || "Failed to restore item.");
    }
  } catch (error) {
    console.error(error);
    toastError("An error occurred while restoring the item.");
  }
}

// Modal Handler
async function openItemModal({ title, item = null }) {
  if (categoriesList.length === 0) {
    await loadCategories();
  }

  const categoryOptions =
    categoriesList.length > 0
      ? categoriesList
          .map(
            (cat) =>
              `<option value="${cat.id}" ${
                item && Number(item.category_id) === Number(cat.id)
                  ? "selected"
                  : ""
              }>
                ${cat.name}
              </option>`,
          )
          .join("")
      : `<option value="" disabled>No categories available.</option>`;

  openModal({
    titleText: title,

    bodyHTML: `
      <form
        id="itemForm"
        onsubmit="event.preventDefault();"
        class="flex flex-col gap-4"
      >

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

          <p
            class="error-msg text-xs text-rose-500 font-medium mt-1 hidden"
            id="error-name"
          ></p>
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

          <p
            class="error-msg text-xs text-rose-500 font-medium mt-1 hidden"
            id="error-category_id"
          ></p>
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

            <p
              class="error-msg text-xs text-rose-500 font-medium mt-1 hidden"
              id="error-safety_stock"
            ></p>
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

            <p
              class="error-msg text-xs text-rose-500 font-medium mt-1 hidden"
              id="error-selling_price"
            ></p>
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

  if (window.lucide) {
    lucide.createIcons();
  }
}

init();
