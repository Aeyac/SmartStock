import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Sales.php";

export function fetchSales() {
  return apiRequest(BASE_URL);
}

export function getSale(id) {
  return apiRequest(`${BASE_URL}?id=${id}`);
}

export function createSale(saleDate, items) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({
      sale_date: saleDate,
      items,
    }),
  });
}
