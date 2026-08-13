import {
  fetchPurchases,
  getPurchase,
  createPurchase,
} from "../api/purchasesApi.js";

// NOTE: assumes suppliersApi.js and itemsApi.js export fetchSuppliers() and
// fetchItems() the same way suppliersController.js already uses fetchSuppliers().
// Adjust these two import paths/names if your itemsApi differs.
import { fetchSuppliers } from "../api/suppliersApi.js";
import { fetchItems } from "../api/itemsApi.js";

import {
  openModal,
  closeModal,
  showModalErrors,
  clearModalErrors,
} from "./modalController.js";
import { toastSuccess, toastError } from "./toastController.js";
import { formatCurrency } from "../../../utils/Utility.js";

console.log("loaded");

// ======================
// State
// ======================
let allPurchases = [];
let currentSearch = "";

let cachedSuppliers = [];
let cachedItems = [];
let itemRowCounter = 0;

// ======================
// Save (Create) Handler
// ======================
async function savePurchase() {
  clearModalErrors();

  const supplier_id = document.getElementById("purchaseSupplier").value;
  const purchase_date = document.getElementById("purchaseDate").value.trim();
  const items = collectItemRows();

  // Basic client-side check before fetching
  const localErrors = {};
  if (!supplier_id) localErrors["supplier_id"] = "Supplier is required.";
  if (!purchase_date)
    localErrors["purchase_date"] = "Purchase date is required.";
  if (items.length === 0) localErrors["items"] = "Add at least one valid item.";

  if (Object.keys(localErrors).length > 0) {
    showModalErrors(localErrors);
    return;
  }

  try {
    const result = await createPurchase(supplier_id, purchase_date, items);

    if (result.status === "error") {
      if (result.errors) {
        // Display validation errors returned directly from PHP
        showModalErrors(result.errors);
      } else if (result.message) {
        toastError(result.message, "Purchase");
      }
      return;
    }

    toastSuccess(
      result.message || "Purchase recorded successfully!",
      "Purchase",
    );
    closeModal();
    loadPurchases();
  } catch (error) {
    console.error(error);
    toastError("An unexpected error occurred while saving.", "Purchase");
  }
}

// ======================
// Load & Render Table
// ======================
async function loadPurchases() {
  try {
    const result = await fetchPurchases();
    const totalPurchaseAmount = document.getElementById("totalPurchases");

    console.log(result.purchases);

    if (result.status === "success") {
      allPurchases = result.purchases;

      if (totalPurchaseAmount) {
        const sum = allPurchases.reduce(
          (acc, p) => acc + Number(p.total_amount || 0),
          0,
        );
        totalPurchaseAmount.innerHTML = `₱${formatCurrency(sum)}`;
      }

      applyFilters();
    } else {
      toastError(result.message || "Failed to load purchases.", "Purchase");
    }
  } catch (error) {
    console.error(error);
    toastError("Unable to load purchases. Please try again.", "Purchase");
  }
}

// ======================
// Search
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allPurchases.filter((purchase) => {
    const matchesSearch =
      term === "" ||
      String(purchase.id).includes(term) ||
      purchase.supplier_name?.toLowerCase().includes(term) ||
      purchase.purchase_date?.toLowerCase().includes(term);

    return matchesSearch;
  });

  renderPurchases(filtered, allPurchases.length);
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
function renderPurchases(purchases, totalPurchasesCount = 0) {
  const tbody = document.querySelector("#purchasesTableBody");
  const cardList = document.querySelector("#purchasesCardList");
  if (!tbody || !cardList) return;

  tbody.innerHTML = "";
  cardList.innerHTML = "";

  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");

  if (showingCount) showingCount.innerHTML = purchases.length;
  if (totalCount) totalCount.innerHTML = totalPurchasesCount;

  if (purchases.length === 0) {
    const emptyHTML = `
      <div class="flex flex-col items-center gap-2 py-10 text-center text-gray-500">
        <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
        <p class="text-base font-medium">No purchases found</p>
        <p class="text-sm text-gray-400">
          Your purchases table looks empty. Try adjusting your search.
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

  purchases.forEach((purchase) => {
    const supplierLabel =
      purchase.supplier_name || "Supplier #" + purchase.supplier_id;
    const amountLabel = `₱${formatCurrency(purchase.total_amount)}`;

    // ---- Table row (desktop) ----
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/80 transition group";

    tr.innerHTML = `
    <td class="py-3.5 px-4 sm:px-6 text-left">
        <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
            PO-${purchase.id}
        </div>
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${supplierLabel}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${amountLabel}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${purchase.purchase_date}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
        <div class="flex items-center justify-end space-x-1">
            <button title="View Purchase" class="view-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
                <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
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
              PO-${purchase.id}
          </div>
          <div class="text-[11px] text-gray-400 font-medium">${purchase.purchase_date}</div>
        </div>
        <span class="shrink-0 text-sm font-bold text-gray-900">
            ${amountLabel}
        </span>
      </div>

      <div class="text-xs sm:text-sm text-gray-600 flex items-center gap-1.5">
        <i data-lucide="truck" class="w-3.5 h-3.5 text-gray-400"></i>
        <span class="font-medium truncate">${supplierLabel}</span>
      </div>

      <div class="flex items-center justify-end gap-1 pt-1 border-t border-gray-100">
          <button title="View Purchase" class="view-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
              <i data-lucide="eye" class="w-4 h-4"></i>
          </button>
      </div>
    `;

    // Bind Listeners
    tr.querySelector(".view-btn").addEventListener("click", () => {
      viewPurchase(purchase.id);
    });
    card.querySelector(".view-btn").addEventListener("click", () => {
      viewPurchase(purchase.id);
    });

    tbody.appendChild(tr);
    cardList.appendChild(card);
  });

  if (window.lucide) {
    lucide.createIcons();
  }
}

// Add Purchase Modal
const addBtn = document.getElementById("addBtn");
if (addBtn) {
  addBtn.addEventListener("click", () => {
    openPurchaseModal();
  });
}

async function openPurchaseModal() {
  // Load dropdown data before building the form
  try {
    const [supplierResult, itemResult] = await Promise.all([
      fetchSuppliers(),
      fetchItems(),
    ]);

    const allFetchedSuppliers =
      supplierResult.status === "success" ? supplierResult.suppliers : [];
    cachedSuppliers = allFetchedSuppliers.filter(
      (supplier) => supplier.deleted_at === null,
    );
    console.log(cachedSuppliers);

    cachedItems = itemResult.status === "success" ? itemResult.items : [];
  } catch (error) {
    console.error(error);
    toastError("Unable to load suppliers/items.", "Purchase");
    return;
  }

  itemRowCounter = 0;

  openModal({
    titleText: "Add Purchase",

    bodyHTML: `
    <form id="purchaseForm" onsubmit="event.preventDefault();" class="flex flex-col gap-4">
      <div>
        <label for="purchaseSupplier" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Supplier <span class="text-red-500">*</span>
        </label>
        <select
          id="purchaseSupplier"
          name="supplier_id"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
        >
          <option value="">Select a supplier</option>
          ${cachedSuppliers
            .map((s) => `<option value="${s.id}">${s.name}</option>`)
            .join("")}
        </select>
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-supplier_id"></p>
      </div>

      <div>
        <label for="purchaseDate" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Purchase Date <span class="text-red-500">*</span>
        </label>
        <input
          id="purchaseDate"
          name="purchase_date"
          type="date"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          value="${new Date().toISOString().split("T")[0]}"
        >
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-purchase_date"></p>
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">
            Items <span class="text-red-500">*</span>
          </label>
          <button
            id="addItemRowBtn"
            type="button"
            class="text-xs font-semibold text-blue-600 hover:text-blue-800 cursor-pointer"
          >
            + Add Item
          </button>
        </div>
        <div id="itemRowsContainer" class="flex flex-col gap-2"></div>
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-items"></p>
      </div>

      <div class="flex items-center justify-between border-t border-slate-100 pt-3">
        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Grand Total</span>
        <span id="grandTotalDisplay" class="text-sm font-bold text-slate-900">₱0.00</span>
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
        id="savePurchaseBtn"
        type="button"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 active:bg-blue-800 transition focus:outline-none focus:ring-2 focus:ring-blue-500/20"
      >
        Save Purchase
      </button>
    `,
  });

  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);

  document
    .getElementById("savePurchaseBtn")
    .addEventListener("click", savePurchase);

  document
    .getElementById("addItemRowBtn")
    .addEventListener("click", () => addItemRow());

  // Start with one empty row
  addItemRow();
}

// Item Row Helpers
function addItemRow() {
  const container = document.getElementById("itemRowsContainer");
  if (!container) return;

  const rowId = `itemRow-${itemRowCounter++}`;

  const row = document.createElement("div");
  row.id = rowId;
  row.className = "grid grid-cols-12 gap-2 items-center";

  row.innerHTML = `
    <select class="item-select col-span-5 rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-800">
      <option value="">Item</option>
      ${cachedItems
        .map((i) => `<option value="${i.id}">${i.name}</option>`)
        .join("")}
    </select>
    <input type="number" min="1" step="1" placeholder="Qty" class="qty-input col-span-2 rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-800">
    <input type="number" min="0" step="0.01" placeholder="Unit Cost" class="cost-input col-span-3 rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-800">
    <span class="row-subtotal col-span-1 text-xs font-semibold text-slate-700 text-right">₱0.00</span>
    <button type="button" class="remove-row-btn col-span-1 text-gray-400 hover:text-red-600 cursor-pointer">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  `;

  container.appendChild(row);

  const qtyInput = row.querySelector(".qty-input");
  const costInput = row.querySelector(".cost-input");

  qtyInput.addEventListener("input", updateGrandTotal);
  costInput.addEventListener("input", updateGrandTotal);

  row.querySelector(".remove-row-btn").addEventListener("click", () => {
    row.remove();
    updateGrandTotal();
  });

  if (window.lucide) {
    lucide.createIcons();
  }
}

function collectItemRows() {
  const rows = document.querySelectorAll("#itemRowsContainer > div");
  const items = [];

  rows.forEach((row) => {
    const item_id = row.querySelector(".item-select").value;
    const quantity = Number(row.querySelector(".qty-input").value);
    const unit_cost = Number(row.querySelector(".cost-input").value);

    if (item_id && quantity > 0 && unit_cost >= 0) {
      items.push({ item_id, quantity, unit_cost });
    }
  });

  return items;
}

function updateGrandTotal() {
  const rows = document.querySelectorAll("#itemRowsContainer > div");
  let grandTotal = 0;

  rows.forEach((row) => {
    const quantity = Number(row.querySelector(".qty-input").value) || 0;
    const unit_cost = Number(row.querySelector(".cost-input").value) || 0;
    const subtotal = quantity * unit_cost;

    row.querySelector(".row-subtotal").innerHTML = `₱${subtotal.toFixed(2)}`;
    grandTotal += subtotal;
  });

  const display = document.getElementById("grandTotalDisplay");
  if (display) display.innerHTML = `₱${grandTotal.toFixed(2)}`;
}

// ======================
// View Purchase (read-only)
// ======================
async function viewPurchase(id) {
  try {
    const result = await getPurchase(id);
    console.log(result);
    if (result.status !== "success") {
      toastError(result.message || "Failed to load purchase.", "Purchase");
      return;
    }

    const purchase = result.purchase;
    const items = result.items || purchase.items || [];

    const itemRowsHTML = items
      .map(
        (item) => `
      <tr>
        <td class="py-2 px-3 text-left text-gray-700">${item.item_name || "Item #" + item.item_id}</td>
        <td class="py-2 px-3 text-right text-gray-700">${item.quantity}</td>
        <td class="py-2 px-3 text-right text-gray-700">₱${Number(item.unit_cost || 0).toFixed(2)}</td>
        <td class="py-2 px-3 text-right text-gray-700">₱${(Number(item.quantity || 0) * Number(item.unit_cost || 0)).toFixed(2)}</td>
      </tr>
    `,
      )
      .join("");

    openModal({
      titleText: `Purchase PO-${purchase.id}`,
      bodyHTML: `
        <div class="flex flex-col gap-3">
          <div class="text-sm text-gray-600">
            <span class="font-semibold text-gray-900">Supplier:</span>
            ${purchase.supplier_name || "Supplier #" + purchase.supplier_id}
          </div>
          <div class="text-sm text-gray-600">
            <span class="font-semibold text-gray-900">Date:</span>
            ${purchase.purchase_date}
          </div>

          <table class="w-full text-sm mt-2">
            <thead>
              <tr class="text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                <th class="py-2 px-3 text-left">Item</th>
                <th class="py-2 px-3 text-right">Qty</th>
                <th class="py-2 px-3 text-right">Unit Cost</th>
                <th class="py-2 px-3 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>${itemRowsHTML}</tbody>
          </table>

          <div class="flex items-center justify-between border-t border-slate-100 pt-3">
            <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Total Amount</span>
            <span class="text-sm font-bold text-slate-900">₱${formatCurrency(purchase.total_amount)}</span>
          </div>
        </div>
      `,
      footerHTML: `
        <button
          id="closeViewBtn"
          type="button"
          class="cursor-pointer rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition"
        >
          Close
        </button>
      `,
    });

    document
      .getElementById("closeViewBtn")
      .addEventListener("click", closeModal);
  } catch (error) {
    console.error(error);
    toastError("Unable to load purchase details.", "Purchase");
  }
}

// Initialize data on load
loadPurchases();
