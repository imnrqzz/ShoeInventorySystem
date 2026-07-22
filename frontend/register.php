<?php
// frontend/register.php
require_once __DIR__ . '/../backend/bootstrap.php';
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

        <form method="POST" action="../backend/process_register.php" id="registerForm" data-validate data-form-name="register" novalidate>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name"
                       required minlength="2" autocomplete="name">
                <span class="field-error" id="nameError"></span>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
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
            <?= csrf_field() ?>
            <button type="submit" class="btn-register">Create Account</button>
        </form>

        <p class="register-footer">Already have an account? <a href="login.php">Sign In</a></p>
    </div>

    <script>
    // Password strength meter - UI only, not validation.
    document.addEventListener('DOMContentLoaded', function() {
        var password = document.getElementById('password');
        var strengthFill = document.getElementById('strengthFill');
        if (!password || !strengthFill) return;

        password.addEventListener('input', function() {
            var val = password.value;
            var score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^a-zA-Z0-9]/.test(val)) score++;

            var width = (score / 5) * 100;
            var color = score <= 1 ? '#dc2626' : score <= 3 ? '#d97706' : '#16a34a';
            strengthFill.style.width = width + '%';
            strengthFill.style.background = color;
        });
    });
    </script>
</body>
</html>