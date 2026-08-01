import { apiRequest } from "../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/StockLedger.php";

export function fetchStockLedger(itemId) {
  return apiRequest(`${BASE_URL}?item_id=${itemId}`);
}
