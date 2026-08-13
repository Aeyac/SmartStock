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

// soft delete only
export function deleteCategory(id) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "DELETE",
  });
}

// Restores a previously archived category (clears deleted_at).
export function restoreCategory(id) {
  return apiRequest(`${BASE_URL}?id=${id}`, {
    method: "PATCH",
  });
}