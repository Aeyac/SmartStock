import { apiRequest } from "../fetchApi.js";
import { toastSuccess, toastError } from "./controller/toastController.js";
console.log("loaded");
const BASE_URL = "/smart_stock/endpoints/Auth.php";

if (window.lucide) {
  lucide.createIcons();
}

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

// --- REGISTER FORM HANDLING ---
const registerForm =
  document.getElementById("registerForm") || document.querySelector("form");

if (registerForm) {
  registerForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = document.getElementById("registerBtn");
    const originalText = btn ? btn.innerHTML : "Register";
    if (btn) {
      btn.disabled = true;
      btn.innerHTML =
        '<span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>';
    }

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      const result = await registerUser(name, email, password);
      // console.log(result);
      setTimeout(() => {
        if (btn) {
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      }, 1000);
      toastSuccess(result.message);
      registerForm.reset();
      
    } catch (err) {
      if (btn) {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
      toastError(
        err.message || "Registration failed. Please try again.",
        "Register",
      );
    }
  });
}

// login
const loginForm =
  document.getElementById("loginForm") || document.querySelector("form");

if (loginForm && !document.getElementById("name")) {
  loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = document.getElementById("signInBtn");
    const originalText = btn ? btn.innerHTML : "Sign in";
    if (btn) {
      btn.disabled = true;
      btn.innerHTML =
        '<span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>';
    }

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      const result = await loginUser(email, password);

      if (result.status === "error") {
        toastError(
          result.message || "Login failed. Check your credentials.",
          "Login",
        );
        if (btn) {
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
        return;
      }
      window.location.replace("./resources/views/dashboard.php");
    } catch (err) {
      if (btn) {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
      toastError(
        err.message || "Login failed. Check your credentials.",
        "Login",
      );
    }
  });
}

// logout
const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", async () => {
    try {
      await logoutUser();
      window.location.href = "../../index.php";
    } catch (err) {
      toastError("Logout failed.", "Logout");
      // alert(err.message || "Logout failed.");
    }
  });
}

// password visibility toggle
const passwordBtn = document.getElementById("passwordBtn");
if (passwordBtn) {
  passwordBtn.addEventListener("click", () => {
    const input = document.getElementById("password");
    const icon = document.getElementById("passwordIcon");

    if (input && icon) {
      if (input.type === "password") {
        input.type = "text";
        icon.setAttribute("data-lucide", "eye-off");
      } else {
        input.type = "password";
        icon.setAttribute("data-lucide", "eye");
      }

      if (window.lucide) {
        lucide.createIcons();
      }
    }
  });
}
