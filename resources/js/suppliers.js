import { apiRequest } from "../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Suppliers.php";
export function fetchSuppliers() {
  return apiRequest(BASE_URL);
}
export function createSupplier(name, contactNumber, email) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({
      name,
      contact_number: contactNumber,
      email,
    }),
  });
}
export function updateSupplier(id, name, contactNumber, email) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "PUT",
    body: JSON.stringify({
      name,
      contact_number: contactNumber,
      email,
    }),
  });
}
export function deleteSupplier(id) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "DELETE",
  });
}
