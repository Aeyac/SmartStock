import { fetchLedger, createAdjustment } from "../api/stockledgerApi.js";

// NOTE: assumes itemsApi.js exports fetchItems() the same way it's used in
// purchasesController.js / salesController.js, and that each item includes
// `name` and `stock` fields.
import { fetchItems } from "../api/itemsApi.js";

import {
  openModal,
  closeModal,
  showModalErrors,
  clearModalErrors,
} from "./modalController.js";

import { toastSuccess, toastError } from "./toastController.js";

console.log("loaded");

// ======================
// Helpers
// ======================
function formatNumber(amount) {
  return Number(amount || 0).toLocaleString("en-PH");
}

// ======================
// State
// ======================
let allEntries = [];
let currentFilter = "all";
let currentSearch = "";

let cachedItems = [];

// ======================
// Load & Render Table
// ======================
async function loadLedger() {
  try {
    const result = await fetchLedger();
    const totalEntries = document.getElementById("totalLedgerEntries");

    console.log(result.entries);

    if (result.status === "success") {
      allEntries = result.entries;

      if (totalEntries)
        totalEntries.innerHTML = formatNumber(allEntries.length);

      applyFilters();
    } else {
      toastError(result.message || "Failed to load stock ledger.");
    }
  } catch (error) {
    console.error(error);
    toastError("Unable to load stock ledger.");
  }
}

// ======================
// Search + Filter
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allEntries.filter((entry) => {
    const matchesFilter =
      currentFilter === "all" || entry.type === currentFilter;

    // Search by item name, numeric ID, or formatted ID (e.g. SL-123 or 123)
    const entryId = String(entry.id || "").toLowerCase();
    const formattedId = `sl-${entryId}`;

    const matchesSearch =
      term === "" ||
      entry.item_name?.toLowerCase().includes(term) ||
      entryId.includes(term) ||
      formattedId.includes(term);

    return matchesFilter && matchesSearch;
  });

  renderLedger(filtered, allEntries.length);
}

const searchInput = document.getElementById("searchInput");

if (searchInput) {
  searchInput.addEventListener("input", () => {
    currentSearch = searchInput.value;
    applyFilters();
  });
}

// ======================
// Filter Button Toggle
// ======================
const filterBtns = [
  { el: document.getElementById("allButton"), value: "all" },
  { el: document.getElementById("purchasesBtn"), value: "purchase" },
  { el: document.getElementById("salesBtn"), value: "sale" },
  { el: document.getElementById("adjustmentBtn"), value: "adjustment" },
];

const activeClasses = ["bg-white", "text-gray-900", "shadow-sm"];

filterBtns.forEach(({ el, value }) => {
  if (!el) return;
  el.addEventListener("click", () => {
    filterBtns.forEach(
      ({ el: btn }) => btn && btn.classList.remove(...activeClasses),
    );
    el.classList.add(...activeClasses);

    currentFilter = value;
    applyFilters();
  });
});

function renderLedger(entries, totalEntiresCount = 0) {
  const tbody = document.querySelector("#itemsTableBody");
  const cardList = document.querySelector("#ledgerCardList");
  if (!tbody || !cardList) return;

  tbody.innerHTML = "";
  cardList.innerHTML = "";

  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");

  if (showingCount) showingCount.innerHTML = entries.length;
  if (totalCount) totalCount.innerHTML = totalEntiresCount;

  if (entries.length === 0) {
    const emptyHTML = `
      <div class="flex flex-col items-center gap-2 py-10 text-center text-gray-500">
        <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
        <p class="text-base font-medium">No ledger entries found</p>
        <p class="text-sm text-gray-400">
          Try adjusting your search or filter.
        </p>
      </div>
    `;

    tbody.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-gray-500">${emptyHTML}</td></tr>`;
    cardList.innerHTML = emptyHTML;

    if (window.lucide) {
      lucide.createIcons();
    }

    return;
  }

  entries.forEach((entry) => {
    const badgeStyles = {
      purchase: "text-emerald-600 ",
      sale: "text-rose-600 ",
      adjustment: "text-amber-600",
    };

    const badgeBg = badgeStyles[entry.type] || badgeStyles.adjustment;
    const typeLabel = entry.type.charAt(0).toUpperCase() + entry.type.slice(1);

    const isPositive = Number(entry.quantity_change) > 0;
    const qtyColor = isPositive ? "text-emerald-600" : "text-rose-600";
    const qtySign = isPositive ? "+" : "";

    // ---- Table row (desktop) ----
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/80 transition group";

    tr.innerHTML = `
    <td class="py-3.5 px-4 sm:px-6 text-left">
        <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
            SL-${entry.id}
        </div>
        <div class="text-[11px] text-gray-400 font-medium">${entry.created_at}</div>
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left">
       <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold  ${badgeBg}">
            ${typeLabel}
        </span>
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${entry.item_name}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left font-semibold ${qtyColor}">
        ${qtySign}${formatNumber(entry.quantity_change)}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${formatNumber(entry.balance_after)}
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
              SL-${entry.id}
          </div>
          <div class="text-[11px] text-gray-400 font-medium">${entry.created_at}</div>
        </div>
        <span class="inline-flex items-center gap-1.5 shrink-0 text-xs font-semibold ${badgeBg}">
            ${typeLabel}
        </span>
      </div>

      <div class="flex items-center justify-between pt-1 border-t border-gray-100 text-sm">
        <span class="text-gray-600 font-medium truncate">${entry.item_name}</span>
        <span class="font-semibold ${qtyColor} shrink-0">${qtySign}${formatNumber(entry.quantity_change)}</span>
      </div>

      <div class="flex items-center justify-between text-xs text-gray-400 font-medium">
        <span>Stock after</span>
        <span class="text-gray-700 font-semibold">${formatNumber(entry.balance_after)}</span>
      </div>
    `;

    tbody.appendChild(tr);
    cardList.appendChild(card);
  });

  if (window.lucide) {
    lucide.createIcons();
  }
}

// ======================
// Add Adjustment Modal
// ======================
const addBtn = document.getElementById("addBtn");
if (addBtn) {
  addBtn.addEventListener("click", () => {
    openAdjustmentModal();
  });
}

async function openAdjustmentModal() {
  try {
    const itemResult = await fetchItems();
    cachedItems = itemResult.status === "success" ? itemResult.items : [];
  } catch (error) {
    console.error(error);
    toastError("Unable to load items.");
    return;
  }

  openModal({
    titleText: "Add Stock Adjustment",

    bodyHTML: `
    <form id="adjustmentForm" onsubmit="event.preventDefault();" class="flex flex-col gap-4">
      <div>
        <label for="adjustmentItem" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Item <span class="text-red-500">*</span>
        </label>
        <select
          id="adjustmentItem"
          name="item_id"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        >
          <option value="">Select an item</option>
          ${cachedItems
            .map(
              (i) =>
                `<option value="${i.id}" data-stock="${i.stock}">${i.name} (current: ${i.stock})</option>`,
            )
            .join("")}
        </select>
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-item_id"></p>
      </div>

      <div>
        <label for="adjustmentQuantity" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Quantity Change <span class="text-red-500">*</span>
        </label>
        <input
          id="adjustmentQuantity"
          name="quantity_change"
          type="number"
          step="1"
          placeholder="e.g. -5 for damaged stock, 10 for a recount correction"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        >
        <p class="text-xs text-slate-400 font-medium mt-1">
          Use a positive number to add stock, negative to remove it.
        </p>
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-quantity_change"></p>
      </div>

      <div class="flex items-center justify-between border-t border-slate-100 pt-3">
        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Resulting Stock</span>
        <span id="resultingStockDisplay" class="text-sm font-bold text-slate-900">—</span>
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
        id="saveAdjustmentBtn"
        type="button"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 active:bg-blue-800 transition focus:outline-none focus:ring-2 focus:ring-blue-500/20"
      >
        Save Adjustment
      </button>
    `,
  });

  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);

  document
    .getElementById("saveAdjustmentBtn")
    .addEventListener("click", saveAdjustment);

  const itemSelect = document.getElementById("adjustmentItem");
  const qtyInput = document.getElementById("adjustmentQuantity");

  const updatePreview = () => {
    const selectedOption = itemSelect.selectedOptions[0];
    const currentStock = Number(selectedOption?.dataset.stock ?? NaN);
    const change = Number(qtyInput.value);

    const display = document.getElementById("resultingStockDisplay");
    if (!display) return;

    if (!itemSelect.value || qtyInput.value === "" || Number.isNaN(change)) {
      display.innerHTML = "—";
      return;
    }

    display.innerHTML = formatNumber(currentStock + change);
  };

  itemSelect.addEventListener("change", updatePreview);
  qtyInput.addEventListener("input", updatePreview);
}

async function saveAdjustment() {
  clearModalErrors();

  const item_id = document.getElementById("adjustmentItem").value;
  const quantity_change = document.getElementById("adjustmentQuantity").value;

  const localErrors = {};
  if (!item_id) localErrors["item_id"] = "Please select an item.";
  if (quantity_change === "" || Number(quantity_change) === 0)
    localErrors["quantity_change"] = "Enter a non-zero quantity change.";

  if (Object.keys(localErrors).length > 0) {
    showModalErrors(localErrors);
    return;
  }

  try {
    const result = await createAdjustment(item_id, Number(quantity_change));

    if (result.status === "error") {
      if (result.errors) {
        showModalErrors(result.errors);
      } else if (result.message) {
        toastError(result.message);
      }
      return;
    }

    toastSuccess(result.message || "Adjustment recorded successfully!");
    closeModal();
    loadLedger();
  } catch (error) {
    console.error(error);
    toastError("An unexpected error occurred while saving the adjustment.");
  }
}

// Initialize data on load
loadLedger();
