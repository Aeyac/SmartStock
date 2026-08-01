<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStock | Login</title>

    <link rel="stylesheet" href="src/output.css">
</head>

<body class="min-h-screen flex flex-col bg-slate-50">

    <!-- Main Content -->
    <main class="relative flex-1">

        <!-- Logo -->
        <div class="absolute top-8 left-8 sm:top-10 sm:left-12 z-10">
            <span class="text-2xl font-bold tracking-tight text-slate-800">
                SmartStock
            </span>
        </div>

        <!-- Centered Login -->
        <div class="grid min-h-screen place-items-center px-6">

            <div class="w-full max-w-sm">

                <!-- Heading -->
                <div class="mb-8">
                    <h1 class="text-2xl font-semibold text-slate-900">
                        Welcome back
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Enter your details to access your dashboard.
                    </p>
                </div>

                <!-- Form -->
                <form id="loginForm" method="POST" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">
                            Email address
                        </label>

                        <div class="relative text-slate-400 focus-within:text-indigo-600">

                            <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="h-5 w-5">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />

                                </svg>

                            </div>

                            <input type="email" id="email" name="email" required placeholder="name@company.com"
                                class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-4 text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>

                        <div class="mb-1.5 flex justify-between">

                            <label for="password" class="text-sm font-medium text-slate-700">
                                Password
                            </label>

                            <a href="/forgot-password" class="text-sm font-medium text-indigo-600 hover:underline">
                                Forgot password?
                            </a>

                        </div>

                        <div class="relative text-slate-400 focus-within:text-indigo-600">

                            <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="h-5 w-5">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />

                                </svg>

                            </div>

                            <input id="password" type="password" name="password" required placeholder="••••••••"
                                class="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-11 text-slate-900 placeholder:text-slate-400 focus:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-100">

                            <button type="button" id="togglePassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">

                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="h-5 w-5">

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />

                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />

                                </svg>

                            </button>

                        </div>

                    </div>

                    <!-- Remember -->
                    <div class="flex items-center">

                        <input id="remember" type="checkbox" name="remember"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600">

                        <label for="remember" class="ml-2 text-sm text-slate-600">
                            Keep me signed in
                        </label>

                    </div>

                    <!-- Button -->
                    <button type="submit"
                        class="w-full cursor-pointer rounded-lg bg-indigo-950 px-4 py-3 font-medium text-white transition hover:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-950 focus:ring-offset-2">
                        Sign in
                    </button>

                </form>

                <!-- Register -->
                <p class="mt-8 text-center text-sm text-slate-500">

                    New to SmartStock?

                    <a href="/" class="font-medium text-indigo-600 hover:underline">
                        Create an account
                    </a>

                </p>

            </div>

        </div>

    </main>

    <?php require_once('resources/views/partials/footer.php'); ?>

    <script type="module" src="resources/js/auth.js"></script>

    <script>
        const toggleBtn = document.getElementById("togglePassword");
        const passwordInput = document.getElementById("password");

        toggleBtn.addEventListener("click", () => {
            passwordInput.type =
                passwordInput.type === "password" ? "text" : "password";
        });
    </script>

</body>

</html>