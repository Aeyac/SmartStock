<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStock | Login</title>
    <link rel="stylesheet" href="src/output.css">
</head>


<body class="bg-gray-50 flex flex-col min-h-screen">

    <div class="w-full max-w-md mx-auto px-4 pt-12 flex-grow">

        <div class="mb-6 text-center">
            <h1 class="text-4xl font-bold "> SmartStock </h1>
            <p class="text-gray-500 text-sm mt-1"> Enterprise Inventory Management</p>
        </div>

        <div class="p-8 shadow-lg bg-white rounded-xl border border-gray-100">
            <p class="font-semibold text-xl"> Welcome Back</p>
            <p class="text-gray-600 text-sm mb-4"> Please enter your details to access your dashboard. </p>

            <form id="loginForm" method="POST">
                <div>
                    <div class="mb-4">
                        <label class="text-sm font-medium mb-1 block">Email Address</label>
                        <div class="relative w-full text-gray-400 focus-within:text-black">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>

                            <input type="email" placeholder="name@gmail.com"
                                class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 w-full focus:outline-none focus:border-black focus:text-black">
                        </div>
                    </div>


                    <div class="mb-4">
                        <label class="text-sm font-medium mb-1 block">Password</label>
                        <div class="relative w-full text-gray-400 focus-within:text-black">
                            <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                            </div>

                            <input id="passwordInput" type="password" placeholder="••••••••"
                                class="border border-gray-300 rounded-lg pl-10 pr-16 py-2 w-full focus:outline-none focus:border-black focus:text-black">
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="mt-2 px-2 py-3 w-full bg-black text-white cursor-pointer rounded-lg hover:bg-gray-900 ">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6 mb-4"> New in our platform? <a href="/"
                class=" font-semibold hover:underline"> Create an account </a></p>

    </div>

    <?php require_once('resources/views/partials/footer.php') ?>

</body>

</html>