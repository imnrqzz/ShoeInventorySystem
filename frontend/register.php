<?php
// frontend/register.php
require_once __DIR__ . '/../backend/db.php';
$error = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ShoeInventory</title>
    <link rel="stylesheet" href="../css/register_styles.css">
</head>
<body>
    <div class="register-card">
        <div class="register-brand">
            <div class="brand-icon">
                <img src="../images/shoes.png" alt="ShoeInventory Logo">
            </div>
            <h1>ShoeInventory</h1>
            <p>Create a new account</p>
        </div>

        <?php if ($error === 'exists'): ?>
        <div class="alert-error">Username or email already taken. Please try another.</div>
        <?php elseif ($error === 'invalid_input'): ?>
        <div class="alert-error">All fields are required and email must be valid.</div>
        <?php elseif ($error === 'password_mismatch'): ?>
        <div class="alert-error">Passwords do not match. Please try again.</div>
        <?php elseif ($error === 'password_short'): ?>
        <div class="alert-error">Password must be at least 6 characters.</div>
        <?php elseif ($error === 'username_short'): ?>
        <div class="alert-error">Username must be at least 3 characters.</div>
        <?php elseif ($error): ?>
        <div class="alert-error">An error occurred. Please try again.</div>
        <?php endif; ?>

        <!--
          Best Practice: novalidate disables browser default validation popups.
          We handle validation with our own JavaScript for a better user experience
          with styled inline error messages below each field.

          HTML5 attributes (required, minlength, type="email", pattern) serve as
          a second layer — if JS is disabled, the browser still enforces them.
        -->
        <form method="POST" action="../backend/process_register.php" id="registerForm" novalidate>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name"
                       required minlength="2" autocomplete="name">
                <span class="field-error" id="nameError"></span>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <!--
                  Best Practice: The pattern attribute uses a regex to restrict input.
                  ^[a-zA-Z0-9_]+$ means only letters, numbers, and underscores allowed.
                  This prevents special characters that could cause issues.
                -->
                <input type="text" id="username" name="username" placeholder="Choose a username"
                       required minlength="3" maxlength="50" pattern="^[a-zA-Z0-9_]+$" autocomplete="username">
                <span class="field-hint">Letters, numbers, and underscores only. Min 3 characters.</span>
                <span class="field-error" id="usernameError"></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email"
                       required autocomplete="email">
                <span class="field-error" id="emailError"></span>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password"
                       required minlength="6" autocomplete="new-password">
                <span class="field-hint">Minimum 6 characters.</span>
                <!-- Password strength indicator bar -->
                <div class="password-strength" id="strengthBar">
                    <div class="password-strength-fill" id="strengthFill"></div>
                </div>
                <span class="field-error" id="passwordError"></span>
            </div>
            <div class="form-group">
                <label for="repeatpassword">Confirm Password</label>
                <input type="password" id="repeatpassword" name="repeatpassword" placeholder="Repeat your password"
                       required autocomplete="new-password">
                <span class="field-error" id="repeatError"></span>
            </div>
            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <p class="register-footer">Already have an account? <a href="login.php">Sign In</a></p>
    </div>

    <script>
    /**
     * Client-Side Form Validation for Registration
     *
     * Best Practice: Validate on blur (when user leaves field) for immediate feedback,
     * and validate the full form on submit as a final check.
     * The server (process_register.php) re-validates everything — client-side
     * validation is purely for user experience, not security.
     */
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('registerForm');

        // Cache all input elements and their error spans
        var fields = {
            name:     { input: document.getElementById('name'),           error: document.getElementById('nameError') },
            username: { input: document.getElementById('username'),       error: document.getElementById('usernameError') },
            email:    { input: document.getElementById('email'),          error: document.getElementById('emailError') },
            password: { input: document.getElementById('password'),       error: document.getElementById('passwordError') },
            repeat:   { input: document.getElementById('repeatpassword'), error: document.getElementById('repeatError') }
        };

        var strengthFill = document.getElementById('strengthFill');

        // Helper functions for showing/clearing errors
        function showError(field, message) {
            field.input.classList.add('input-error');
            field.error.textContent = message;
        }
        function clearError(field) {
            field.input.classList.remove('input-error');
            field.error.textContent = '';
        }

        // ── Individual Field Validators ──────────────────────────

        function validateName() {
            var val = fields.name.input.value.trim();
            if (val === '') { showError(fields.name, 'Full name is required.'); return false; }
            if (val.length < 2) { showError(fields.name, 'Name must be at least 2 characters.'); return false; }
            clearError(fields.name); return true;
        }

        function validateUsername() {
            var val = fields.username.input.value.trim();
            if (val === '') { showError(fields.username, 'Username is required.'); return false; }
            if (val.length < 3) { showError(fields.username, 'Username must be at least 3 characters.'); return false; }
            // Best Practice: Test against regex pattern to enforce allowed characters
            if (!/^[a-zA-Z0-9_]+$/.test(val)) {
                showError(fields.username, 'Only letters, numbers, and underscores allowed.');
                return false;
            }
            clearError(fields.username); return true;
        }

        function validateEmail() {
            var val = fields.email.input.value.trim();
            if (val === '') { showError(fields.email, 'Email is required.'); return false; }
            // Best Practice: Simple email regex — the real validation happens server-side with filter_var()
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                showError(fields.email, 'Please enter a valid email address.');
                return false;
            }
            clearError(fields.email); return true;
        }

        function validatePassword() {
            var val = fields.password.input.value;
            if (val === '') { showError(fields.password, 'Password is required.'); return false; }
            if (val.length < 6) { showError(fields.password, 'Password must be at least 6 characters.'); return false; }
            clearError(fields.password); return true;
        }

        function validateRepeat() {
            var val = fields.repeat.input.value;
            if (val === '') { showError(fields.repeat, 'Please confirm your password.'); return false; }
            // Check that both password fields match
            if (val !== fields.password.input.value) {
                showError(fields.repeat, 'Passwords do not match.');
                return false;
            }
            clearError(fields.repeat); return true;
        }

        // ── Password Strength Meter ─────────────────────────────
        // Best Practice: Give visual feedback on password strength so users
        // choose better passwords. This is purely informational, not enforced.
        function updateStrength() {
            var val = fields.password.input.value;
            var score = 0;

            if (val.length >= 6) score++;      // Minimum length met
            if (val.length >= 10) score++;     // Good length
            if (/[A-Z]/.test(val)) score++;    // Has uppercase letter
            if (/[0-9]/.test(val)) score++;    // Has a number
            if (/[^a-zA-Z0-9]/.test(val)) score++; // Has special character

            // Map score (0-5) to width percentage and color
            var width = (score / 5) * 100;
            var color = score <= 1 ? '#dc2626' : score <= 3 ? '#d97706' : '#16a34a';

            strengthFill.style.width = width + '%';
            strengthFill.style.background = color;
        }

        // ── Event Listeners ─────────────────────────────────────

        // Validate on blur (when user leaves the field)
        fields.name.input.addEventListener('blur', validateName);
        fields.username.input.addEventListener('blur', validateUsername);
        fields.email.input.addEventListener('blur', validateEmail);
        fields.password.input.addEventListener('blur', validatePassword);
        fields.repeat.input.addEventListener('blur', validateRepeat);

        // Clear errors and update strength as user types
        fields.name.input.addEventListener('input', function() { if (this.value.trim()) clearError(fields.name); });
        fields.username.input.addEventListener('input', function() { if (this.value.trim()) clearError(fields.username); });
        fields.email.input.addEventListener('input', function() { if (this.value.trim()) clearError(fields.email); });
        fields.password.input.addEventListener('input', function() {
            if (this.value) clearError(fields.password);
            updateStrength();
            // Also re-validate confirm field if it has a value (in case passwords now match)
            if (fields.repeat.input.value) validateRepeat();
        });
        fields.repeat.input.addEventListener('input', function() { if (this.value) clearError(fields.repeat); });

        // ── Form Submit ─────────────────────────────────────────
        form.addEventListener('submit', function(e) {
            // Run all validators — collect results
            var valid = true;
            if (!validateName()) valid = false;
            if (!validateUsername()) valid = false;
            if (!validateEmail()) valid = false;
            if (!validatePassword()) valid = false;
            if (!validateRepeat()) valid = false;

            if (!valid) e.preventDefault();
        });
    });
    </script>
</body>
</html>
