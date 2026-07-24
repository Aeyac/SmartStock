/**
 * This file talks to routes.php using fetch() — this IS the AJAX part
 * your professor asked for. No page reloads, just background requests.
 *
 * Every request follows the same 3 steps:
 *   1. Build the URL: routes.php?controller=...&action=...
 *   2. Send the data (name, email, password, etc.) as JSON
 *   3. Read back the JSON response and use it
 */

// One shared function that every module (auth, items, sales...) can reuse.
// You just change controller, action, method, and the data being sent.
export async function apiRequest(controller, action, method, data) {
  const url =
    "/SmartStock/routes.php?controller=" + controller + "&action=" + action;

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
