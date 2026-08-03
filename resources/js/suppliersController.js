import {
  fetchSuppliers,
  createSupplier,
  updateSupplier,
  deleteSupplier,
} from "./api/suppliersApi.js";
import { openModal, closeModal } from "./modalController.js";

console.log("loaded");

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
      <div class="flex flex-col gap-4">
        <input
          id="supplierName"
          class="w-full rounded border p-2"
          placeholder="Supplier Name"
          value="${supplier ? supplier.name : ""}"
        >

        <input
          id="supplierContact"
          class="w-full rounded border p-2"
          placeholder="Contact Number"
          value="${supplier ? supplier.contact_number : ""}"
        >

        <input
          id="supplierEmail"
          class="w-full rounded border p-2"
          placeholder="Email"
          value="${supplier ? supplier.email : ""}"
        >
      </div>
    `,

    footerHTML: `
      <button
        id="cancelModalBtn"
        class="cursor-pointer rounded px-4 py-2 hover:bg-gray-100"
      >
        Cancel
      </button>

      <button
        id="saveSupplierBtn"
        class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 cursor-pointer"
      >
        Save
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

// Save (Create or Update) Handler
async function saveSupplier(id = null) {
  const name = document.getElementById("supplierName").value.trim();
  const contact_number = document
    .getElementById("supplierContact")
    .value.trim();
  const email = document.getElementById("supplierEmail").value.trim();

  if (!name || !contact_number || !email) {
    alert("Please fill in all fields.");
    return;
  }

  try {
    let result;
    if (id) {
      result = await updateSupplier(id, name, contact_number, email);
    } else {
      result = await createSupplier(name, contact_number, email);
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
            Try adjusting your search or filter.
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
    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/80 transition group";

    tr.innerHTML = `
      <td class="py-3.5 px-4 sm:px-6">
          <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
              ${supplier.name}</div>
          <div class="text-[11px] text-gray-400 font-medium">ID: SUP-${supplier.id}</div>
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-gray-600 font-medium">${supplier.contact_number}</td>
      <td class="py-3.5 px-4 sm:px-6">
          <a href="mailto:${supplier.email}"
              class="text-blue-600 hover:underline font-medium">${supplier.email}</a>
      </td>
      <td class="py-3.5 px-4 sm:px-6">
          <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              Active
          </span>
      </td>
      <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
          <div class="flex items-center justify-end space-x-1">
              <button title="Edit Supplier"
                  class="edit-btn p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition cursor-pointer">
                  <i data-lucide="pencil" class="w-4 h-4"></i>
              </button>

              <button title="Delete Supplier"
                  class="delete-btn p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
          </div>
      </td>
    `;

    // Bind Edit event listener
    tr.querySelector(".edit-btn").addEventListener("click", () => {
      openSupplierModal({ title: "Edit Supplier", supplier });
    });

    // Bind Delete event listener
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
// Filter Button Toggle
// ======================
// const filterBtns = [
//   document.getElementById("allButton"),
//   document.getElementById("activeBtn"),
//   document.getElementById("inactiveBtn"),
// ];

// const activeClasses = ["bg-white", "text-gray-900", "shadow-sm"];

// filterBtns.forEach((selectedBtn) => {
//   if (!selectedBtn) return;
//   selectedBtn.addEventListener("click", () => {
//     filterBtns.forEach((btn) => btn && btn.classList.remove(...activeClasses));
//     selectedBtn.classList.add(...activeClasses);
//   });
// });

// Initialize on load
loadSuppliers();
