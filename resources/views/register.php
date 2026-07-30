<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SmartStock | Create Account</title>

    <!-- External Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />

    <!-- External Clean CSS Link -->
    <link rel="stylesheet" href="../resources/styles.css" />
</head>

<body>
    <!-- Background Glow Decoration -->
    <div class="bg-decoration">
        <div class="glow-1"></div>
        <div class="glow-2"></div>
    </div>

    <!-- Registration Container -->
    <main class="auth-container">
        <!-- Logo Branding -->
        <?php require_once "partials/header.php" ?>


        <!-- Registration Card -->
        <section class="glass-card">
            <header class="card-header">
                <h2>Create your account</h2>
                <p>Join the network of professional logistics managers.</p>
            </header>

            <form class="auth-form" id="registerForm" method="POST">
                <!-- Full Name Field -->
                <div class="form-group">
                    <label for="full_name">Name</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">person</span>
                        <input class="input-minimal" id="name" placeholder="John Doe" required type="text"
                            autocomplete="name" />
                    </div>
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input class="input-minimal" id="email" placeholder="name@gmail.com" required type="email"
                            autocomplete="email" />
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper password-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input class="input-minimal" id="password" placeholder="••••••••" required type="password"
                            autocomplete="new-password" />
                        <button class="toggle-password-btn" id="togglePassword" type="button"
                            aria-label="Toggle password visibility">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- CTA Button -->
                <button class="btn-submit" type="submit">
                    <span>Create Account</span>
                    <span class="material-symbols-outlined btn-arrow">arrow_forward</span>
                </button>
            </form>

            <div class="card-footer-divider">
                <p class="alt-action-text" style="margin-top: 0;">
                    Already have an account?
                    <a class="alt-action-link" href="login.html">Sign in</a>
                </p>
            </div>
        </section>

        <!-- Secondary Information / Footer -->
        <?php require_once "partials/footer.php" ?>
    </main>


    <!-- Micro-interactions Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            // Password toggle
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                const icon = toggleBtn.querySelector('.material-symbols-outlined');
                icon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });

        });
    </script>
    <script type="module" src="../resources/js/auth.js"></script>
</body>

</html>