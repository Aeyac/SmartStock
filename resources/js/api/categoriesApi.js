import { apiRequest } from "../../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Categories.php";

export function fetchCategories() {
  return apiRequest(BASE_URL, { method: "GET" });
}

// Archived (soft-deleted) categories — for a "restore from archive" view.
export function fetchArchivedCategories() {
  return apiRequest(`${BASE_URL}?archived=1`, { method: "GET" });
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

// Archives (soft deletes) a category — id goes in the query string, since
// the endpoint reads $_GET['id'] for this action, not the request body.
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
