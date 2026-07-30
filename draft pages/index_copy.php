<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStock | Enterprise Logistics Sign In</title>

    <!-- External Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet">

    <!-- Shared CSS Link -->
    <link rel="stylesheet" href="resources/styles.css">
</head>

<body>
    <!-- Background Glow Decoration -->
    <!-- <div class="bg-decoration">
        <div class="glow-1"></div>
        <div class="glow-2"></div>
    </div> -->

    <!-- Main Container -->
    <main class="auth-container">
        <!-- Brand Header with Left-Aligned Logo -->
        <?php require_once "views/partials/header.php" ?>


        <!-- Auth Card -->
        <section class="glass-card">
            <header class="card-header">
                <h2>Welcome Back</h2>
                <p>Please enter your details to access your dashboard.</p>
            </header>

            <form id="loginForm" class="auth-form" method="POST">
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input class="input-minimal" id="email" name="email" placeholder="name@company.com" required
                            type="email" autocomplete="email">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password">Password</label>
                        <a class="forgot-link" href="#">Forgot Password?</a>
                    </div>
                    <div class="input-wrapper password-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input class="input-minimal" id="password" name="password" placeholder="••••••••" required
                            type="password" autocomplete="current-password">
                        <button class="toggle-password-btn" id="togglePassword" type="button"
                            aria-label="Toggle password visibility">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="remember-row">
                    <input id="remember" name="remember" type="checkbox">
                    <label for="remember">Remember this device for 30 days</label>
                </div>

                <!-- Submit Button -->
                <button class="btn-submit" type="submit">
                    <span>Sign In</span>
                    <span class="material-symbols-outlined btn-arrow">arrow_forward</span>
                </button>
            </form>

            <div class="card-footer-divider">
                <p class="alt-action-text" style="margin-top: 0;">
                    Don't have an account?
                    <a class="alt-action-link" href="register.html">Register Now</a>
                </p>
            </div>
        </section>

        <!-- Footer -->
        <?php require_once "views/partials/footer.php" ?>
    </main>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            // Password visibility toggle
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                const icon = toggleBtn.querySelector('.material-symbols-outlined');
                icon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });
        });
    </script>

    <script type="module" src="resources/js/auth.js"></script>
</body>

</html>