<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartStock - Sign In</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body
    class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex flex-col justify-between relative overflow-x-hidden">

    <!-- Minimalist Grid Background & Radial Glow -->
    <div class="absolute inset-0 -z-10 pointer-events-none flex items-center justify-center">
        <!-- Soft ambient center glow -->
        <div class="w-[500px] h-[500px] bg-slate-200/50 rounded-full blur-3xl"></div>
        <!-- Subtle SVG dot pattern -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.3]" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid-pattern" width="32" height="32" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1" fill="#94a3b8" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid-pattern)" />
        </svg>
    </div>

    <!-- Top Navigation Header (Tighter padding on mobile) -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-8 pt-4 sm:pt-8 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="text-2xl font-extrabold tracking-tight text-slate-900">SmartStock</span>
        </div>
    </header>

    <!-- Main Card Container (Reduced margin on mobile) -->
    <main class="w-full flex-1 flex items-center justify-center p-4 my-4 sm:my-8">
        <div
            class="w-full max-w-[440px] bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-200 p-6 sm:p-10 transition-all">

            <h1 class="text-2xl font-bold text-slate-900 mb-6 tracking-tight">Sign in to your account</h1>

            <!-- Login Form -->
            <form class="flex flex-col gap-5" id="loginForm" onsubmit="return false;">
                <!-- Email Field -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-700" for="email">Email</label>
                    <input
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all duration-200"
                        id="email" name="email" required type="email" />
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-1.5">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-semibold text-slate-700" for="password">Password</label>
                    </div>
                    <div class="relative">
                        <input
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all duration-200 pr-10"
                            id="password" name="password" required type="password" />
                        <button
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors"
                            onclick="togglePassword()" type="button">
                            <span class="material-symbols-outlined text-[18px]" id="passwordIcon">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center gap-2.5 pt-1">
                    <input
                        class="w-4 h-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 accent-slate-900 cursor-pointer"
                        id="remember" name="remember" type="checkbox" />
                    <label class="text-xs font-medium text-slate-700 cursor-pointer select-none" for="remember">
                        Remember me on this device
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    class="mt-1 w-full py-2.5 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold text-sm rounded-lg shadow-sm transition-all duration-150 flex items-center justify-center gap-2"
                    id="signInBtn" type="submit">
                    <span>Sign in</span>
                </button>
            </form>

            <!-- Card Bottom Banner -->
            <div
                class="mt-8 pt-4 -mx-6 sm:-mx-10 -mb-6 sm:-mb-10 bg-slate-50 rounded-b-xl border-t border-slate-200 text-center py-4">
                <p class="text-xs text-slate-600 font-medium">
                    New to SmartStock?
                    <a class="font-bold text-slate-900 hover:underline transition-all ml-1"
                        href="#">Create account</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Bottom Footer Navigation -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-8 pb-6 sm:pb-8 flex items-center justify-start gap-6 text-xs text-slate-500">
        <span>© SmartStock</span>
        <a class="hover:text-slate-900 transition-colors" href="#">Privacy & terms</a>
    </footer>

    <!-- Script -->
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        const btn = document.getElementById('signInBtn');
        btn.addEventListener('click', (e) => {
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>';

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 1500);
        });
    </script>
</body>

</html>