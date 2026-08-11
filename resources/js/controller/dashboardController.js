import { fetchDashboard } from "../api/dashboardApi.js";

console.log("loaded");

// ======================
// Helpers
// ======================
function formatCurrency(amount) {
  return Number(amount || 0).toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function formatNumber(amount) {
  return Number(amount || 0).toLocaleString("en-PH");
}

// ======================
// State
// ======================
let cachedBestSelling = { by_quantity: [], by_revenue: [] };
let currentBestSellingMode = "quantity";
let trendsChart = null;

// ======================
// Load Dashboard
// ======================
async function loadDashboard() {
  try {
    const result = await fetchDashboard();

    console.log(result);

    if (result.status !== "success") {
      alert(result.message || "Failed to load dashboard.");
      return;
    }

    renderStats(result.stats);
    renderTrendsChart(result.trends);
    renderLowStock(result.low_stock);

    cachedBestSelling = result.best_selling;
    renderBestSelling();
  } catch (error) {
    console.error(error);
    alert("Unable to load dashboard.");
  }
}

// ======================
// Stat Cards
// ======================
function renderChangeBadge(elementId, pct) {
  const el = document.getElementById(elementId);
  if (!el) return;

  // No previous-month baseline to compare against — showing a fake "+100%"
  // here would be misleading (especially for a value that can be negative,
  // like Net Profit), so show a neutral indicator instead.
  if (pct === null || pct === undefined) {
    el.className = "text-xs font-semibold text-gray-400";
    el.innerHTML = "New";
    return;
  }

  const isPositive = Number(pct) >= 0;
  const icon = isPositive ? "trending-up" : "trending-down";
  const color = isPositive ? "text-emerald-600" : "text-rose-600";
  const sign = isPositive ? "+" : "";

  el.className = `inline-flex items-center gap-0.5 text-xs font-semibold ${color}`;
  el.innerHTML = `<i data-lucide="${icon}" class="w-3 h-3"></i> ${sign}${Number(pct).toFixed(1)}%`;
}

function renderStats(stats) {
  const spendEl = document.getElementById("statMonthlySpend");
  const revenueEl = document.getElementById("statMonthlyRevenue");
  const profitEl = document.getElementById("statNetProfit");

  if (spendEl) spendEl.innerHTML = `₱${formatCurrency(stats.monthly_spend)}`;
  if (revenueEl)
    revenueEl.innerHTML = `₱${formatCurrency(stats.monthly_revenue)}`;
  if (profitEl) profitEl.innerHTML = `₱${formatCurrency(stats.net_profit)}`;

  renderChangeBadge("statMonthlySpendChange", stats.monthly_spend_change_pct);
  renderChangeBadge(
    "statMonthlyRevenueChange",
    stats.monthly_revenue_change_pct,
  );
  renderChangeBadge("statNetProfitChange", stats.net_profit_change_pct);

  if (window.lucide) {
    lucide.createIcons();
  }
}

// ======================
// Performance Trends Chart
// ======================
function renderTrendsChart(trends) {
  const canvas = document.getElementById("trendsChart");
  if (!canvas) return;

  if (typeof Chart === "undefined") {
    console.error(
      "Chart.js did not load — check that the <script> tag for chart.js in dashboard.php loaded successfully (view Network tab / console for a blocked/failed request).",
    );
    return;
  }

  if (trendsChart) {
    trendsChart.destroy();
  }

  const totals = trends.months.map((_, i) => trends.spend[i] + trends.sales[i]);

  trendsChart = new Chart(canvas.getContext("2d"), {
    type: "bar",
    data: {
      labels: trends.months,
      datasets: [
        {
          type: "bar",
          label: "Volume",
          data: totals,
          backgroundColor: "rgba(148, 163, 184, 0.12)",
          borderRadius: 6,
          barPercentage: 0.9,
          categoryPercentage: 0.9,
          order: 3,
        },
        {
          type: "line",
          label: "Spend",
          data: trends.spend,
          borderColor: "#818cf8",
          backgroundColor: "#818cf8",
          borderWidth: 2.5,
          tension: 0.4,
          pointRadius: 0,
          pointHoverRadius: 4,
          order: 1,
        },
        {
          type: "line",
          label: "Sales",
          data: trends.sales,
          borderColor: "#111827",
          backgroundColor: "#111827",
          borderWidth: 2.5,
          tension: 0.4,
          pointRadius: 0,
          pointHoverRadius: 4,
          order: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              if (ctx.dataset.label === "Volume") return null;
              return `${ctx.dataset.label}: ₱${formatCurrency(ctx.parsed.y)}`;
            },
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: "#94a3b8", font: { size: 11, weight: "600" } },
        },
        y: {
          display: false,
        },
      },
    },
  });
}

// ======================
// Low Stock Alerts
// ======================
function renderLowStock(items) {
  const container = document.getElementById("lowStockList");
  if (!container) return;

  container.innerHTML = "";

  if (!items || items.length === 0) {
    container.innerHTML = `
      <p class="text-sm text-gray-400 py-4 text-center">All items are above their safety stock level.</p>
    `;
    return;
  }

  items.forEach((item) => {
    const isOut = item.status === "out";
    const statusText = isOut ? "Out of Stock" : `${item.stock} Left`;
    const statusColor = isOut ? "text-rose-600" : "text-amber-600";

    const row = document.createElement("div");
    row.className =
      "flex items-center justify-between py-3 border-b border-gray-50 last:border-0";

    row.innerHTML = `
      <div>
        <div class="text-sm font-semibold text-gray-900">${item.name}</div>
        <div class="text-[11px] text-gray-400 font-medium">${item.sku}</div>
      </div>
      <span class="text-xs font-bold ${statusColor}">${statusText}</span>
    `;

    container.appendChild(row);
  });
}

// ======================
// Best Selling (toggle: Quantity / Revenue)
// ======================
const qtyToggleBtn = document.getElementById("bestSellingQtyBtn");
const revenueToggleBtn = document.getElementById("bestSellingRevenueBtn");

const toggleActiveClasses = ["bg-white", "text-gray-900", "shadow-sm"];

function setBestSellingMode(mode) {
  currentBestSellingMode = mode;

  if (qtyToggleBtn && revenueToggleBtn) {
    qtyToggleBtn.classList.remove(...toggleActiveClasses);
    revenueToggleBtn.classList.remove(...toggleActiveClasses);

    const activeBtn = mode === "quantity" ? qtyToggleBtn : revenueToggleBtn;
    activeBtn.classList.add(...toggleActiveClasses);
  }

  renderBestSelling();
}

if (qtyToggleBtn) {
  qtyToggleBtn.addEventListener("click", () => setBestSellingMode("quantity"));
}
if (revenueToggleBtn) {
  revenueToggleBtn.addEventListener("click", () =>
    setBestSellingMode("revenue"),
  );
}

const rankStyles = [
  "text-amber-500", // 1st
  "text-gray-400", // 2nd
  "text-orange-700", // 3rd
];

function renderBestSelling() {
  const container = document.getElementById("bestSellingList");
  if (!container) return;

  const items =
    currentBestSellingMode === "quantity"
      ? cachedBestSelling.by_quantity
      : cachedBestSelling.by_revenue;

  container.innerHTML = "";

  if (!items || items.length === 0) {
    container.innerHTML = `
      <p class="text-sm text-gray-400 py-4 text-center">No sales recorded this month yet.</p>
    `;
    return;
  }

  items.forEach((item, index) => {
    const rankLabel = String(index + 1).padStart(2, "0");
    const rankColor = rankStyles[index] || "text-gray-300";

    const valueText =
      currentBestSellingMode === "quantity"
        ? `${formatNumber(item.quantity_sold)} Sold`
        : `₱${formatCurrency(item.revenue)}`;

    const row = document.createElement("div");
    row.className =
      "flex items-center justify-between py-3 border-b border-gray-50 last:border-0";

    row.innerHTML = `
      <div class="flex items-center gap-3">
        <span class="text-sm font-extrabold ${rankColor} w-5">${rankLabel}</span>
        <span class="text-sm font-semibold text-gray-900">${item.name}</span>
      </div>
      <span class="text-xs font-bold text-gray-700">${valueText}</span>
    `;

    container.appendChild(row);
  });
}

function renderLowStockBanner(lowStockItems) {
  const banner = document.getElementById("lowStockBanner");
  const text = document.getElementById("lowStockBannerText");
  if (!banner || !text) return;

  if (lowStockItems.length === 0) {
    banner.classList.add("hidden");
    return;
  }

  text.innerHTML = `${lowStockItems.length} item(s) low on stock: ${lowStockItems
    .map((i) => i.name)
    .slice(0, 3)
    .join(", ")}${lowStockItems.length > 3 ? "..." : ""}`;
  banner.classList.remove("hidden");
  if (window.lucide) lucide.createIcons();
}

// Initialize on load
loadDashboard();
renderLowStockBanner();