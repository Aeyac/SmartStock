import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Dashboard.php";

export function fetchDashboard() {
  return apiRequest(BASE_URL);
}
