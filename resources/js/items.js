import { apiRequest } from "../fetchApi.js";
const ITEMS_URL = "/smart_stock/endpoints/Items.php";

export function fetchItems() {
  return apiRequest(ITEMS_URL);
}
export function fetchLowStockItems() {
  return apiRequest(`${ITEMS_URL}?low_stock=1`);
}
export function createItem(name, categoryId, safetyStock, sellingPrice) {
  return apiRequest(ITEMS_URL, {
    method: "POST",
    body: JSON.stringify({
      name,
      category_id: categoryId,
      safety_stock: safetyStock,
      selling_price: sellingPrice,
    }),
  });
}
export function updateItem(id, name, categoryId, safetyStock, sellingPrice) {
  return apiRequest(`${ITEMS_URL}?id=${id}`, {
    method: "PUT",
    body: JSON.stringify({
      name,
      category_id: categoryId,
      safety_stock: safetyStock,
      selling_price: sellingPrice,
    }),
  });
}
export function deleteItem(id) {
  return apiRequest(`${ITEMS_URL}?id=${id}`, {
    method: "DELETE",
  });
}
