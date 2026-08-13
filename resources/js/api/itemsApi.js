import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Items.php";

export function fetchItems() {
  return apiRequest(BASE_URL, { method: "GET" });
}

export function fetchDeletedItems() {
  return apiRequest(`${BASE_URL}?trashed=1`, { method: "GET" });
}

export function createItem(name, category_id, safety_stock, selling_price) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({
      name,
      category_id: Number(category_id),
      // supplier_id: Number(supplier_id),
      safety_stock: Number(safety_stock),
      selling_price: Number(selling_price),
    }),
  });
}

export function updateItem(id, name, category_id, safety_stock, selling_price) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "PUT",
    body: JSON.stringify({
      name,
      category_id: Number(category_id),
      // supplier_id: Number(supplier_id),
      safety_stock: Number(safety_stock),
      selling_price: Number(selling_price),
    }),
  });
}

export function deleteItem(id) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "DELETE",
  });
}

export function restoreItem(id) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "PATCH",
  });
}
