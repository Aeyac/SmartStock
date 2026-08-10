<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col justify-between -translate-x-full transition-transform duration-200 ease-in-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:z-auto lg:shrink-0">
    <div>
        <!-- Sidebar Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-bold tracking-tight text-gray-900">SmartStock</span>
            </div>

            <button id="close-sidebar" class="lg:hidden text-gray-500 hover:text-gray-700 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1">
            <a id="dashboardNav" href="./dashboard.php"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="layout-grid" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <a id="categoriesNav" href="./categories.php"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>Categories</span>
            </a>

            <a id="inventoryNav" href="./items.php"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span>Inventory</span>
            </a>

            <a id="supplierNav" href="./suppliers.php"
                class="flex items-center space-x-3 px-4 py-3 text-gray-900 rounded-lg font-semibold text-sm transition">
                <i data-lucide="grid" class="w-5 h-5"></i>
                <span>Suppliers</span>
            </a>

            <a id="purchasesNav" href="./purchases.php"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span>Purchases</span>
            </a>

            <a id="salesrNav" href="./sales.php"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="tag" class="w-5 h-5"></i>
                <span>Sales</span>
            </a>

            <a id="stockledgerNav" href="./stockledger.php"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="book-open" class="w-5 h-5"></i>
                <span>Stock Ledger</span>
            </a>
        </nav>
    </div>

    <!-- Pinned Footer Section for Log Out -->
    <div class="p-4 border-t border-gray-100">
        <a id="logoutBtn"
            class="flex items-center space-x-3 px-4 py-3 bg-red-50/50 text-red-600 rounded-lg hover:bg-red-100 hover:text-red-700 font-semibold text-sm transition duration-150 cursor-pointer">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span>Log Out</span>
        </a>
    </div>
</aside>

<!-- Backdrop shown behind the sidebar whenever it's open, on any screen size -->
<div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/40 z-40"></div>

<script>
    // Get the current page file name from the URL path
    const currentPage = window.location.pathname.split("/").pop();

    // Map your PHP file names to the corresponding navigation link IDs
    const navMapping = {
        'dashboard.php': 'dashboardNav',
        'categories.php': 'categoriesNav',
        'items.php': 'inventoryNav',
        'suppliers.php': 'supplierNav',
        'purchases.php': 'purchasesNav',
        'sales.php': 'salesrNav',
        'stockledger.php': 'stockledgerNav'
    };

    // Get the ID of the link that should be active
    const activeLinkId = navMapping[currentPage];

    if (activeLinkId) {
        // Target the active link element
        const activeLink = document.getElementById(activeLinkId);

        if (activeLink) {
            // Remove the default inactive styling
            activeLink.classList.remove('text-gray-600', 'hover:bg-gray-50', 'font-medium');
            activeLink.classList.add('bg-gray-100', 'text-gray-900', 'font-semibold');
        }
    }
</script>

<script type="module" src="../../resources/js/authController.js"></script>