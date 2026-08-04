import {
  fetchSuppliers,
  createSupplier,
  updateSupplier,
  deleteSupplier,
} from "./api/suppliersApi.js";
import { openModal, closeModal } from "./modalController.js";

console.log("loaded");
// Save (Create or Update) Handler
async function saveSupplier(id = null) {
  const name = document.getElementById("supplierName").value.trim();
  const contact_number = document
    .getElementById("supplierContact")
    .value.trim();
  const email = document.getElementById("supplierEmail").value.trim();
  const status = document.getElementById("supplierStatus").value.trim();

  if (!name || !contact_number || !email) {
    alert("Please fill in all fields.");
    return;
  }

  try {
    let result;
    if (id) {
      result = await updateSupplier(id, name, contact_number, email, status);
    } else {
      result = await createSupplier(name, contact_number, email, status);
    }

    if (result.status === "error") {
      console.log(result.errors || result.message);
      alert(result.message || "Failed to save supplier.");
      return;
    }

    alert(result.message || "Saved successfully!");
    closeModal();
    loadSuppliers();
  } catch (error) {
    console.error(error);
    alert("An error occurred while saving.");
  }
}

// ======================
// Delete Supplier
// ======================
async function removeSupplier(id) {
  if (!confirm("Are you sure you want to delete this supplier?")) return;

  try {
    const result = await deleteSupplier(id);

    if (result.status === "success") {
      alert(result.message || "Supplier deleted successfully.");
      loadSuppliers();
    } else {
      alert(result.message || "Failed to delete supplier.");
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
let allSuppliers = [];
let currentFilter = "all";
let currentSearch = "";

// ======================
// Load & Render Table
// ======================
async function loadSuppliers() {
  try {
    const result = await fetchSuppliers();
    const total = document.getElementById("totalSuppliers");
    console.log(result.suppliers);

    if (result.status === "success") {
      allSuppliers = result.suppliers;
      total.innerHTML = allSuppliers.length;
      applyFilters();
    } else {
      alert(result.message || "Failed to load suppliers.");
    }
  } catch (error) {
    console.error(error);
    alert("Unable to load suppliers.");
  }
}

// ======================
// Search + Filter
// ======================
function applyFilters() {
  const term = currentSearch.trim().toLowerCase();

  const filtered = allSuppliers.filter((supplier) => {
    const status = (supplier.status || "active").toLowerCase();

    const matchesFilter = currentFilter === "all" || status === currentFilter;

    const matchesSearch =
      term === "" ||
      supplier.name.toLowerCase().includes(term) ||
      supplier.contact_number.toLowerCase().includes(term) ||
      supplier.email.toLowerCase().includes(term);

    return matchesFilter && matchesSearch;
  });

  renderSuppliers(filtered);
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
  { el: document.getElementById("activeBtn"), value: "active" },
  { el: document.getElementById("inactiveBtn"), value: "inactive" },
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

function renderSuppliers(suppliers) {
  const tbody = document.querySelector("#suppliersTableBody");
  if (!tbody) return;

  tbody.innerHTML = "";

  if (suppliers.length === 0) {
    tbody.innerHTML = `
    <tr>
      <td colspan="5" class="py-10 text-center text-gray-500">
        <div class="flex flex-col items-center gap-2">
          <i data-lucide="search-x" class="w-10 h-10 text-gray-300"></i>
          <p class="text-base font-medium">No suppliers found</p>
          <p class="text-sm text-gray-400">
            Your suppliers table looks empty. Try adjusting your search or filter.
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

  suppliers.forEach((supplier) => {
    const isActive =
      typeof supplier.status !== "undefined"
        ? supplier.status.toLowerCase() === "active"
        : Boolean(supplier.active);

    // Dynamic Tailwind classes for Active (Green) vs Inactive (Red)
    const badgeBg = isActive
      ? "bg-emerald-50 text-emerald-700 border-emerald-200/60"
      : "bg-rose-50 text-rose-700 border-rose-200/60";

    const dotBg = isActive ? "bg-emerald-500" : "bg-rose-500";
    const statusText = isActive ? "Active" : "Inactive";

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

    <td class="py-3.5 px-4 sm:px-6 text-left">
       <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border ${badgeBg}">
            <span class="w-1.5 h-1.5 rounded-full ${dotBg}"></span>
            ${statusText}
        </span>
    </td>

    <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
        <div class="flex items-center justify-end space-x-1">
            <button title="Edit Supplier" class="edit-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </button>
            <button title="Delete Supplier" class="delete-btn p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
    </td>
  `;

    // Bind Listeners
    tr.querySelector(".edit-btn").addEventListener("click", () => {
      openSupplierModal({ title: "Edit Supplier", supplier });
    });

    tr.querySelector(".delete-btn").addEventListener("click", () => {
      removeSupplier(supplier.id);
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
    openSupplierModal({ title: "Add Supplier" });
  });
}

// Helper function to build Add/Edit Modal dynamically
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
          type="text"
          required
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          placeholder="Acer Inc."
          value="${supplier ? supplier.name : ""}"
        >
      </div>

      <div>
        <label for="supplierContact" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Contact Number <span class="text-red-500">*</span>
        </label>
        <input
          id="supplierContact"
          type="text"
          required
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          placeholder="0912345678"
          value="${supplier ? supplier.contact_number : ""}"
        >
      </div>

      <div>
        <label for="supplierEmail" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Email Address <span class="text-red-500">*</span>
        </label>
        <input
          id="supplierEmail"
          type="email"
          required
          class="w-full rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          placeholder="supplier@gmail.com"
          value="${supplier ? supplier.email : ""}"
        >
      </div>

      <div>
        <label for="supplierStatus" class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">
          Status <span class="text-red-500">*</span>
        </label>
        <div class="relative">
          <select
            id="supplierStatus"
            name="status"
            class="w-full appearance-none rounded-lg border border-slate-200 px-3.5 py-2.5 text-sm font-medium text-slate-800 bg-white shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 pr-10 cursor-pointer"
          >
            <option value="1" ${!supplier || supplier.status === "active" || supplier.active == 1 ? "selected" : ""}>Active</option>
            <option value="0" ${supplier && (supplier.status === "inactive" || supplier.active == 0) ? "selected" : ""}>Inactive</option>
          </select>
          
          <!-- Custom Dropdown Chevron Icon -->
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
            <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
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
