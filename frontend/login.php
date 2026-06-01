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
        <!-- Best Practice: Show user-friendly error messages, never expose system details -->
        <div class="alert-error">Invalid username or password. Please try again.</div>
        <?php endif; ?>

        <form method="POST" action="../backend/process_login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
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
</body>
</html>
