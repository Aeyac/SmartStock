<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col justify-between -translate-x-full transition-transform duration-200 ease-in-out md:translate-x-0 md:static md:z-auto md:shrink-0">
    <div>
        <!-- Sidebar Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-bold tracking-tight text-gray-900">SmartStock</span>
            </div>
            <button id="close-sidebar" class="md:hidden text-gray-500 hover:text-gray-700">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1">
            <a href="#"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="layout-grid" class="w-5 h-5"></i>
                <span>Dashboard</span>
            </a>

            <a href="#"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="check-square" class="w-5 h-5"></i>
                <span>Inventory</span>
            </a>

            <a href="#"
                class="flex items-center space-x-3 px-4 py-3 bg-gray-100 text-gray-900 rounded-lg font-semibold text-sm transition">
                <i data-lucide="grid" class="w-5 h-5"></i>
                <span>Suppliers</span>
            </a>

            <a href="#"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                <span>Purchases</span>
            </a>

            <a href="#"
                class="flex items-center space-x-3 px-4 py-3 text-gray-600 rounded-lg hover:bg-gray-50 font-medium text-sm transition">
                <i data-lucide="tag" class="w-5 h-5"></i>
                <span>Sales</span>
            </a>
        </nav>
    </div>
</aside>