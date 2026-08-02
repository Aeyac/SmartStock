import { apiRequest } from "../fetchApi.js";
console.log("loaded");
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

const registerForm =
  document.getElementById("registerForm") || document.querySelector("form");

if (registerForm && document.getElementById("name")) {
  registerForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = document.getElementById("registerBtn");
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>';

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      const result = await registerUser(name, email, password);
      console.log(result);
      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }, 1000);

      registerForm.reset();
    } catch (err) {
      btn.innerHTML = originalText;
      btn.disabled = false;

      alert(err.message || "Registration failed. Please try again.");
    }
  });
}

const loginForm =
  document.getElementById("loginForm") || document.querySelector("form");

if (loginForm && !document.getElementById("name")) {
  loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = document.getElementById("signInBtn");
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>';

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      const result = await loginUser(email, password);

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }, 1000);

      console.log(result);
    } catch (err) {
      btn.innerHTML = originalText;
      btn.disabled = false;

      alert(err.message || "Login failed. Check your credentials.");
    }
  });
}

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", async function () {
    try {
      await logoutUser();
      window.location.href = "index.php";
    } catch (err) {
      alert(err.message || "Logout failed.");
    }
  });
}


const passwordBtn = document.getElementById("passwordBtn");
passwordBtn.addEventListener("click", () => {
  const input = document.getElementById("password");
  const icon = document.getElementById("passwordIcon");

  if (input.type === "password") {
    input.type = "text";
    icon.textContent = "visibility_off";
  } else {
    input.type = "password";
    icon.textContent = "visibility";
  }
});
