import { fetchSales, getSale, createSale } from "../api/salesApi.js";
import { formatCurrency } from "../../../utils/Utility.js";

// NOTE: assumes itemsApi.js exports fetchItems() the same way it's used in
// purchasesController.js, and that each item includes `selling_price` and
// `stock` fields (both are used to build the row UI below).
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
// State
// ======================
let allSales = [];
let currentSearch = "";

let cachedItems = [];
let itemRowCounter = 0;

// ======================
// Save (Create) Handler
// ======================
async function saveSale() {
  clearModalErrors();

  const sale_date = document.getElementById("saleDate").value.trim();
  const items = collectItemRows();

  // Basic client-side check before fetching
  const localErrors = {};
  if (!sale_date) localErrors["sale_date"] = "Sale date is required.";
  if (items.length === 0) localErrors["items"] = "Add at least one valid item.";

  if (Object.keys(localErrors).length > 0) {
    showModalErrors(localErrors);
    return;
  }

  try {
    const result = await createSale(sale_date, items);

    if (result.status === "error") {
      if (result.errors) {
        // Display validation errors returned directly from PHP
        // (this is also where "only X unit(s) left in stock" errors surface)
        showModalErrors(result.errors);
      } else if (result.message) {
        toastError(result.message, "Sale");
      }
      return;
    }

    toastSuccess(result.message || "Sale recorded successfully!", "Sale");
    closeModal();
    loadSales();
  } catch (error) {
    console.error(error);
    toastError("An unexpected error occurred while saving.", "Sale");
  }
}

// ======================
// Load & Render Table
// ======================
async function loadSales() {
  try {
    const result = await fetchSales();
    const totalSalesAmount = document.getElementById("totalSales");

    console.log(result.sales);

    if (result.status === "success") {
      allSales = result.sales;

      if (totalSalesAmount) {
        const sum = allSales.reduce(
          (acc, s) => acc + Number(s.total_amount || 0),
          0,
        );
        totalSalesAmount.innerHTML = `₱${formatCurrency(sum)}`;
      }

      applyFilters();
    } else {
      toastError(result.message || "Failed to load sales.", "Sale");
    }
  } catch (error) {
    console.error(error);
    toastError("Unable to load sales. Please try again.", "Sale");
  }
}

// ======================
// Search
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allSales.filter((sale) => {
    const matchesSearch =
      term === "" ||
      String(sale.id).includes(term) ||
      sale.sale_date?.toLowerCase().includes(term);

    return matchesSearch;
  });

  renderSales(filtered, allSales.length);
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
function renderSales(sales, totalSaleCount = 0) {
  const showingCount = document.getElementById("showingCount");
  const totalCount = document.getElementById("totalCount");

  if (totalCount) totalCount.innerHTML = totalSaleCount;
  if (showingCount) showingCount.innerHTML = sales.length;

  const tbody = document.querySelector("#salesTableBody");
  const cardList = document.querySelector("#salesCardList");
  if (!tbody || !cardList) return;

  tbody.innerHTML = "";
  cardList.innerHTML = "";

  if (sales.length === 0) {
    const emptyHTML = `
      <div class="flex flex-col items-center gap-2 py-10 text-center text-gray-500">
        <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
        <p class="text-base font-medium">No sales found</p>
        <p class="text-sm text-gray-400">
          Your sales table looks empty. Try adjusting your search.
        </p>
      </div>
    `;

    tbody.innerHTML = `<tr><td colspan="4" class="py-10 text-center text-gray-500">${emptyHTML}</td></tr>`;
    cardList.innerHTML = emptyHTML;

    if (window.lucide) {
      lucide.createIcons();
    }

    return;
  }

  sales.forEach((sale) => {
    const amountLabel = `₱${formatCurrency(sale.total_amount)}`;

    // ---- Table row (desktop) ----
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/80 transition group";

    tr.innerHTML = `
    <td class="py-3.5 px-4 sm:px-6 text-left">
        <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
            SO-${sale.id}
        </div>
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${sale.sale_date}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-left text-gray-600 font-medium">
        ${amountLabel}
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
        <div class="flex items-center justify-end space-x-1">
            <button title="View Sale" class="view-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
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
              SO-${sale.id}
          </div>
          <div class="text-[11px] text-gray-400 font-medium">${sale.sale_date}</div>
        </div>
        <span class="shrink-0 text-sm font-bold text-gray-900">
            ${amountLabel}
        </span>
      </div>

      <div class="flex items-center justify-end gap-1 pt-1 border-t border-gray-100">
          <button title="View Sale" class="view-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
              <i data-lucide="eye" class="w-4 h-4"></i>
          </button>
      </div>
    `;

    // Bind Listeners
    tr.querySelector(".view-btn").addEventListener("click", () => {
      viewSale(sale.id);
    });
    card.querySelector(".view-btn").addEventListener("click", () => {
      viewSale(sale.id);
    });

    tbody.appendChild(tr);
    cardList.appendChild(card);
  });

  if (window.lucide) {
    lucide.createIcons();
  }
}

// ======================
// Add Sale Modal
// ======================
const addBtn = document.getElementById("addBtn");
if (addBtn) {
  addBtn.addEventListener("click", () => {
    openSaleModal();
  });
}

async function openSaleModal() {
  // Load item data (with stock + selling price) before building the form
  try {
    const itemResult = await fetchItems();
    cachedItems = itemResult.status === "success" ? itemResult.items : [];
  } catch (error) {
    console.error(error);
    toastError("Unable to load items.", "Sale");
    return;
  }

  itemRowCounter = 0;

  openModal({
    titleText: "Add Sale",

    bodyHTML: `
    <form id="saleForm" onsubmit="event.preventDefault();" class="flex flex-col gap-4">
      <div>
        <label for="saleDate" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Sale Date <span class="text-red-500">*</span>
        </label>
        <input
          id="saleDate"
          name="sale_date"
          type="date"
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          value="${new Date().toISOString().split("T")[0]}"
        >
        <p class="error-msg text-xs text-rose-500 font-medium mt-1 hidden" id="error-sale_date"></p>
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
        id="saveSaleBtn"
        type="button"
        class="inline-flex cursor-pointer items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 active:bg-blue-800 transition focus:outline-none focus:ring-2 focus:ring-blue-500/20"
      >
        Record Sale
      </button>
    `,
  });

  document
    .getElementById("cancelModalBtn")
    .addEventListener("click", closeModal);

  document.getElementById("saveSaleBtn").addEventListener("click", saveSale);

  document
    .getElementById("addItemRowBtn")
    .addEventListener("click", () => addItemRow());

  // Start with one empty row
  addItemRow();
}

// ======================
// Item Row Helpers
// ======================
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
        .map(
          (i) =>
            `<option value="${i.id}" data-price="${i.selling_price}" data-stock="${i.stock}">${i.name} (${i.stock} in stock)</option>`,
        )
        .join("")}
    </select>
    <input type="number" min="1" step="1" placeholder="Qty" class="qty-input col-span-2 rounded-lg border border-slate-200 px-2 py-2 text-sm text-slate-800" disabled>
    <span class="row-price col-span-2 text-xs font-medium text-slate-500 text-right">₱0.00 / unit</span>
    <span class="row-subtotal col-span-2 text-xs font-semibold text-slate-700 text-right">₱0.00</span>
    <button type="button" class="remove-row-btn col-span-1 text-gray-400 hover:text-red-600 cursor-pointer">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  `;

  container.appendChild(row);

  const itemSelect = row.querySelector(".item-select");
  const qtyInput = row.querySelector(".qty-input");

  // Selecting an item locks in its price + caps quantity to current stock —
  // price is display-only (the server is the source of truth), the stock
  // cap is just a UX guard so the "only X left" error is rarer, not a bypass.
  itemSelect.addEventListener("change", () => {
    const selectedOption = itemSelect.selectedOptions[0];
    const price = Number(selectedOption?.dataset.price || 0);
    const stock = Number(selectedOption?.dataset.stock || 0);

    qtyInput.disabled = !itemSelect.value;
    qtyInput.max = stock;
    qtyInput.value = "";

    row.querySelector(".row-price").innerHTML =
      `₱${formatCurrency(price)} / unit`;
    updateRowSubtotal(row);
    updateGrandTotal();
  });

  qtyInput.addEventListener("input", () => {
    updateRowSubtotal(row);
    updateGrandTotal();
  });

  row.querySelector(".remove-row-btn").addEventListener("click", () => {
    row.remove();
    updateGrandTotal();
  });

  if (window.lucide) {
    lucide.createIcons();
  }
}

function updateRowSubtotal(row) {
  const selectedOption = row.querySelector(".item-select").selectedOptions[0];
  const price = Number(selectedOption?.dataset.price || 0);
  const quantity = Number(row.querySelector(".qty-input").value) || 0;

  row.querySelector(".row-subtotal").innerHTML =
    `₱${formatCurrency(price * quantity)}`;
}

function collectItemRows() {
  const rows = document.querySelectorAll("#itemRowsContainer > div");
  const items = [];

  rows.forEach((row) => {
    const item_id = row.querySelector(".item-select").value;
    const quantity = Number(row.querySelector(".qty-input").value);

    if (item_id && quantity > 0) {
      items.push({ item_id, quantity });
    }
  });

  return items;
}

function updateGrandTotal() {
  const rows = document.querySelectorAll("#itemRowsContainer > div");
  let grandTotal = 0;

  rows.forEach((row) => {
    const selectedOption = row.querySelector(".item-select").selectedOptions[0];
    const price = Number(selectedOption?.dataset.price || 0);
    const quantity = Number(row.querySelector(".qty-input").value) || 0;

    grandTotal += price * quantity;
  });

  const display = document.getElementById("grandTotalDisplay");
  if (display) display.innerHTML = `₱${formatCurrency(grandTotal)}`;
}

// ======================
// View Sale (read-only)
// ======================
async function viewSale(id) {
  try {
    const result = await getSale(id);

    if (result.status !== "success") {
      toastError(result.message || "Failed to load sale.", "Sale");
      return;
    }

    const sale = result.sale;
    const items = result.items || sale.items || [];

    const itemRowsHTML = items
      .map(
        (item) => `
      <tr>
        <td class="py-2 px-3 text-left text-gray-700">${item.item_name || "Item #" + item.item_id}</td>
        <td class="py-2 px-3 text-right text-gray-700">${item.quantity}</td>
        <td class="py-2 px-3 text-right text-gray-700">₱${formatCurrency(item.unit_price)}</td>
        <td class="py-2 px-3 text-right text-gray-700">₱${formatCurrency(Number(item.quantity || 0) * Number(item.unit_price || 0))}</td>
      </tr>
    `,
      )
      .join("");

    openModal({
      titleText: `Sale SO-${sale.id}`,
      bodyHTML: `
        <div class="flex flex-col gap-3">
          <div class="text-sm text-gray-600">
            <span class="font-semibold text-gray-900">Date:</span>
            ${sale.sale_date}
          </div>

          <table class="w-full text-sm mt-2">
            <thead>
              <tr class="text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                <th class="py-2 px-3 text-left">Item</th>
                <th class="py-2 px-3 text-right">Qty</th>
                <th class="py-2 px-3 text-right">Unit Price</th>
                <th class="py-2 px-3 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>${itemRowsHTML}</tbody>
          </table>

          <div class="flex items-center justify-between border-t border-slate-100 pt-3">
            <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Total Amount</span>
            <span class="text-sm font-bold text-slate-900">₱${formatCurrency(sale.total_amount)}</span>
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
    toastError("Unable to load sale details.", "Sale");
  }
}

// Initialize data on load
loadSales();
