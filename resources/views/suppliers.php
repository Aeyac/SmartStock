<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SmartStock - Suppliers</title>

    <!-- Compiled Tailwind CSS via CLI -->
    <link rel="stylesheet" href="../../src/output.css" />

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Top Bar Header -->
    <header
        class="md:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-30">
        <div class="flex items-center space-x-3">
            <button id="open-sidebar" class="text-gray-600 hover:text-gray-900 focus:outline-none">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <div class="flex items-center space-x-2">
                <span class="text-lg font-bold tracking-tight text-gray-900">SmartStock</span>
            </div>
        </div>

        <!-- Mobile User Profile Icon -->
        <div class="w-8 h-8 rounded-full bg-black text-white flex items-center justify-center">
            <i data-lucide="user" class="w-4 h-4"></i>
        </div>
    </header>

    <!-- Mobile Overlay Backdrop -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <!-- Sidebar -->
    <?php require_once "partials/sidebar.php" ?>


    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        <!-- Desktop Header Bar -->
        <header
            class="hidden md:flex h-16 bg-white border-b border-gray-200 items-center justify-end px-8 space-x-6 shrink-0">
            <button class="text-gray-500 hover:text-gray-700 relative">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <div class="h-6 w-px bg-gray-200"></div>

            <!-- User Info -->
            <div class="flex items-center space-x-3">
                <div class="text-right">
                    <div class="text-sm font-bold text-gray-900 leading-tight">Admin User</div>
                    <div class="text-xs text-gray-400 font-medium tracking-wider">SUPER ADMIN</div>
                </div>
                <div class="w-9 h-9 rounded-full bg-black text-white flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </div>
            </div>
        </header>

        <!-- Page Body -->
        <main class="p-4 sm:p-6 md:p-8 space-y-6 max-w-7xl w-full mx-auto flex-1">

            <!-- Title & Action Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Suppliers</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage your global procurement network and vendor
                        contacts</p>
                </div>

                <!-- Controls: Stat Counter + Primary Button -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                    <div
                        class="flex justify-around bg-white border border-gray-200 rounded-xl px-4 py-2 divide-x divide-gray-200 shadow-sm">
                        <div class="pr-4 sm:pr-6 text-center sm:text-left">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">TOTAL
                                SUPPLIERS</span>
                            <span class="text-base sm:text-lg font-bold text-gray-900">24</span>
                        </div>
                        <div class="pl-4 sm:pl-6 text-center sm:text-left">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">ACTIVE
                                SUPPLIERS</span>
                            <span class="text-base sm:text-lg font-bold text-gray-900">14</span>
                        </div>
                    </div>

                    <button
                        class="bg-black text-white px-4 py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center space-x-2 hover:bg-gray-800 transition shadow-sm active:scale-95 cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Add Supplier</span>
                    </button>
                </div>
            </div>

            <!-- Table Container Card -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">

                <!-- Controls Bar: Search & Filtering -->
                <div
                    class="p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-b border-gray-100">
                    <div class="relative w-full sm:w-72">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" placeholder="Search Suppliers..."
                            class="w-full bg-gray-100 text-sm rounded-lg pl-10 pr-4 py-2 border border-transparent focus:bg-white focus:border-gray-300 focus:ring-2 focus:ring-black/5 outline-none transition placeholder-gray-400" />
                    </div>

                    <div class="flex items-center justify-between sm:justify-end space-x-2">
                        <div
                            class="inline-flex bg-gray-100 p-1 rounded-lg text-xs font-semibold text-gray-600 flex-1 sm:flex-initial justify-between">
                            <button
                                class="px-3 py-1.5 rounded-md bg-white text-gray-900 shadow-sm flex-1 sm:flex-none text-center cursor-pointer">All</button>
                            <button
                                class="px-3 py-1.5 rounded-md hover:text-gray-900 transition flex-1 sm:flex-none text-center cursor-pointer">Active</button>
                            <button
                                class="px-3 py-1.5 rounded-md hover:text-gray-900 transition flex-1 sm:flex-none text-center cursor-pointer">Inactive</button>
                        </div>

                        <button title="Export CSV"
                            class="p-2 text-gray-500 hover:text-gray-700 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition shrink-0 cursor-pointer">
                            <i data-lucide="download" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-200 text-[11px] font-bold tracking-wider text-gray-500 uppercase select-none">
                                <th class="py-3 px-4 sm:px-6 cursor-pointer hover:text-gray-800 transition">
                                    <div class="flex items-center space-x-1">
                                        <span>SUPPLIER NAME</span>
                                        <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400"></i>
                                    </div>
                                </th>
                                <th class="py-3 px-4 sm:px-6">CONTACT NUMBER</th>
                                <th class="py-3 px-4 sm:px-6">EMAIL ADDRESS</th>
                                <th class="py-3 px-4 sm:px-6 cursor-pointer hover:text-gray-800 transition">
                                    <div class="flex items-center space-x-1">
                                        <span>STATUS</span>
                                        <i data-lucide="arrow-up-down" class="w-3 h-3 text-gray-400"></i>
                                    </div>
                                </th>
                                <th class="py-3 px-4 sm:px-6 text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">

                            <!-- Row 1 -->
                            <tr class="hover:bg-gray-50/80 transition group">
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">Global
                                        Tech Distro</div>
                                    <div class="text-[11px] text-gray-400 font-medium">ID: SUP-2001</div>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-gray-600 font-medium">+1 (555) 012-4433</td>
                                <td class="py-3.5 px-4 sm:px-6">
                                    <a href="mailto:procurement@globaltech.com"
                                        class="text-blue-600 hover:underline font-medium">procurement@globaltech.com</a>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-right cursor-pointer">
                                    <button
                                        class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition cursor-pointer">
                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr class="hover:bg-gray-50/80 transition cursor-pointer group">
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="font-bold text-gray-900 group-hover:text-blue-600 transition">
                                        MicroSystems Inc.</div>
                                    <div class="text-[11px] text-gray-400 font-medium">ID: SUP-2008</div>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-gray-600 font-medium">+1 (555) 998-1122</td>
                                <td class="py-3.5 px-4 sm:px-6">
                                    <a href="mailto:sales@microsys.inc"
                                        class="text-blue-600 hover:underline font-medium">sales@microsys.inc</a>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-right">
                                    <button
                                        class="p-1.5 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition">
                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer / Pagination -->
                <div
                    class="p-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500 font-medium">
                    <div>
                        Showing <span class="font-semibold text-gray-900">1</span> to <span
                            class="font-semibold text-gray-900">4</span> of <span
                            class="font-semibold text-gray-900">24</span> results
                    </div>
                    <div class="flex items-center space-x-1">
                        <button
                            class="px-3 py-1.5 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            disabled>
                            Previous
                        </button>
                        <button
                            class="px-3 py-1.5 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 text-gray-700 transition cursor-pointer">
                            Next
                        </button>
                    </div>
                </div>

            </div>

        </main>
    </div>
    
    <script type="module" src="../js/suppliers.js"></script>


    <!-- Lucide & Sidebar Script -->
    <script>
        lucide.createIcons();

        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        openBtn.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
    </script>
</body>

</html>