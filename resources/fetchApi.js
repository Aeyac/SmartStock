export async function apiRequest(url, options = {}) {
  const response = await fetch(url, {
    headers: {
      "Content-Type": "application/json",
      ...options.headers,
    },
    ...options,
  });
  const result = await response.json();
  // if (!response.ok) {
  //   throw new Error(result.message || "Request failed.");
  // }
  return result;
}



