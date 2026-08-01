import { apiRequest } from "../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Purchases.php";

export function fetchPurchases() {
  return apiRequest(BASE_URL);
}
export function getPurchase(id) {
  return apiRequest(`${BASE_URL}?id=${id}`);
}
// items: array of { item_id, quantity, unit_cost }
export function createPurchase(supplierId, purchaseDate, items) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({
      supplier_id: supplierId,
      purchase_date: purchaseDate,
      items,
    }),
  });
}
