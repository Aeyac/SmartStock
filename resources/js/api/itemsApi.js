import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Items.php";

export function fetchItems(lowStock = false) {
  const url = lowStock ? `${BASE_URL}?low_stock=1` : BASE_URL;
  return apiRequest(url, { method: "GET" });
}

export function createItem(name, category_id, supplier_id, safety_stock, selling_price) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({
      name,
      category_id: Number(category_id),
      supplier_id: Number(supplier_id),
      safety_stock: Number(safety_stock),
      selling_price: Number(selling_price),
    }),
  });
}

export function updateItem(id, name, category_id, supplier_id, safety_stock, selling_price) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "PUT",
    body: JSON.stringify({
      name,
      category_id: Number(category_id),
      supplier_id: Number(supplier_id),
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

// export function fetchItems() {
//   return apiRequest(ITEMS_URL);
// }

// export function fetchLowStockItems() {
//   return apiRequest(`${ITEMS_URL}?low_stock=1`);
// }
