<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>SmartStock - Create Account</title>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link href="../../src/output.css" rel="stylesheet" />
</head>

<body
    class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen flex flex-col justify-between relative overflow-x-hidden">

    <!-- background -->
    <?php require_once "partials/background.php" ?>

    <!-- header -->
    <?php require_once "partials/header.php" ?>

    <!-- Main Card Container -->
    <main class="w-full flex-1 flex items-center justify-center p-4 py-8">
        <div
            class="w-full max-w-[440px] bg-white rounded-xl shadow-lg shadow-slate-200/50 border border-slate-200 p-6 sm:p-10 transition-all">

            <!-- Heading & Subtitle -->
            <h1 class="text-2xl font-bold text-slate-900 mb-1 tracking-tight">Create an account</h1>
            <p class="text-xs text-slate-500 mb-6 font-normal">Start managing your stock effortlessly today.</p>

            <!-- Registration Form -->
            <form class="flex flex-col gap-4" id="registerForm">
                <!-- Full Name Field -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-700" for="name">Name</label>
                    <input
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all duration-200"
                        id="name" name="name" placeholder="John Doe" required type="text" />
                </div>

                <!-- Email Field -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-700" for="email">Email address</label>
                    <input
                        class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all duration-200"
                        id="email" name="email" placeholder="name@company.com" required type="email" />
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-700" for="password">Password</label>
                    <div class="relative flex items-center">
                        <input
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all duration-200 pr-10"
                            id="password" name="password" placeholder="At least 8 characters" required
                            type="password" />
                        <button
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 transition-colors flex items-center justify-center"
                            id="passwordBtn" type="button">
                            <i data-lucide="eye" class="w-[18px] h-[18px]" id="passwordIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    class="mt-2 w-full py-2.5 bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold text-sm rounded-lg shadow-sm transition-all duration-150 flex items-center justify-center gap-2"
                    id="registerBtn" type="submit">
                    <span>Create account</span>
                </button>
            </form>

            <!-- Card Bottom Banner -->
            <div
                class="mt-8 pt-4 -mx-6 sm:-mx-10 -mb-6 sm:-mb-10 bg-slate-50 rounded-b-xl border-t border-slate-200 text-center py-4">
                <p class="text-xs text-slate-600 font-medium">
                    Already have an account?
                    <a class="font-bold text-slate-900 hover:underline transition-all ml-1" href="../../index.php">Sign
                        in</a>
                </p>
            </div>
        </div>
    </main>

    <!-- Bottom Footer Navigation -->
    <?php require_once "partials/footer.php" ?>
    <script type="module" src="../js/authController.js"></script>

</body>

</html>