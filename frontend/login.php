<?php
// frontend/login.php

// Best Practice: Include db.php to start sessions and load shared utilities.
require_once __DIR__ . '/../backend/db.php';

// Check for success message from registration
$registered = isset($_GET['registered']) && $_GET['registered'] === '1';
// Check for login errors
$error = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShoeInventory</title>
    <link rel="stylesheet" href="../css/login_style.css">
</head>
<body>
    <?php if ($registered): ?>
    <!-- Success modal shown after registration -->
    <div class="modal-backdrop" id="successModal">
        <div class="modal">
            <h4>Registration Successful</h4>
            <p>Your account has been created. Please log in to continue.</p>
            <button class="btn-ok" id="modalOk">OK</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="login-card">
        <div class="login-brand">
            <div class="brand-icon">
                <img src="../images/shoes.png" alt="ShoeInventory Logo">
            </div>
            <h1>ShoeInventory</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error === 'invalid' || $error === '1'): ?>
        <div class="alert-error">Invalid username or password. Please try again.</div>
        <?php endif; ?>

        <!--
          Best Practice: Client-side validation with novalidate + JavaScript.
          We use novalidate to disable browser default popups and handle
          validation ourselves with custom styled error messages instead.
        -->
        <form method="POST" action="../backend/process_login.php" id="loginForm" novalidate>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required
                       minlength="3" autocomplete="username">
                <!-- Inline error message — hidden by default, shown by JS when validation fails -->
                <span class="field-error" id="usernameError"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required
                       minlength="6" autocomplete="current-password">
                <span class="field-error" id="passwordError"></span>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <p class="login-footer">Don't have an account? <a href="register.php">Register</a></p>
    </div>

    <?php if ($registered): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('successModal');
            var ok = document.getElementById('modalOk');
            function hide() { if (modal) modal.style.display = 'none'; }
            if (ok) ok.addEventListener('click', hide);
            setTimeout(hide, 4000);
        });
    </script>
    <?php endif; ?>

    <script>
    /**
     * Client-Side Form Validation for Login
     *
     * Best Practice: Validate on the client side for instant feedback,
     * but ALWAYS validate on the server side too (backend/process_login.php),
     * because client-side validation can be bypassed by disabling JavaScript.
     * Client-side = user experience. Server-side = security.
     */
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('loginForm');
        var username = document.getElementById('username');
        var password = document.getElementById('password');

        // Helper: show/clear error message for a specific field
        function showError(input, errorEl, message) {
            input.classList.add('input-error');
            errorEl.textContent = message;
        }
        function clearError(input, errorEl) {
            input.classList.remove('input-error');
            errorEl.textContent = '';
        }

        // Validate a single field — returns true if valid
        function validateField(input, errorEl) {
            var value = input.value.trim();

            if (value === '') {
                showError(input, errorEl, 'This field is required.');
                return false;
            }
            if (input.minLength > 0 && value.length < input.minLength) {
                showError(input, errorEl, 'Must be at least ' + input.minLength + ' characters.');
                return false;
            }
            clearError(input, errorEl);
            return true;
        }

        // Best Practice: Validate on blur (when user leaves a field) for real-time feedback
        username.addEventListener('blur', function() {
            validateField(username, document.getElementById('usernameError'));
        });
        password.addEventListener('blur', function() {
            validateField(password, document.getElementById('passwordError'));
        });

        // Clear error styling as user types (immediate feedback that they're fixing it)
        username.addEventListener('input', function() {
            if (username.value.trim() !== '') clearError(username, document.getElementById('usernameError'));
        });
        password.addEventListener('input', function() {
            if (password.value.trim() !== '') clearError(password, document.getElementById('passwordError'));
        });

        // Validate entire form on submit
        form.addEventListener('submit', function(e) {
            var valid = true;
            if (!validateField(username, document.getElementById('usernameError'))) valid = false;
            if (!validateField(password, document.getElementById('passwordError'))) valid = false;

            // Best Practice: preventDefault() stops the form from submitting if validation fails
            if (!valid) e.preventDefault();
        });
    });
    </script>
</body>
</html>
