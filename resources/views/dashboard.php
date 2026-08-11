<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /smart_stock/index.php");
    session_destroy();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SmartStock - Dashboard</title>

    <link rel="stylesheet" href="../../src/output.css" />

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 antialiased min-h-screen flex flex-col md:flex-row">
    <?php require_once "partials/modal.php" ?>
    <?php require_once "partials/sidebar.php" ?>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <?php require_once "partials/topbar.php" ?>

        <div id="lowStockBanner"
            class="hidden bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium rounded-xl px-4 py-3 flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            <span id="lowStockBannerText"></span>
        </div>
        <!-- Page Body -->
        <main class="p-4 sm:p-6 md:p-8 space-y-6 max-w-7xl w-full mx-auto flex-1">

            <!-- Title -->
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">A quick overview of your inventory movement, spending,
                    and sales performance for this month.</p>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Monthly
                        Spend</span>
                    <div class="flex items-end justify-between">
                        <span id="statMonthlySpend" class="text-2xl font-bold text-gray-900">₱0.00</span>
                        <span id="statMonthlySpendChange"
                            class="inline-flex items-center gap-0.5 text-xs font-semibold text-gray-400"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Monthly
                        Revenue</span>
                    <div class="flex items-end justify-between">
                        <span id="statMonthlyRevenue" class="text-2xl font-bold text-gray-900">₱0.00</span>
                        <span id="statMonthlyRevenueChange"
                            class="inline-flex items-center gap-0.5 text-xs font-semibold text-gray-400"></span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Net
                        Profit</span>
                    <div class="flex items-end justify-between">
                        <span id="statNetProfit" class="text-2xl font-bold text-gray-900">₱0.00</span>
                        <span id="statNetProfitChange"
                            class="inline-flex items-center gap-0.5 text-xs font-semibold text-gray-400"></span>
                    </div>
                </div>
            </div>

            <!-- Performance Trends -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Performance Trends</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Monthly Performance: Spend vs Sales</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold text-gray-600">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-400"></span> Spend
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-gray-900"></span> Sales
                        </span>
                    </div>
                </div>

                <!-- Scrollable sideways so all 12 months fit without squeezing -->
                <div class="overflow-x-auto">
                    <div style="min-width: 900px; height: 320px; position: relative;">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alerts + Best Selling -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Low Stock Alerts -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Low Stock Alerts</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Immediate attention required</p>
                        </div>
                        <a href="./items.php"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 border border-gray-200 rounded-lg px-3 py-1.5 transition">
                            <span>View Items</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>

                    <div id="lowStockList" class="flex flex-col"></div>
                </div>

                <!-- Best Selling -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-bold text-gray-900">Best Selling</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Top performers this month</p>
                        </div>

                        <div class="inline-flex bg-gray-100 p-1 rounded-lg text-xs font-semibold text-gray-600">
                            <button id="bestSellingQtyBtn"
                                class="px-3 py-1.5 rounded-md bg-white text-gray-900 shadow-sm cursor-pointer">
                                Quantity
                            </button>
                            <button id="bestSellingRevenueBtn"
                                class="px-3 py-1.5 rounded-md hover:text-gray-900 transition cursor-pointer">
                                Revenue
                            </button>
                        </div>
                    </div>

                    <div id="bestSellingList" class="flex flex-col"></div>
                </div>

            </div>

        </main>
    </div>

    <script type="module" src="../js/controller/dashboardController.js"></script>

</body>

</html>