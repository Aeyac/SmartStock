<header
    class="bg-white border-b border-gray-200 px-4 md:px-8 h-16 flex items-center justify-between lg:justify-end sticky lg:static top-0 z-30 shrink-0">

    <div class="flex items-center space-x-3 lg:hidden">
        <button id="open-sidebar" class="text-gray-600 hover:text-gray-900 focus:outline-none cursor-pointer">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <span class="text-lg font-bold tracking-tight text-gray-900">SmartStock</span>
    </div>

    <!-- SHARED / DESKTOP RIGHT SIDE: Notifications & Profile -->
    <div class="flex items-center space-x-4 lg:space-x-6">

        <!-- User Info -->
        <div class="flex items-center space-x-3">
            <!-- Desktop Text Meta (Hidden on mobile/tablet) -->
            <div class="block text-right">
                <div class="text-sm font-bold text-gray-900 leading-tight">
                    <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></span>
                </div>
                <div class="text-xs text-gray-400 font-medium tracking-wider">
                    ADMIN
                </div>
            </div>

            <!-- Avatar (Shown on both) -->
            <div class="w-8 h-8 md:w-9 md:h-9 rounded-full bg-black text-white flex items-center justify-center">
                <i data-lucide="user" class="w-4 h-4 md:w-5 md:h-5"></i>
            </div>
        </div>
    </div>
</header>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('open-sidebar');
        const closeBtn = document.getElementById('close-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const sidebar = document.getElementById('sidebar');

        function openMenu() {
            // Show backdrop
            overlay?.classList.remove('hidden');
            sidebar?.classList.remove('-translate-x-full');
        }

        function closeMenu() {
            // Hide backdrop
            overlay?.classList.add('hidden');
            sidebar?.classList.add('-translate-x-full');
        }

        if (openBtn) openBtn.addEventListener('click', openMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        if (overlay) overlay.addEventListener('click', closeMenu);
    });
</script>