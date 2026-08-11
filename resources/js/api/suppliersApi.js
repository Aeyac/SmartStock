import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Suppliers.php";

export function fetchSuppliers() {
  return apiRequest(BASE_URL, { method: "GET" });
}

export function createSupplier(name, contact_number, email, status) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({ name, contact_number, email, status }),
  });
}

export function updateSupplier(id, name, contact_number, email, status) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "PUT",
    body: JSON.stringify({ name, contact_number, email, status }),
  });
}

// Archives (soft deletes) a supplier — it's excluded from fetchSuppliers()
// afterward but can be brought back with restoreSupplier().
export function deleteSupplier(id) {
  return apiRequest(`${BASE_URL}?id=${id}`, { method: "DELETE" });
}

export function restoreSupplier(id) {
  return apiRequest(`${BASE_URL}?id=${id}`, { method: "PATCH" });
}
