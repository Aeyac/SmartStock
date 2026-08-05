export async function apiRequest(url, options = {}) {
  const response = await fetch(url, {
    headers: {
      "Content-Type": "application/json",
    },
    ...options,
  });

  if (response.status === 401) {
    window.location.replace("/smart_stock/index.php");
    throw new Error("Unauthorized access. Redirecting...");
  }

  const result = await response.json();

  if (!response.ok) {
    throw new Error(result.message || "Request failed.");
  }

  return result;
}
