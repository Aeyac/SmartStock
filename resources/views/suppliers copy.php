<div class="fixed inset-0 z-[60] flex items-center justify-center bg-primary/20 backdrop-blur-sm p-margin-page hidden"
    id="supplierModal">
    <div
        class="bg-surface-container-lowest w-full max-w-lg rounded-xl shadow-xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-gutter border-b border-outline-variant flex items-center justify-between">
            <h2 class="font-headline-md text-headline-md text-primary">New Supplier Entry</h2>
            <button class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors"
                onclick="toggleModal(false)">close</button>
        </div>
        <form class="p-gutter space-y-stack-lg" onsubmit="event.preventDefault(); toggleModal(false);">
            <div class="space-y-stack-sm">
                <label
                    class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Supplier
                    Name</label>
                <input
                    class="w-full px-stack-md py-stack-sm bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all font-body-md"
                    placeholder="e.g. Acme Components Ltd" type="text">
            </div>
            <div class="grid grid-cols-2 gap-stack-md">
                <div class="space-y-stack-sm">
                    <label
                        class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Contact
                        Number</label>
                    <input
                        class="w-full px-stack-md py-stack-sm bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all font-mono-sm"
                        placeholder="+1..." type="tel">
                </div>
                <div class="space-y-stack-sm">
                    <label
                        class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Category</label>
                    <select
                        class="w-full px-stack-md py-stack-sm bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all font-body-md">
                        <option>Hardware</option>
                        <option>Logistics</option>
                        <option>Software</option>
                        <option>Raw Materials</option>
                    </select>
                </div>
            </div>
            <div class="space-y-stack-sm">
                <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Email
                    Address</label>
                <input
                    class="w-full px-stack-md py-stack-sm bg-surface-container-low border border-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-all font-body-md"
                    placeholder="contact@supplier.com" type="email">
            </div>
            <div class="pt-stack-md flex items-center justify-end gap-stack-md">
                <button
                    class="px-stack-lg py-stack-sm font-label-md text-label-md text-on-surface hover:bg-surface-container transition-colors rounded-lg"
                    onclick="toggleModal(false)" type="button">Cancel</button>
                <button
                    class="px-stack-lg py-stack-sm bg-primary text-on-primary font-label-md text-label-md rounded-lg shadow-md hover:opacity-90 transition-all"
                    type="submit">Save Supplier</button>
            </div>
        </form>
    </div>
</div>
<script>
    function toggleModal(show) {
        const modal = document.getElementById('supplierModal');
        if (show) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Close modal on escape key
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') toggleModal(false);
    });
</script>