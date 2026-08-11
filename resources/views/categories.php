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
    <title>SmartStock - Categories</title>

    <link rel="stylesheet" href="../../src/output.css" />

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 antialiased min-h-screen flex flex-col md:flex-row">
    <?php require_once "partials/modal.php" ?>
    <?php require_once "partials/sidebar.php" ?>


    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <?php require_once "partials/topbar.php" ?>

        <!-- Page Body -->
        <main class="p-4 sm:p-6 md:p-8 space-y-6 max-w-7xl w-full mx-auto flex-1">

            <!-- Title & Action Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Categories</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Organize and manage product categories for better
                        inventory classification</p>
                </div>

                <!-- Controls: Stat Counter + Primary Button -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                    <div
                        class="flex justify-around bg-white border border-gray-200 rounded-xl px-4 py-2 divide-x divide-gray-200 shadow-sm">
                        <div class="pr-4 sm:pr-6 text-center sm:text-left">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">TOTAL
                                CATEGORIES</span>
                            <span id="totalCategories" class="text-base sm:text-lg font-bold text-gray-900"></span>
                        </div>
                    </div>

                    <button id="addBtn"
                        class="bg-black text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center space-x-2 hover:bg-gray-800 transition shadow-sm active:scale-95 cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Category</span>
                    </button>
                </div>
            </div>

            <!-- Table Container Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">

                <!-- Controls Bar: Search & View Tabs -->
                <div
                    class="p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-b border-gray-100">
                    <div class="relative w-full sm:w-72">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input id="searchInput" type="text" placeholder="Search Categories..."
                            class="w-full bg-gray-100 text-sm rounded-lg pl-10 pr-4 py-2 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-2 focus:ring-black/5 outline-none transition placeholder-gray-400" />
                    </div>

                    <div class="flex items-center justify-between sm:justify-end space-x-2">
                        <div
                            class="inline-flex bg-gray-100 p-1 rounded-lg text-xs font-semibold text-gray-600 flex-1 sm:flex-initial justify-between">
                            <button id="allCategoriesBtn"
                                class="px-3 py-1.5 rounded-md bg-white text-gray-900 shadow-sm flex-1 sm:flex-none text-center cursor-pointer">
                                All
                            </button>
                            <button id="archivedCategoriesBtn"
                                class="px-3 py-1.5 rounded-md hover:text-gray-900 transition flex-1 sm:flex-none text-center cursor-pointer">
                                Archived
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table View (desktop) -->
                <div class="hidden md:block max-h-[500px] overflow-y-auto overflow-x-auto">
                    <table id="categoriesTable" class="w-full min-w-[700px] border-collapse">
                        <thead class="sticky top-0 bg-gray-50 z-10">
                            <tr
                                class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold tracking-wider text-gray-500 uppercase select-none">
                                <th class="py-3.5 px-4 sm:px-6 text-left cursor-pointer hover:text-gray-800 transition">
                                    <span>CATEGORY NAME</span>
                                </th>
                                <th class="py-3.5 px-4 sm:px-6 text-left">TOTAL ITEMS</th>
                                <th class="py-3.5 px-4 sm:px-6 text-left">CREATED AT</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody" class="divide-y divide-gray-100 text-xs sm:text-sm">
                        </tbody>
                    </table>
                </div>

                <!-- Card View (mobile) -->
                <div id="categoriesCardList" class="md:hidden max-h-[500px] overflow-y-auto divide-y divide-gray-100">
                </div>

                <div
                    class="p-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500 font-medium">
                    <div>
                        Showing <span id="showingCount" class="font-semibold text-gray-900">0</span>
                        out of
                        <span id="totalCount" class="font-semibold text-gray-900"></span>
                        categories
                    </div>
                </div>
            </div>

        </main>


    </div>

    <script type="module" src="../js/controller/categoriesController.js"></script>


</body>

</html>