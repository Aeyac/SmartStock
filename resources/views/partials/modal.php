<!-- Modal Backdrop -->
<div id="modal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-opacity duration-200">

    <div
        class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-2xl transition-all border border-slate-100">

        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <h2 id="modalTitle" class="text-lg font-bold text-slate-800 tracking-tight"></h2>
            <button id="closeModalBtn"
                class="group rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition cursor-pointer"
                title="Close modal">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div id="modalBody" class="py-5"></div>

        <div id="modalFooter" class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4"></div>
    </div>
</div>