
export async function apiRequest(controller, action, method, data) {
  const url =
    "/smartstock/routes.php?controller=" + controller + "&action=" + action;

  const response = await fetch(url, {
    method: method,
    headers: {
      "Content-Type": "application/json",
    },
    credentials: "same-origin", // sends the login session cookie with the request
    body: data ? JSON.stringify(data) : null,
  });

  const result = await response.json();

  // If something went wrong on the server, throw the error message
  // so whoever called this function can show it to the user.
  if (!response.ok) {
    throw new Error(result.message);
  }

  return result;
}
