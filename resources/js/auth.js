import { apiRequest } from "../fetchApi.js";
const BASE_URL = "/smart_stock/endpoints/Auth.php";

export function registerUser(name, email, password) {
  return apiRequest(`${BASE_URL}?type=register`, {
    method: "POST",
    body: JSON.stringify({ name, email, password }),
  });
}
export function loginUser(email, password) {
  return apiRequest(`${BASE_URL}?type=login`, {
    method: "POST",
    body: JSON.stringify({ email, password }),
  });
}
export function logoutUser() {
  return apiRequest(BASE_URL, {
    method: "DELETE",
  });
}

// const registerForm =
//   document.getElementById("registerForm") || document.querySelector("form");

// if (registerForm && document.getElementById("name")) {
//   registerForm.addEventListener("submit", async function (e) {
//     e.preventDefault();

//     const name = document.getElementById("name").value;
//     const email = document.getElementById("email").value;
//     const password = document.getElementById("password").value;

//     try {
//       await registerUser(name, email, password);

//       registerForm.reset();
//     } catch (err) {
//       alert(err.message || "Registration failed. Please try again.");
//     }
//   });
// }

// const loginForm =
//   document.getElementById("loginForm") || document.querySelector("form");

// if (loginForm && !document.getElementById("name")) {
//   loginForm.addEventListener("submit", async function (e) {
//     e.preventDefault();

//     const email = document.getElementById("email").value;
//     const password = document.getElementById("password").value;

//     try {
//       const result = await loginUser(email, password);
//       console.log(result);

//       console.log("logged in");
//     } catch (err) {
//       alert(err.message || "Login failed. Check your credentials.");
//     }
//   });
// }

// const logoutBtn = document.getElementById("logoutBtn");
// if (logoutBtn) {
//   logoutBtn.addEventListener("click", async function () {
//     try {
//       await logoutUser();
//       window.location.href = "index.php";
//     } catch (err) {
//       alert(err.message || "Logout failed.");
//     }
//   });
// }
