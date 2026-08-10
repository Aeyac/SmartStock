import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Stockledger.php";

export function fetchLedger() {
  return apiRequest(BASE_URL);
}

// Records a manual 'adjustment' entry only — purchase/sale ledger rows are
// always created automatically by createPurchase()/createSale().
export function createAdjustment(itemId, quantityChange) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({
      item_id: itemId,
      quantity_change: quantityChange,
    }),
  });
}