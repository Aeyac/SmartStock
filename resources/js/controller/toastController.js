// ======================
// Reusable Toast Notifications + Confirmations
// ======================
// Two things live here:
//
// 1. Notification toasts — drop-in replacement for alert() on
//    create/update/delete/restore results. Slides in from the
//    bottom-right, stacking upward, and auto-dismisses.
//
//      import { toastSuccess, toastError, toastWarning, toastInfo } from "./toastController.js";
//      toastSuccess("Supplier created successfully.");
//      toastError("This category already exists.");
//
// 2. Confirmation toast — drop-in replacement for confirm(). Pops in at
//    the center of the screen over a dimmed backdrop, since it's gating
//    an action rather than just informing. Unlike the browser's
//    confirm(), this doesn't block the page, so calling code needs to
//    `await` it:
//
//      const confirmed = await confirmToast({
//        title: "Archive this category?",
//        message: "You can restore it later from the Archived tab.",
//        confirmText: "Archive",
//        danger: true,
//      });
//      if (!confirmed) return;
//
// All animation is done with plain Tailwind utility classes toggled via
// JS (the same -translate-x-full <-> translate-x-0 technique your
// sidebar already uses) — nothing here is raw injected CSS, so your
// Tailwind CLI build compiles it normally.

const TOAST_CONTAINER_ID = "toast-container";
const CONFIRM_OVERLAY_ID = "confirm-overlay";
const DEFAULT_DURATION = 4000;

// Matches the badge color patterns already used across the app
// (suppliers' active/inactive badges, ledger type badges, etc.)
const TOAST_STYLES = {
  success: {
    icon: "check-circle-2",
    iconBg: "bg-emerald-50",
    iconColor: "text-emerald-600",
  },
  error: { icon: "x-circle", iconBg: "bg-rose-50", iconColor: "text-rose-600" },
  warning: {
    icon: "alert-triangle",
    iconBg: "bg-amber-50",
    iconColor: "text-amber-600",
  },
  info: { icon: "info", iconBg: "bg-blue-50", iconColor: "text-blue-600" },
};

const DEFAULT_TITLES = {
  success: "Success",
  error: "Something went wrong",
  warning: "Heads up",
  info: "Notice",
};

// ----------------------
// Notification container (bottom-right, stacks upward)
// ----------------------
function ensureContainer() {
  let container = document.getElementById(TOAST_CONTAINER_ID);
  if (container) return container;

  container = document.createElement("div");
  container.id = TOAST_CONTAINER_ID;
  container.className =
    "fixed bottom-4 right-4 z-[9999] flex flex-col-reverse gap-3 items-end";
  document.body.appendChild(container);

  return container;
}

// Slide-in-from-bottom-right animation for notification toasts.
function animateIn(card) {
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      card.classList.remove("opacity-0", "translate-x-8", "translate-y-4");
      card.classList.add("opacity-100", "translate-x-0", "translate-y-0");
    });
  });
}

function animateOutAndRemove(card, onDone) {
  card.classList.remove("opacity-100", "translate-x-0", "translate-y-0");
  card.classList.add("opacity-0", "translate-x-8");
  card.addEventListener(
    "transitionend",
    () => {
      card.remove();
      if (onDone) onDone();
    },
    { once: true },
  );
}

// ----------------------
// Notification Toast
// ----------------------
export function showToast({
  type = "info",
  title = null,
  message = "",
  duration = DEFAULT_DURATION,
} = {}) {
  const style = TOAST_STYLES[type] || TOAST_STYLES.info;
  const container = ensureContainer();

  const toast = document.createElement("div");
  toast.className =
    "w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-gray-200 shadow-lg p-4 " +
    "transform transition-all duration-300 ease-out opacity-0 translate-x-8 translate-y-4";

  toast.innerHTML = `
    <div class="flex items-start gap-3">
      <div class="w-8 h-8 rounded-full ${style.iconBg} ${style.iconColor} flex items-center justify-center shrink-0">
        <i data-lucide="${style.icon}" class="w-4 h-4"></i>
      </div>

      <div class="flex-1 min-w-0">
        <p class="text-sm font-bold text-gray-900">${title || DEFAULT_TITLES[type]}</p>
        ${message ? `<p class="text-xs text-gray-500 font-medium mt-0.5 break-words">${message}</p>` : ""}
      </div>

      <button class="toast-close-btn shrink-0 text-gray-300 hover:text-gray-500 transition cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>
  `;

  container.appendChild(toast);

  if (window.lucide) {
    lucide.createIcons();
  }

  animateIn(toast);

  let dismissTimer = null;

  function dismiss() {
    if (dismissTimer) clearTimeout(dismissTimer);
    animateOutAndRemove(toast);
  }

  toast.querySelector(".toast-close-btn").addEventListener("click", dismiss);

  if (duration > 0) {
    dismissTimer = setTimeout(dismiss, duration);
  }

  return dismiss;
}

export function toastSuccess(message, title = null) {
  return showToast({ type: "success", title, message });
}

export function toastError(message, title = null) {
  return showToast({ type: "error", title, message });
}

export function toastWarning(message, title = null) {
  return showToast({ type: "warning", title, message });
}

export function toastInfo(message, title = null) {
  return showToast({ type: "info", title, message });
}

// ----------------------
// Confirmation Toast (center pop, dimmed backdrop)
// ----------------------
// Returns a Promise<boolean> — true if the user clicked Confirm, false if
// they clicked Cancel (or clicked the backdrop). Unlike confirm(), it
// doesn't freeze the page while waiting, so calling code must `await` it
// (or use .then()).
export function confirmToast({
  title = "Are you sure?",
  message = "",
  confirmText = "Confirm",
  cancelText = "Cancel",
  danger = false,
} = {}) {
  return new Promise((resolve) => {
    // Overlay dims the page and centers the confirmation card. Each call
    // gets its own overlay so stacked confirmations (rare, but possible)
    // don't clobber each other.
    const overlay = document.createElement("div");
    overlay.className =
      "fixed inset-0 z-[9999] flex items-center justify-center p-4 " +
      "bg-gray-900/40 backdrop-blur-[2px] " +
      "transition-opacity duration-200 ease-out opacity-0";
    overlay.id = CONFIRM_OVERLAY_ID;

    const card = document.createElement("div");
    card.className =
      "w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-gray-200 shadow-2xl p-4 " +
      "transform transition-all duration-200 ease-out opacity-0 scale-95";

    const iconBg = danger ? "bg-rose-50" : "bg-amber-50";
    const iconColor = danger ? "text-rose-600" : "text-amber-600";
    const confirmBtnClasses = danger
      ? "bg-rose-600 hover:bg-rose-700"
      : "bg-black hover:bg-gray-800";

    card.innerHTML = `
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-full ${iconBg} ${iconColor} flex items-center justify-center shrink-0">
          <i data-lucide="alert-triangle" class="w-4 h-4"></i>
        </div>

        <div class="flex-1 min-w-0">
          <p class="text-sm font-bold text-gray-900">${title}</p>
          ${message ? `<p class="text-xs text-gray-500 font-medium mt-0.5">${message}</p>` : ""}
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 mt-3">
        <button class="confirm-cancel-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
          ${cancelText}
        </button>
        <button class="confirm-ok-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-white ${confirmBtnClasses} transition cursor-pointer">
          ${confirmText}
        </button>
      </div>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    if (window.lucide) {
      lucide.createIcons();
    }

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        overlay.classList.remove("opacity-0");
        overlay.classList.add("opacity-100");
        card.classList.remove("opacity-0", "scale-95");
        card.classList.add("opacity-100", "scale-100");
      });
    });

    function respond(result) {
      overlay.classList.remove("opacity-100");
      overlay.classList.add("opacity-0");
      card.classList.remove("opacity-100", "scale-100");
      card.classList.add("opacity-0", "scale-95");

      overlay.addEventListener(
        "transitionend",
        () => {
          overlay.remove();
          resolve(result);
        },
        { once: true },
      );
    }

    // Backdrop click cancels, same as most confirm dialogs.
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) respond(false);
    });

    card
      .querySelector(".confirm-cancel-btn")
      .addEventListener("click", () => respond(false));

    card
      .querySelector(".confirm-ok-btn")
      .addEventListener("click", () => respond(true));
  });
}
