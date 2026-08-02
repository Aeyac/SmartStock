<!-- Minimalist Grid Background & Radial Glow -->
<div class="absolute inset-0 -z-10 pointer-events-none flex items-center justify-center">
    <div class="w-[500px] h-[500px] bg-slate-200/50 rounded-full blur-3xl"></div>
    <!-- Subtle SVG dot pattern -->
    <svg class="absolute inset-0 w-full h-full opacity-[0.3]" width="100%" height="100%"
        xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="grid-pattern" width="32" height="32" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="1" fill="#94a3b8" />
            </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#grid-pattern)" />
    </svg>
</div>