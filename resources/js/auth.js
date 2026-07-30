import { apiRequest } from "../fetchApi.js";
console.log("loaded");


async function registerUser(name, email, password) {
  const data = { name: name, email: email, password: password };
  return await apiRequest("auth", "register", "POST", data);
}

async function loginUser(email, password) {
  const data = { email: email, password: password };
  return await apiRequest("auth", "login", "POST", data);
}

async function logoutUser() {
  return await apiRequest("auth", "logout", "POST", null);
}


function setLoadingState(btn, text = "Processing...") {
  const originalHTML = btn.innerHTML;
  btn.innerHTML = `<span class="material-symbols-outlined animate-spin">progress_activity</span> ${text}`;
  btn.style.pointerEvents = "none";
  btn.style.opacity = "0.8";
  return originalHTML;
}

function restoreButtonState(btn, originalHTML) {
  btn.innerHTML = originalHTML;
  btn.style.pointerEvents = "auto";
  btn.style.opacity = "1";
  btn.classList.remove("success");
}



const registerForm =
  document.getElementById("registerForm") || document.querySelector("form");

if (registerForm && document.getElementById("name")) {
  registerForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = e.target.querySelector('button[type="submit"]');
    const originalBtnHTML = setLoadingState(btn, "Creating Account...");

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      await registerUser(name, email, password);

      // Success visual feedback
      btn.innerHTML =
        'Account Created <span class="material-symbols-outlined">check_circle</span>';
      btn.classList.add("success");

      registerForm.reset();

      setTimeout(() => {
        window.location.href = "/smartstock/index.php";
      }, 1500);
    } catch (err) {
      alert(err.message || "Registration failed. Please try again.");
      restoreButtonState(btn, originalBtnHTML);
    }
  });
}

const loginForm =
  document.getElementById("loginForm") || document.querySelector("form");

if (loginForm && !document.getElementById("name")) {
  loginForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const btn = e.target.querySelector('button[type="submit"]');
    const originalBtnHTML = setLoadingState(btn, "Authenticating...");

    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    try {
      const result = await loginUser(email, password);
      console.log(result);

      btn.innerHTML =
        'Success <span class="material-symbols-outlined">check_circle</span>';
      btn.classList.add("success");
      console.log("logged in");
    } catch (err) {
      alert(err.message || "Login failed. Check your credentials.");
      restoreButtonState(btn, originalBtnHTML);
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
