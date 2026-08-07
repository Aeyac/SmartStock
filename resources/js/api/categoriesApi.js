import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Categories.php";

export function fetchCategories() {
  return apiRequest(BASE_URL, { method: "GET" });
}

export function createCategory(name) {
  return apiRequest(BASE_URL, {
    method: "POST",
    body: JSON.stringify({
      name,
    }),
  });
}

export function updateCategory(id, name) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "PUT",
    body: JSON.stringify({
      name,
    }),
  });
}

export function deleteCategory(id) {
  return apiRequest(BASE_URL, {
    method: "DELETE",
    body: JSON.stringify({
      id,
    }),
  });
}
