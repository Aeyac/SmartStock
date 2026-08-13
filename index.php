<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartStock - Sign In</title>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Local Tailwind CSS -->
    <link href="./src/output.css" rel="stylesheet" />
</head>

<body
    class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col justify-between relative overflow-x-hidden">

    <?php require_once "./resources/views/partials/background.php" ?>
    <?php require_once "./resources/views/partials/header.php" ?>

    <!-- Main Card Container -->
    <main class="w-full flex-1 flex items-center justify-center p-4 py-8">
        <div
            class="w-full max-w-[440px] bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-200 p-6 sm:p-10 transition-all">

            <!-- Heading & Subtitle -->
            <h1 class="text-2xl font-bold text-slate-900 mb-1 tracking-tight">Sign in to your account</h1>
            <p class="text-xs text-slate-500 mb-6 font-normal">Welcome back! Please enter your details.</p>

            <!-- Login Form -->
            <form class="flex flex-col gap-5" id="loginForm">
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
                    <div class="relative flex items-center">
                        <input
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all duration-200 pr-10"
                            id="password" name="password" required type="password" />
                        <button
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors flex items-center justify-center"
                            id="passwordBtn" type="button">
                            <i data-lucide="eye" class="w-[18px] h-[18px]" id="passwordIcon"></i>
                        </button>
                    </div>
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
                        href="./resources/views/create.php">Create account
                    </a>
                </p>
            </div>
        </div>
    </main>

    <!-- Bottom Footer Navigation -->
    <?php require_once "./resources/views/partials/footer.php" ?>

    <script type="module" src="./resources/js/authController.js"></script>
</body>

</html>